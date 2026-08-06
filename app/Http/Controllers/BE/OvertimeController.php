<?php

namespace App\Http\Controllers\BE;

use App\Constants\StatusCodeConstants;
use App\Exceptions\AppException;
use App\Filters\OvertimeFilter;
use App\Http\Controllers\Controller;
use App\Http\Requests\OvertimeIndexRequest;
use App\Http\Requests\OvertimeDirectorApproveRequest;
use App\Http\Requests\OvertimeShowRequest;
use App\Http\Requests\OvertimeStoreRequest;
use App\Http\Requests\OvertimeUpdateStatusRequest;
use App\Http\Resources\OvertimeResource;
use App\Mail\OvertimeApplicationMail;
use App\Models\Overtime;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;

class OvertimeController extends Controller
{
    public function __construct(private OvertimeFilter $overtime_filter)
    {
    }

    public function index(OvertimeIndexRequest $request)
    {
        $overtime = Overtime::with([
            'user.personal',
            'user.contact',
            'user.employment.office',
            'user.employment.position',
            'user.employment.department',
            'user.emergency',
            'user.certificates',

            'directorActionBy.personal',
            'directorActionBy.contact',
            'directorActionBy.employment.office',
            'directorActionBy.employment.position',
            'directorActionBy.employment.department',
            'directorActionBy.emergency',
            'directorActionBy.certificates',
        ])->active();

        $overtime = $this->overtime_filter->apply($request, $request->size, $overtime);

        return self::responsePaginated(OvertimeResource::collection($overtime), $overtime);
    }

    public function store(OvertimeStoreRequest $request)
    {
        $user = User::findByUuid($request->user_uuid);

        DB::beginTransaction();

        try {
            $attachment_path = null;

            if ($request->hasFile('attachment'))
            {
                $file = $request->file('attachment');

                $filename = time() . '_' . self::uuid() . '.' . $file->getClientOriginalExtension();

                $attachment_path = $file->storeAs('overtimes', $filename, 'public');
            }

            $overtime = Overtime::create([
                'uuid' => self::uuid(),
                'user_id' => $user->id,
                'description' => $request->description,
                'total_days' => $request->total_days ?? null,
                'attachment_path' => $attachment_path,
                'is_active' => StatusCodeConstants::ACTIVE,
                'created_by' => self::auth()->uuid,
                'created_at' => self::currentDateTime(),
                'updated_by' => self::auth()->uuid,
                'updated_at' => self::currentDateTime(),
            ]);

            $overtime->load([
                'user.personal',
                'user.contact',
                'user.employment.office',
                'user.employment.position',
                'user.employment.department',
                'user.emergency',
                'user.certificates',

                'directorActionBy.personal',
                'directorActionBy.contact',
                'directorActionBy.employment.office',
                'directorActionBy.employment.position',
                'directorActionBy.employment.department',
                'directorActionBy.emergency',
            ]);

            $this->sendDirectorEmail($overtime);

            DB::commit();

            return self::response(new OvertimeResource($overtime));

        } catch (\Exception $exception) {
            DB::rollback();
            throw $exception;
        }
    }

    public function updateStatus(OvertimeUpdateStatusRequest $request, string $uuid)
    {
        DB::beginTransaction();

        try {
            $overtime = Overtime::findByUuid($uuid);

            $overtime->update([
                'is_active' => $request->is_active ? StatusCodeConstants::ACTIVE : StatusCodeConstants::INACTIVE,
                'updated_by' => self::auth()->uuid,
                'updated_at' => self::currentDateTime(),
            ]);

            if ($request->is_active == StatusCodeConstants::INACTIVE)
            {
                $this->sendDirectorCancellationEmail($overtime);
            }

            DB::commit();

            return self::response(new OvertimeResource($overtime));

        } catch (\Exception $exception) {
            DB::rollback();
            throw $exception;
        }
    }

    public function show(OvertimeShowRequest $request, string $uuid)
    {
        $overtime = Overtime::findByUuid($uuid);

        return self::response(new OvertimeResource($overtime));
    }

    public function directorApprove(OvertimeDirectorApproveRequest $request, string $uuid)
    {
        $overtime = Overtime::findByUuid($uuid);

        $director = User::findByUuid(self::auth()->uuid);

        throw_if($director->employment?->is_director != StatusCodeConstants::ACTIVE, AppException::class, 'Director access only');
        throw_if($overtime->director_action_at, AppException::class, 'Overtime already reviewed by director');

        $overtime->update([
            'director_action_by' => $director->id,
            'director_action_at' => self::currentDateTime(),
            'director_approved' => $request->approve ? StatusCodeConstants::ACTIVE : StatusCodeConstants::INACTIVE,
            'director_remark' => $request->remark,
            'updated_by' => self::auth()->uuid,
            'updated_at' => self::currentDateTime(),
        ]);

        $this->sendAccountantEmail($overtime, $request->approve, $director);

        $overtime = Overtime::findByUuid($uuid);

        return self::response(new OvertimeResource($overtime));
    }

