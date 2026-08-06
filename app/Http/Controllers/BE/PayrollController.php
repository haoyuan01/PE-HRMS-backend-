<?php

namespace App\Http\Controllers\BE;

use App\Http\Controllers\Controller;
use App\Constants\StatusCodeConstants;
use App\Exceptions\AppException;
use App\Filters\UserFilter;
use App\Http\Requests\PayrollShowRequest;
use App\Http\Requests\PayrollStoreRequest;
use App\Http\Requests\PayrollUpdateRequest;
use App\Http\Requests\PayrollUpdateStatusRequest;
use App\Http\Requests\UserIndexRequest;
use App\Http\Resources\PayrollResource;
use App\Http\Resources\UserResource;
use App\Mail\PayrollNotificationMail;
use App\Models\Payroll;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;

class PayrollController extends Controller
{
    public function __construct(private UserFilter $user_filter, private AuthService $auth_service)
    {
    }

    public function index(UserIndexRequest $request)
    {
        $user = User::with([
            'personal',
            'contact',
            'employment.office',
            'employment.department',
            'employment.position',
            'emergency',
            'certificates',
            'roles.permissions',
            'roles' => function ($query) {
                $query->where('is_active', StatusCodeConstants::ACTIVE);  
            },
            'payrolls' => function ($query) use ($request) {

                if ($request->has('payroll_month') && !empty($request->payroll_month))
                {
                    $query->where('month', $request->payroll_month);
                }

                if ($request->has('payroll_year') && !empty($request->payroll_year))
                {
                    $query->where('year', $request->payroll_year);
                }
                
            },
        ]);
        
        $user = $this->user_filter->apply($request, $request->size, $user);
        
        return self::responsePaginated(UserResource::collection($user), $user);
    }

    public function store(PayrollStoreRequest $request)
    {
        $user = User::findByUuid($request->user_uuid);

        throw_if(Payroll::where('user_id', $user->id)->where('month', $request->month)->where('year', $request->year)->active()->exists(), AppException::class, 'Payroll for this month already exist');

        DB::beginTransaction();

        try {
            $attachment_path = null;

            if ($request->hasFile('attachment'))
            {
                $file = $request->file('attachment');

                $filename = time() . '_' . self::uuid() . '.' . $file->getClientOriginalExtension();

                $attachment_path = $file->storeAs('payrolls', $filename, 'public');
            }

            $payroll = Payroll::create([
                'uuid' => self::uuid(),
                'user_id' => $user->id,
                'month' => $request->month,
                'year' => $request->year,
                'attachment_path' => $attachment_path,
                'remark' => $request->remark,
                'is_published' => $request->is_published ? StatusCodeConstants::ACTIVE : StatusCodeConstants::INACTIVE,
                'email_sent_at' => null,
                'is_active' => StatusCodeConstants::ACTIVE,
                'created_by' => self::auth()->uuid,
                'created_at' => self::currentDateTime(),
                'updated_by' => self::auth()->uuid,
                'updated_at' => self::currentDateTime(),
            ]);

            $payroll->load([
                'user.personal',
                'user.contact',
                'user.employment.office',
                'user.employment.position',
                'user.employment.department',
                'user.emergency',
                'user.certificates',
            ]);

            if ($payroll->is_published)
            {
                $this->sendPayrollEmail($payroll);
            }

            DB::commit();

            return self::response(new PayrollResource($payroll));

        } catch (\Exception $exception) {
            DB::rollback();
            throw $exception;
        }
    }

    public function update(PayrollUpdateRequest $request, string $uuid)
    {
        $payroll = Payroll::findByUuid($uuid);

        throw_if(Payroll::where('user_id', $payroll->user->id)->where('month', $request->month)->where('year', $request->year)->where('uuid', '!=', $uuid)->active()->exists(), AppException::class, 'Payroll for this month already exist');

        DB::beginTransaction();

        try {
            $attachment_path = null;

            if ($request->hasFile('attachment'))
            {
                $file = $request->file('attachment');

                $filename = time() . '_' . self::uuid() . '.' . $file->getClientOriginalExtension();

                $attachment_path = $file->storeAs('payrolls', $filename, 'public');
            }

            $old_is_published = $payroll->is_published;

            $payroll->update([
                'user_id' => $payroll->user_id,
                'month' => $request->month,
                'year' => $request->year,
                'attachment_path' => $attachment_path ? $attachment_path : $payroll->attachment_path,
                'remark' => $request->remark,
                'is_published' => $request->is_published ? StatusCodeConstants::ACTIVE : StatusCodeConstants::INACTIVE,
                'updated_by' => self::auth()->uuid,
                'updated_at' => self::currentDateTime(),
            ]);

            $payroll->load([
                'user.personal',
                'user.contact',
                'user.employment.office',
                'user.employment.position',
                'user.employment.department',
                'user.emergency',
                'user.certificates',
            ]);

            if (!$old_is_published && $payroll->is_published && !$payroll->email_sent_at)
            {
                $this->sendPayrollEmail($payroll);
            }

            DB::commit();

            return self::response(new PayrollResource($payroll));

        } catch (\Exception $exception) {
            DB::rollback();
            throw $exception;
        }
    }

    public function updateStatus(PayrollUpdateStatusRequest $request, string $uuid)
    {
        DB::beginTransaction();

        try {
            $payroll = Payroll::findByUuid($uuid);

            $payroll->update([
                'is_active' => $request->is_active ? StatusCodeConstants::ACTIVE : StatusCodeConstants::INACTIVE,
                'updated_by' => self::auth()->uuid,
                'updated_at' => self::currentDateTime(),
            ]);

            $payroll->load([
                'user.personal',
                'user.contact',
                'user.employment.office',
                'user.employment.position',
                'user.employment.department',
                'user.emergency',
                'user.certificates',
            ]);

            DB::commit();

            return self::response(new PayrollResource($payroll));

        } catch (\Exception $exception) {
            DB::rollback();
            throw $exception;
        }
    }

    public function show(PayrollShowRequest $request, string $uuid)
    {
        $payroll = Payroll::findByUuid($uuid);

        $this->auth_service->validatePasscode(self::auth(), $request->passcode);

        return self::response(new PayrollResource($payroll));
    }

    private function sendPayrollEmail($payroll)
    {
        Password::deleteToken($payroll->user);

        $token = Password::createToken($payroll->user);

        $data = [
            'name' => trim(($payroll->user->personal?->first_name ?? '') . ' ' . ($payroll->user->personal?->last_name ?? '')) ?: $payroll->user->email,
            'subject' => 'PE Portal - Payroll Available',
            'payroll' => $payroll,
            'action_url' => url('/payroll-preview?token=' . $token . '&email=' . urlencode($payroll->user->email) . '&payroll_uuid=' . $payroll->uuid),
        ];

        Mail::to($payroll->user->email)->send(new PayrollNotificationMail($data));

        $payroll->update([
            'email_sent_at' => self::currentDateTime(),
            'updated_by' => self::auth()->uuid,
            'updated_at' => self::currentDateTime(),
        ]);
    }
}
