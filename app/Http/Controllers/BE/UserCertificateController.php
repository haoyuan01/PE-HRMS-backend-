<?php

namespace App\Http\Controllers\BE;

use App\Constants\StatusCodeConstants;
use App\Filters\UserCertificateFilter;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserCertificateIndexRequest;
use App\Http\Requests\UserCertificateShowRequest;
use App\Http\Requests\UserCertificateStoreRequest;
use App\Http\Requests\UserCertificateUpdateRequest;
use App\Http\Requests\UserCertificateUpdateStatusRequest;
use App\Http\Resources\UserCertificateResource;
use App\Models\User;
use App\Models\UserCertificate;
use Illuminate\Support\Facades\DB;

class UserCertificateController extends Controller
{
    public function __construct(private UserCertificateFilter $user_certificate_filter)
    {
    }

    public function index(UserCertificateIndexRequest $request)
    {
        $user_certificate = UserCertificate::with([
            'user.personal',
            'user.contact',
            'user.employment.office',
            'user.employment.position',
            'user.employment.department',
            'user.emergency',
        ])->active();

        $user_certificate = $this->user_certificate_filter->apply($request, $request->size, $user_certificate);

        return self::responsePaginated(UserCertificateResource::collection($user_certificate), $user_certificate);
    }

    public function store(UserCertificateStoreRequest $request)
    {
        $user = User::findByUuid($request->user_uuid);

        DB::beginTransaction();

        try {
            $attachment_path = null;

            if ($request->hasFile('attachment'))
            {
                $file = $request->file('attachment');

                $filename = time() . '_' . self::uuid() . '.' . $file->getClientOriginalExtension();

                $attachment_path = $file->storeAs('user-certificates', $filename, 'public');
            }

            $user_certificate = UserCertificate::create([
                'uuid' => self::uuid(),
                'user_id' => $user->id,
                'name' => $request->name,
                'organization' => $request->organization,
                'description' => $request->description,
                'date_applied' => $request->date_applied,
                'valid_until' => $request->valid_until,
                'attachment_path' => $attachment_path,
                'is_active' => StatusCodeConstants::ACTIVE,
                'created_by' => self::auth()->uuid,
                'created_at' => self::currentDateTime(),
                'updated_by' => self::auth()->uuid,
                'updated_at' => self::currentDateTime(),
            ]);

            $user_certificate->load([
                'user.personal',
                'user.contact',
                'user.employment.office',
                'user.employment.position',
                'user.employment.department',
                'user.emergency',
            ]);

            DB::commit();

            return self::response(new UserCertificateResource($user_certificate));

        } catch (\Exception $exception) {
            DB::rollback();
            throw $exception;
        }
    }

    public function update(UserCertificateUpdateRequest $request, string $uuid)
    {
        $user_certificate = UserCertificate::findByUuid($uuid);

        DB::beginTransaction();

        try {
            $attachment_path = null;

            if ($request->hasFile('attachment'))
            {
                $file = $request->file('attachment');

                $filename = time() . '_' . self::uuid() . '.' . $file->getClientOriginalExtension();

                $attachment_path = $file->storeAs('user-certificates', $filename, 'public');
            }

            $user_certificate->update([
                'user_id' => $user_certificate->user_id,
                'name' => $request->name,
                'organization' => $request->organization,
                'description' => $request->description,
                'date_applied' => $request->date_applied,
                'valid_until' => $request->valid_until,
                'attachment_path' => $attachment_path ? $attachment_path : $user_certificate->attachment_path,
                'is_active' => StatusCodeConstants::ACTIVE,
                'updated_by' => self::auth()->uuid,
                'updated_at' => self::currentDateTime(),
            ]);

            $user_certificate->load([
                'user.personal',
                'user.contact',
                'user.employment.office',
                'user.employment.position',
                'user.employment.department',
                'user.emergency',
            ]);

            DB::commit();

            return self::response(new UserCertificateResource($user_certificate));

        } catch (\Exception $exception) {
            DB::rollback();
            throw $exception;
        }
    }

    public function updateStatus(UserCertificateUpdateStatusRequest $request, string $uuid)
    {
        $user_certificate = UserCertificate::findByUuid($uuid);

        $user_certificate->update([
            'is_active' => $request->is_active ? StatusCodeConstants::ACTIVE : StatusCodeConstants::INACTIVE,
            'updated_by' => self::auth()->uuid,
            'updated_at' => self::currentDateTime(),
        ]);

        $user_certificate->load([
            'user.personal',
            'user.contact',
            'user.employment.office',
            'user.employment.position',
            'user.employment.department',
            'user.emergency',
        ]);

        return self::response(new UserCertificateResource($user_certificate));
    }

    public function show(UserCertificateShowRequest $request, string $uuid)
    {
        $user_certificate = UserCertificate::with([
            'user.personal',
            'user.contact',
            'user.employment.office',
            'user.employment.position',
            'user.employment.department',
            'user.emergency',
        ])->where('uuid', $uuid)->active()->firstOrFail();

        return self::response(new UserCertificateResource($user_certificate));
    }
}