    private function sendDirectorEmail($overtime)
    {
        $directors = User::whereHas('employment', function ($query) {
            $query->where('is_director', '=', StatusCodeConstants::ACTIVE);
        })
            ->where('is_active', StatusCodeConstants::ACTIVE)
            ->get();

        foreach($directors as $director)
        {
            Password::deleteToken($director);

            $token = Password::createToken($director);

            $data = [
                'name' => trim(($director->personal?->first_name ?? '') . ' ' . ($director->personal?->last_name ?? '')) ?: $director->email,
                'applicant_name' => trim(($overtime->user->personal?->first_name ?? '') . ' ' . ($overtime->user->personal?->last_name ?? '')) ?: $overtime->user->email,
                'applicant_email' => $overtime->user->email,
                'applicant_phone_number' => $overtime->user->contact?->phone_number,
                'submitted_at' => self::currentDateTime()->format('Y-m-d h:i:s A'),
                'subject' => 'PE Portal - Overtime Pending Director Approval',
                'title' => 'Overtime Director Approval',
                'overtime' => $overtime,
                'action_url' => url('/overtime-review?token=' . $token . '&email=' . urlencode($director->email) . '&overtime_uuid=' . $overtime->uuid),
                'action_label' => 'Review Overtime',
            ];

            Mail::to($director->email)->send(new OvertimeApplicationMail($data));
        }
    }

    private function sendAccountantEmail($overtime, $approved = true, $reviewer = null)
    {
        $accountants = User::whereHas('employment', function ($query) {
            $query->where('is_accountant', '=', StatusCodeConstants::ACTIVE);
        })
            ->where('is_active', StatusCodeConstants::ACTIVE)
            ->get();

        foreach($accountants as $accountant)
        {
            $data = [
                'name' => trim(($accountant->personal?->first_name ?? '') . ' ' . ($accountant->personal?->last_name ?? '')) ?: $accountant->email,
                'applicant_name' => trim(($overtime->user->personal?->first_name ?? '') . ' ' . ($overtime->user->personal?->last_name ?? '')) ?: $overtime->user->email,
                'applicant_email' => $overtime->user->email,
                'applicant_phone_number' => $overtime->user->contact?->phone_number,
                'submitted_at' => self::currentDateTime()->format('Y-m-d h:i:s A'),
                'subject' => $approved ? 'PE Portal - Overtime Approved' : 'PE Portal - Overtime Rejected',
                'title' => $approved ? 'Overtime Approved' : 'Overtime Rejected',
                'status_text' => $approved ? 'approved' : 'rejected',
                'reviewed_by' => $reviewer ? trim(($reviewer->personal?->first_name ?? '') . ' ' . ($reviewer->personal?->last_name ?? '')) ?: $reviewer->email : null,
                'director_remark' => $overtime->director_remark,
                'footer_message' => 'Please log in to PE Portal to view the overtime application.',
                'overtime' => $overtime,
            ];

            Mail::to($accountant->email)->send(new OvertimeApplicationMail($data));
        }
    }

    private function sendDirectorCancellationEmail($overtime)
    {
        $directors = User::whereHas('employment', function ($query) {
            $query->where('is_director', '=', StatusCodeConstants::ACTIVE);
        })
            ->where('is_active', StatusCodeConstants::ACTIVE)
            ->get();

        $cancelled_by = User::findByUuid(self::auth()->uuid);

        foreach($directors as $director)
        {
            $data = [
                'name' => trim(($director->personal?->first_name ?? '') . ' ' . ($director->personal?->last_name ?? '')) ?: $director->email,
                'applicant_name' => trim(($overtime->user->personal?->first_name ?? '') . ' ' . ($overtime->user->personal?->last_name ?? '')) ?: $overtime->user->email,
                'applicant_email' => $overtime->user->email,
                'applicant_phone_number' => $overtime->user->contact?->phone_number,
                'submitted_at' => self::currentDateTime()->format('Y-m-d h:i:s A'),
                'subject' => 'PE Portal - Overtime Cancelled',
                'title' => 'Overtime Cancelled',
                'cancelled_by' => trim(($cancelled_by->personal?->first_name ?? '') . ' ' . ($cancelled_by->personal?->last_name ?? '')) ?: $cancelled_by->email,
                'footer_message' => 'Please log in to PE Portal to view the overtime application.',
                'is_cancelled_notification' => true,
                'overtime' => $overtime,
            ];

            Mail::to($director->email)->send(new OvertimeApplicationMail($data));
        }
    }
}
