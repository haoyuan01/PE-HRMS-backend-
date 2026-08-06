<?php

namespace App\Http\Controllers\BE;

use App\Constants\StatusCodeConstants;
use App\Exceptions\AppException;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserIndexRequest;
use App\Filters\UserFilter;
use App\Http\Requests\UserShowRequest;
use App\Http\Requests\UserStoreRequest;
use App\Http\Requests\UserUpdatePasscodeRequest;
use App\Http\Requests\UserUpdatePasswordRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Http\Requests\UserUpdateStatusRequest;
use App\Http\Resources\UserResource;
use App\Models\LeaveEntitlement;
use App\Models\LeavePolicy;
use App\Models\Department;
use App\Models\Office;
use App\Models\Position;
use App\Models\Role;
use App\Models\User;
use App\Services\AuthService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
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
        ]);
        
        $user = $this->user_filter->apply($request, $request->size, $user);
        
        return self::responsePaginated(UserResource::collection($user), $user);
    }

    public function store(UserStoreRequest $request)
    {
        DB::beginTransaction();

        try {
            // create user
            $user = User::create([
                'uuid' => self::uuid(),
                'email' => $request->email,
                'password' => $request->password ? bcrypt($request->password) : null,
                'passcode' => $request->passcode ? bcrypt($request->passcode) : null,
                'is_active' => StatusCodeConstants::ACTIVE,
                'created_by' => self::auth()->uuid,
                'created_at' => self::currentDateTime(),
                'updated_by' => self::auth()->uuid,
                'updated_at' => self::currentDateTime(),
            ]);

            // assign role
            $role = Role::findByUuid($request->role_uuid);
            $user->assignRole($role);

            // create personal
            if ($request->has('personal') && $request->input('personal'))
            {
                $image_path = null;
                if ($request->hasFile('personal.image'))
                {
                    $file = $request->file('personal.image');

                    $filename = time() . '_' . self::uuid() . '.' . $file->getClientOriginalExtension();

                    $image_path = $file->storeAs('users', $filename, 'public');
                }

                $user->personal()->create([
                    'uuid' => self::uuid(),
                    'user_id' => $user->id,
                    'full_name' => $request->input('personal.full_name'),
                    'first_name' => $request->input('personal.first_name'),
                    'last_name' => $request->input('personal.last_name'),
                    'identity_number' => $request->input('personal.identity_number'),
                    'passport_number' => $request->input('personal.passport_number'),
                    'passport_expiry_date' => $request->input('personal.passport_expiry_date'),
                    'blood_type' => $request->input('personal.blood_type'),
                    'image_path' => $image_path,
                    'gender' => $request->input('personal.gender'),
                    'is_married' => $request->input('personal.is_married') ? StatusCodeConstants::ACTIVE : StatusCodeConstants::INACTIVE,
                    'is_active' => StatusCodeConstants::ACTIVE,
                    'created_by' => self::auth()->uuid,
                    'created_at' => self::currentDateTime(),
                    'updated_by' => self::auth()->uuid,
                    'updated_at' => self::currentDateTime(),
                ]);
            }

            // create contact
            if ($request->has('contact') && $request->input('contact'))
            {
                $user->contact()->create([
                    'uuid' => self::uuid(),
                    'user_id' => $user->id,
                    'company_email' => $request->input('contact.company_email'),
                    'phone_number' => $request->input('contact.phone_number'),
                    'address_1' => $request->input('contact.address_1'),
                    'address_2' => $request->input('contact.address_2'),
                    'address_3' => $request->input('contact.address_3'),
                    'city' => $request->input('contact.city'),
                    'state' => $request->input('contact.state'),
                    'postcode' => $request->input('contact.postcode'),
                    'country' => $request->input('contact.country'),
                    'is_active' => StatusCodeConstants::ACTIVE,
                    'created_by' => self::auth()->uuid,
                    'created_at' => self::currentDateTime(),
                    'updated_by' => self::auth()->uuid,
                    'updated_at' => self::currentDateTime(),
                ]);
            }

            // create emergency
            if ($request->has('emergency') && $request->input('emergency'))
            {
                $user->emergency()->create([
                    'uuid' => self::uuid(),
                    'user_id' => $user->id,
                    'name' => $request->input('emergency.name'),
                    'relationship' => $request->input('emergency.relationship'),
                    'phone_number' => $request->input('emergency.phone_number'),
                    'is_active' => StatusCodeConstants::ACTIVE,
                    'created_by' => self::auth()->uuid,
                    'created_at' => self::currentDateTime(),
                    'updated_by' => self::auth()->uuid,
                    'updated_at' => self::currentDateTime(),
                ]);
            }

            $user_employment = null;

            // create employment
            if ($request->has('employment') && $request->input('employment'))
            {
                $position_count = ($request->input('employment.is_director') ? 1 : 0) + ($request->input('employment.is_manager') ? 1 : 0) + ($request->input('employment.is_accountant') ? 1 : 0);

                throw_if($position_count > 1, AppException::class, 'User can only have one position');

                $office = null;
                $position = null;
                $department = null;
                
                if ($request->input('employment.office_uuid'))
                {
                    $office = Office::findByUuid($request->input('employment.office_uuid'), false);
                }

                if ($request->input('employment.position_uuid'))
                {
                    $position = Position::findByUuid($request->input('employment.position_uuid'), false);
                }

                if ($request->input('employment.department_uuid'))
                {
                    $department = Department::findByUuid($request->input('employment.department_uuid'), false);
                }

                $user_employment = $user->employment()->create([
                    'uuid' => self::uuid(),
                    'user_id' => $user->id,
                    'position_id' => $position?->id,
                    'department_id' => $department?->id,
                    'office_id' => $office?->id,
                    'joined_date' => $request->input('employment.joined_date'),
                    'is_director' => $request->input('employment.is_director') ? StatusCodeConstants::ACTIVE : StatusCodeConstants::INACTIVE,
                    'is_manager' => $request->input('employment.is_manager') ? StatusCodeConstants::ACTIVE : StatusCodeConstants::INACTIVE,
                    'is_accountant' => $request->input('employment.is_accountant') ? StatusCodeConstants::ACTIVE : StatusCodeConstants::INACTIVE,
                    'is_active' => StatusCodeConstants::ACTIVE,
                    'created_by' => self::auth()->uuid,
                    'created_at' => self::currentDateTime(),
                    'updated_by' => self::auth()->uuid,
                    'updated_at' => self::currentDateTime(),
                ]);
            }

            // create leave entitlement
            $year = self::currentDateTime()->format('Y');
            $years_of_service = $user_employment?->joined_date ? Carbon::parse($user_employment->joined_date)->diffInYears(self::currentDateTime()) : 0;
            $leave_policies = LeavePolicy::with(['leavePolicyTiers'])->active()->get();

            foreach($leave_policies as $leave_policy)
            {
                $leave_policy_tier = $leave_policy->leavePolicyTiers
                    ->where('service_year_from', '<=', $years_of_service)
                    ->filter(function($tier) use ($years_of_service) {
                        return $tier->service_year_to === null || $tier->service_year_to > $years_of_service;
                    })
                    ->first();

                if (!$leave_policy_tier)
                {
                    $leave_policy_tier = $leave_policy->leavePolicyTiers->first();
                }

                $entitled_days = $leave_policy_tier?->entitlement_days ?? 0;
                $carry_forward_expiry_date = null;

                if ($leave_policy->carry_forward_expiry_month && $leave_policy->carry_forward_expiry_date)
                {
                    $carry_forward_expiry_month = Carbon::create($year + 1, $leave_policy->carry_forward_expiry_month, 1);
                    $carry_forward_expiry_date = $carry_forward_expiry_month
                        ->copy()
                        ->day(min($leave_policy->carry_forward_expiry_date, $carry_forward_expiry_month->daysInMonth))
                        ->format('Y-m-d');
                }

                $leave_entitlement = LeaveEntitlement::where('user_id', $user->id)
                    ->where('leave_policy_id', $leave_policy->id)
                    ->where('year', $year)
                    ->first();

                if (!$leave_entitlement)
                {
                    LeaveEntitlement::create([
                        'uuid' => self::uuid(),
                        'user_id' => $user->id,
                        'leave_policy_id' => $leave_policy->id,
                        'year' => $year,
                        'entitled_days' => $entitled_days,
                        'used_days' => 0,
                        'balance_days' => $entitled_days,
                        'carried_forward_days' => 0,
                        'carry_forward_expiry_date' => $carry_forward_expiry_date,
                        'is_active' => StatusCodeConstants::ACTIVE,
                        'created_by' => self::auth()->uuid,
                        'created_at' => self::currentDateTime(),
                        'updated_by' => self::auth()->uuid,
                        'updated_at' => self::currentDateTime(),
                    ]);
                }
            }

            $user->load(['personal', 'employment', 'contact', 'emergency', 'certificates', 'roles.permissions']);

            DB::commit();

            return self::response(new UserResource($user));

        } catch (\Exception $exception) {
            DB::rollback();
            throw $exception;
        }
    }

    public function update(UserUpdateRequest $request, string $uuid)
    {
        $user = User::findByUuid($uuid);

        DB::beginTransaction();

        try {
            // update user
            $user->update([
                'email' => $request->input('email'),
                'password' => $request->input('password') ? bcrypt($request->input('password')) : $user->password,
                'passcode' => $request->input('passcode') ? bcrypt($request->input('passcode')) : $user->passcode,
                'updated_by' => self::auth()->uuid,
                'updated_at' => self::currentDateTime(),
            ]);

            // update role
            if ($request->has('role_uuid') && $request->input('role_uuid'))
            {
                $role = Role::findByUuid($request->input('role_uuid'));
                $user->roles()->sync([$role->id]);
            }

            // update personal
            if ($request->has('personal') && $request->input('personal'))
            {
                $personal = [
                    'user_id' => $user->id,
                    'full_name' => $request->input('personal.full_name'),
                    'first_name' => $request->input('personal.first_name'),
                    'last_name' => $request->input('personal.last_name'),
                    'identity_number' => $request->input('personal.identity_number'),
                    'passport_number' => $request->input('personal.passport_number'),
                    'passport_expiry_date' => $request->input('personal.passport_expiry_date'),
                    'blood_type' => $request->input('personal.blood_type'),
                    'gender' => $request->input('personal.gender'),
                    'is_married' => $request->input('personal.is_married') ? StatusCodeConstants::ACTIVE : StatusCodeConstants::INACTIVE,
                    'updated_by' => self::auth()->uuid,
                    'updated_at' => self::currentDateTime(),
                ];

                // if image not uploaded, keep the existing image
                if ($request->hasFile('personal.image'))
                {
                    if ($user->personal && $user->personal->image_path)
                    {
                        Storage::disk('public')->delete($user->personal->image_path);
                    }

                    $file = $request->file('personal.image');

                    $filename = time() . '_' . self::uuid() . '.' . $file->getClientOriginalExtension();

                    $personal['image_path'] = $file->storeAs('users', $filename, 'public');
                }

                // update or create personal
                if ($user->personal)
                {
                    $user->personal->update($personal);
                }
                else
                {
                    $user->personal()->create([
                        ...$personal,
                        'uuid' => self::uuid(),
                        'is_active' => StatusCodeConstants::ACTIVE,
                        'created_by' => self::auth()->uuid,
                        'created_at' => self::currentDateTime(),
                    ]);
                }
            }

            // update contact
            if ($request->has('contact') && $request->input('contact'))
            {
                $contact = [
                    'user_id' => $user->id,
                    'company_email' => $request->input('contact.company_email'),
                    'phone_number' => $request->input('contact.phone_number'),
                    'address_1' => $request->input('contact.address_1'),
                    'address_2' => $request->input('contact.address_2'),
                    'address_3' => $request->input('contact.address_3'),
                    'city' => $request->input('contact.city'),
                    'state' => $request->input('contact.state'),
                    'postcode' => $request->input('contact.postcode'),
                    'country' => $request->input('contact.country'),
                    'updated_by' => self::auth()->uuid,
                    'updated_at' => self::currentDateTime(),
                ];

                // update or create contact
                if ($user->contact)
                {
                    $user->contact->update($contact);
                }
                else
                {
                    $user->contact()->create([
                        ...$contact,
                        'uuid' => self::uuid(),
                        'is_active' => StatusCodeConstants::ACTIVE,
                        'created_by' => self::auth()->uuid,
                        'created_at' => self::currentDateTime(),
                    ]);
                }
            }

            // update emergency
            if ($request->has('emergency') && $request->input('emergency'))
            {
                $emergency = [
                    'user_id' => $user->id,
                    'name' => $request->input('emergency.name'),
                    'relationship' => $request->input('emergency.relationship'),
                    'phone_number' => $request->input('emergency.phone_number'),
                    'updated_by' => self::auth()->uuid,
                    'updated_at' => self::currentDateTime(),
                ];
                
                // update or create emergency
                if ($user->emergency)
                {
                    $user->emergency->update($emergency);
                }
                else
                {
                    $user->emergency()->create([
                        ...$emergency,
                        'uuid' => self::uuid(),
                        'is_active' => StatusCodeConstants::ACTIVE,
                        'created_by' => self::auth()->uuid,
                        'created_at' => self::currentDateTime(),
                    ]);
                }
            }

            // update employment
            if ($request->has('employment') && $request->input('employment'))
            {
                $position_count = ($request->input('employment.is_director') ? 1 : 0) + ($request->input('employment.is_manager') ? 1 : 0) + ($request->input('employment.is_accountant') ? 1 : 0);

                throw_if($position_count > 1, AppException::class, 'User can only have one position');

                $office = null;
                $position = null;
                $department = null;
                
                if ($request->input('employment.office_uuid'))
                {
                    $office = Office::findByUuid($request->input('employment.office_uuid'), false);
                }
                if ($request->input('employment.position_uuid'))
                {
                    $position = Position::findByUuid($request->input('employment.position_uuid'), false);
                }
                if ($request->input('employment.department_uuid'))
                {
                    $department = Department::findByUuid($request->input('employment.department_uuid'), false);
                }
                
                $employment = [
                    'user_id' => $user->id,
                    'position_id' => $position?->id,
                    'department_id' => $department?->id,
                    'office_id' => $office?->id,
                    'joined_date' => $request->input('employment.joined_date'),
                    'is_director' => $request->input('employment.is_director') ? StatusCodeConstants::ACTIVE : StatusCodeConstants::INACTIVE,
                    'is_manager' => $request->input('employment.is_manager') ? StatusCodeConstants::ACTIVE : StatusCodeConstants::INACTIVE,
                    'is_accountant' => $request->input('employment.is_accountant') ? StatusCodeConstants::ACTIVE : StatusCodeConstants::INACTIVE,
                    'updated_by' => self::auth()->uuid,
                    'updated_at' => self::currentDateTime(),
                ];

                // update or create employment
                if ($user->employment)
                {
                    $user->employment->update($employment);
                }
                else
                {
                    $user->employment()->create([
                        ...$employment,
                        'uuid' => self::uuid(),
                        'is_active' => StatusCodeConstants::ACTIVE,
                        'created_by' => self::auth()->uuid,
                        'created_at' => self::currentDateTime(),
                    ]);
                }
            }

            $user->load(['personal', 'employment', 'contact', 'emergency', 'certificates', 'roles.permissions']);

            DB::commit();

            return self::response(new UserResource($user));

        } catch (\Exception $exception) {
            DB::rollback();
            throw $exception;
        }
    }

    public function updateStatus(UserUpdateStatusRequest $request, string $uuid)
    {
        $user = User::findByUuid($uuid, true, false);
        
        $user->update([
            'is_active' => $request->is_active,
            'updated_by' => self::auth()->uuid,
            'updated_at' => self::currentDateTime(),
        ]);
        
        return self::response(new UserResource($user));
    }

    public function show(UserShowRequest $request, string $uuid)
    {
        $user = User::findByUuid($uuid, true, false);
        
        return self::response(new UserResource($user));
    }

    public function updatePassword(UserUpdatePasswordRequest $request, string $uuid)
    {
        $user = User::findByUuid($uuid);
        
        $user->update([
            'password' => bcrypt($request->password),
            'updated_by' => self::auth()->uuid,
            'updated_at' => self::currentDateTime(),
        ]);
        
        return self::response(new UserResource($user));
    }

    public function updatePasscode(UserUpdatePasscodeRequest $request, string $uuid)
    {
        $user = User::findByUuid($uuid);

        $this->auth_service->validatePasscode($user, $request->old_passcode);

        DB::beginTransaction();

        try {
            
            $user->update([
                'passcode' => bcrypt($request->passcode),
                'updated_by' => self::auth()->uuid,
                'updated_at' => self::currentDateTime(),
            ]);
            
            DB::commit();
            
            return self::response(new UserResource($user));

        } catch (\Exception $exception) {
            DB::rollback();
            throw $exception;
        }
    }
}
