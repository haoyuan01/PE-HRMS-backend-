<?php

namespace App\Http\Controllers\BE;

use App\Constants\StatusCodeConstants;
use App\Exceptions\AppException;
use App\Filters\OvertimeFilter;
use App\Http\Controllers\Controller;
use App\Http\Requests\OvertimeIndexRequest;
use App\Http\Requests\OvertimeManagerApproveRequest;
use App\Http\Requests\OvertimeShowRequest;
use App\Http\Requests\OvertimeStoreRequest;
use App\Http\Requests\OvertimeUpdateRequest;
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

            'managerApprover.personal',
            'managerApprover.contact',
            'managerApprover.employment.office',
            'managerApprover.employment.position',
            'managerApprover.employment.department',
            'managerApprover.emergency',

            'managerActionBy.personal',
            'managerActionBy.contact',
            'managerActionBy.employment.office',
            'managerActionBy.employment.position',
            'managerActionBy.employment.department',
            'managerActionBy.emergency',
        ])->active();

        $overtime = $this->overtime_filter->apply($request, $request->size, $overtime);

        return self::responsePaginated(OvertimeResource::collection($overtime), $overtime);
    }

    public function store(OvertimeStoreRequest $request)
    {
        $user = User::findByUuid(self::auth()->uuid);
        $manager_approver = User::findByUuid($request->manager_approver_uuid);

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
                'manager_approver_id' => $manager_approver->id,
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

                'managerApprover.personal',
                'managerApprover.contact',
                'managerApprover.employment.office',
                'managerApprover.employment.position',
                'managerApprover.employment.department',
                'managerApprover.emergency',

                'managerActionBy.personal',
                'managerActionBy.contact',
                'managerActionBy.employment.office',
                'managerActionBy.employment.position',
                'managerActionBy.employment.department',
                'managerActionBy.emergency',
            ]);

            $this->sendManagerEmail($overtime, $manager_approver);

            DB::commit();

            return self::response(new OvertimeResource($overtime));

        } catch (\Exception $exception) {
            DB::rollback();
            throw $exception;
        }
    }

    public function update(OvertimeUpdateRequest $request, string $uuid)
    {
        $overtime = Overtime::findByUuid($uuid);
        $user = User::findByUuid(self::auth()->uuid);
        $old_manager_approver_id = $overtime->manager_approver_id;
        $manager_approver = User::findByUuid($request->manager_approver_uuid);

        throw_if($overtime->manager_action_at, AppException::class, 'Overtime already reviewed by manager');

        DB::beginTransaction();

        try {
            $attachment_path = null;

            if ($request->hasFile('attachment'))
            {
                $file = $request->file('attachment');

                $filename = time() . '_' . self::uuid() . '.' . $file->getClientOriginalExtension();

                $attachment_path = $file->storeAs('overtimes', $filename, 'public');
            }

            $overtime->update([
                'user_id' => $user->id,
                'manager_approver_id' => $manager_approver->id,
                'description' => $request->description,
                'total_days' => $request->total_days ?? null,
                'attachment_path' => $attachment_path ? $attachment_path : $overtime->attachment_path,
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

                'managerApprover.personal',
                'managerApprover.contact',
                'managerApprover.employment.office',
                'managerApprover.employment.position',
                'managerApprover.employment.department',
                'managerApprover.emergency',
                
                'managerActionBy.personal',
                'managerActionBy.contact',
                'managerActionBy.employment.office',
                'managerActionBy.employment.position',
                'managerActionBy.employment.department',
                'managerActionBy.emergency',
            ]);

            if ($manager_approver->id != $old_manager_approver_id)
            {
                $this->sendManagerEmail($overtime, $manager_approver);
            }

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

    public function managerApprove(OvertimeManagerApproveRequest $request, string $uuid)
    {
        $overtime = Overtime::findByUuid($uuid);

        $manager = User::findByUuid(self::auth()->uuid);

        throw_if($manager->employment?->is_manager != StatusCodeConstants::ACTIVE, AppException::class, 'Manager access only');
        throw_if($overtime->manager_approver_id != $manager->id, AppException::class, 'Invalid manager approver');
        throw_if($overtime->manager_action_at, AppException::class, 'Overtime already reviewed by manager');

        $overtime->update([
            'manager_action_by' => $manager->id,
            'manager_action_at' => self::currentDateTime(),
            'manager_approved' => $request->approve ? StatusCodeConstants::ACTIVE : StatusCodeConstants::INACTIVE,
            'manager_remark' => $request->remark,
            'updated_by' => self::auth()->uuid,
            'updated_at' => self::currentDateTime(),
        ]);

        if ($request->approve)
        {
            $this->sendAccountantEmail($overtime);
        }

        $overtime = Overtime::findByUuid($uuid);

        return self::response(new OvertimeResource($overtime));
    }

    private function sendManagerEmail($overtime, $manager_approver)
    {
        $token = Password::createToken($manager_approver);

        $data = [
            'name' => trim(($manager_approver->personal?->first_name ?? '') . ' ' . ($manager_approver->personal?->last_name ?? '')) ?: $manager_approver->email,
            'applicant_name' => trim(($overtime->user->personal?->first_name ?? '') . ' ' . ($overtime->user->personal?->last_name ?? '')) ?: $overtime->user->email,
            'applicant_email' => $overtime->user->email,
            'applicant_phone_number' => $overtime->user->contact?->phone_number,
            'submitted_at' => self::currentDateTime()->format('Y-m-d h:i:s A'),
            'subject' => 'PE Portal - Overtime Pending Manager Approval',
            'title' => 'Overtime Manager Approval',
            'overtime' => $overtime,
            'action_url' => url('/overtime-review?token=' . $token . '&email=' . urlencode($manager_approver->email) . '&overtime_uuid=' . $overtime->uuid),
            'action_label' => 'Review Overtime',
        ];

        Mail::to($manager_approver->email)->send(new OvertimeApplicationMail($data));
    }

    private function sendAccountantEmail($overtime)
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
                'subject' => 'PE Portal - Overtime Approved',
                'title' => 'Overtime Approved',
                'overtime' => $overtime,
            ];

            Mail::to($accountant->email)->send(new OvertimeApplicationMail($data));
        }
    }
}
