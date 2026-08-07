<?php

namespace Database\Seeders;

use App\Constants\StatusCodeConstants;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Models\UserContact;
use App\Models\UserEmergency;
use App\Models\UserEmployment;
use App\Models\UserPersonal;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // create admin user
        if (Schema::hasTable('users'))
        {
            $users = [
                [
                    'email' => 'admin@gmail.com',
                    'password' => '123456',
                    'passcode' => '123456',
                    'is_manager' => StatusCodeConstants::ACTIVE,
                    'is_director' => StatusCodeConstants::ACTIVE,
                    'is_accountant' => StatusCodeConstants::ACTIVE,
                ],
                [
                    'email' => 'employee@gmail.com',
                    'password' => '123456',
                    'passcode' => '123456',
                    'is_manager' => StatusCodeConstants::INACTIVE,
                    'is_director' => StatusCodeConstants::INACTIVE,
                    'is_accountant' => StatusCodeConstants::INACTIVE,
                ],
            ];

            foreach($users as $user)
            {
                $user_id = User::create([
                    'uuid'          => (string) Str::uuid(),
                    'email'         => $user['email'],
                    'password'      => bcrypt($user['password']),
                    'passcode'      => bcrypt($user['passcode']),
                    'is_active'     => StatusCodeConstants::ACTIVE,
                    'created_by'    => 'system',
                    'created_at'    => Carbon::now(),
                    'updated_by'    => 'system',
                    'updated_at'    => Carbon::now(),
                ]);

                UserEmployment::create([
                    'uuid'          => (string) Str::uuid(),
                    'user_id'       => $user_id->id,
                    'is_manager'    => $user['is_manager'],
                    'is_director'   => $user['is_director'],
                    'is_accountant' => $user['is_accountant'],
                    'is_active'     => StatusCodeConstants::ACTIVE,
                    'created_by'    => 'system',
                    'created_at'    => Carbon::now(),
                    'updated_by'    => 'system',
                    'updated_at'    => Carbon::now(),
                ]);
            }
        }

        if (Schema::hasTable('permissions'))
        {
            $permissions = [

                // activity log
                [
                    'code' => 'activity_log_read',
                    'name' => 'Read Activity Log',
                    'module' => 'activity_log',
                ],

                // request log
                [
                    'code' => 'request_log_read',
                    'name' => 'Read Request Log',
                    'module' => 'request_log',
                ],

                // error log
                [
                    'code' => 'error_log_read',
                    'name' => 'Read Error Log',
                    'module' => 'error_log',
                ],

                // configuration
                [
                    'code' => 'configuration_read',
                    'name' => 'Read Configuration',
                    'module' => 'configuration',
                ],
                [
                    'code' => 'configuration_update',
                    'name' => 'Update Configuration',
                    'module' => 'configuration',
                ],

                // role
                [
                    'code' => 'role_read',
                    'name' => 'Read Role',
                    'module' => 'role',
                ],
                [
                    'code' => 'role_create',
                    'name' => 'Create Role',
                    'module' => 'role',
                ],
                [
                    'code' => 'role_update',
                    'name' => 'Update Role',
                    'module' => 'role',
                ],
                [
                    'code' => 'role_delete',
                    'name' => 'Delete Role',
                    'module' => 'role',
                ],

                // department
                [
                    'code' => 'department_read',
                    'name' => 'Read Department',
                    'module' => 'department',
                ],
                [
                    'code' => 'department_create',
                    'name' => 'Create Department',
                    'module' => 'department',
                ],
                [
                    'code' => 'department_update',
                    'name' => 'Update Department',
                    'module' => 'department',
                ],
                [
                    'code' => 'department_delete',
                    'name' => 'Delete Department',
                    'module' => 'department',
                ],

                // position
                [
                    'code' => 'position_read',
                    'name' => 'Read Position',
                    'module' => 'position',
                ],
                [
                    'code' => 'position_create',
                    'name' => 'Create Position',
                    'module' => 'position',
                ],
                [
                    'code' => 'position_update',
                    'name' => 'Update Position',
                    'module' => 'position',
                ],
                [
                    'code' => 'position_delete',
                    'name' => 'Delete Position',
                    'module' => 'position',
                ],

                // office
                [
                    'code' => 'office_read',
                    'name' => 'Read Office',
                    'module' => 'office',
                ],
                [
                    'code' => 'office_create',
                    'name' => 'Create Office',
                    'module' => 'office',
                ],
                [
                    'code' => 'office_update',
                    'name' => 'Update Office',
                    'module' => 'office',
                ],
                [
                    'code' => 'office_delete',
                    'name' => 'Delete Office',
                    'module' => 'office',
                ],
                
                //announcement
                [
                    'code' => 'announcement_read',
                    'name' => 'Read Announcement',
                    'module' => 'announcement',
                ],
                [
                    'code' => 'announcement_create',
                    'name' => 'Create Announcement',
                    'module' => 'announcement',
                ],
                [
                    'code' => 'announcement_update',
                    'name' => 'Update Announcement',
                    'module' => 'announcement',
                ],
                [
                    'code' => 'announcement_delete',
                    'name' => 'Delete Announcement',
                    'module' => 'announcement',
                ],

                // upcoming event
                [
                    'code' => 'upcoming_event_read',
                    'name' => 'Read Upcoming Event',
                    'module' => 'upcoming_event',
                ],
                [
                    'code' => 'upcoming_event_create',
                    'name' => 'Create Upcoming Event',
                    'module' => 'upcoming_event',
                ],
                [
                    'code' => 'upcoming_event_update',
                    'name' => 'Update Upcoming Event',
                    'module' => 'upcoming_event',
                ],
                [
                    'code' => 'upcoming_event_delete',
                    'name' => 'Delete Upcoming Event',
                    'module' => 'upcoming_event',
                ],

                // user
                [
                    'code' => 'user_read',
                    'name' => 'Read User',
                    'module' => 'user',
                ],
                [
                    'code' => 'user_create',
                    'name' => 'Create User',
                    'module' => 'user',
                ],
                [
                    'code' => 'user_update',
                    'name' => 'Update User',
                    'module' => 'user',
                ],
                [
                    'code' => 'user_delete',
                    'name' => 'Delete User',
                    'module' => 'user',
                ],

                // claim header
                [
                    'code' => 'claim_header_read',
                    'name' => 'Read Claim Header',
                    'module' => 'claim',
                ],
                [
                    'code' => 'claim_header_create',
                    'name' => 'Create Claim Header',
                    'module' => 'claim',
                ],
                [
                    'code' => 'claim_header_update',
                    'name' => 'Update Claim Header',
                    'module' => 'claim',
                ],
                [
                    'code' => 'claim_header_delete',
                    'name' => 'Delete Claim Header',
                    'module' => 'claim',
                ],

                // leave policy
                [
                    'code' => 'leave_policy_read',
                    'name' => 'Read Leave Policy',
                    'module' => 'leave_policy',
                ],
                [
                    'code' => 'leave_policy_create',
                    'name' => 'Create Leave Policy',
                    'module' => 'leave_policy',
                ],
                [
                    'code' => 'leave_policy_update',
                    'name' => 'Update Leave Policy',
                    'module' => 'leave_policy',
                ],
                [
                    'code' => 'leave_policy_delete',
                    'name' => 'Delete Leave Policy',
                    'module' => 'leave_policy',
                ],
                [
                    'code' => 'leave_entitlement_read',
                    'name' => 'Read Leave Entitlement',
                    'module' => 'leave_entitlement',
                ],
                [
                    'code' => 'leave_entitlement_create',
                    'name' => 'Create Leave Entitlement',
                    'module' => 'leave_entitlement',
                ],
                [
                    'code' => 'leave_entitlement_update',
                    'name' => 'Update Leave Entitlement',
                    'module' => 'leave_entitlement',
                ],
                [
                    'code' => 'leave_entitlement_delete',
                    'name' => 'Delete Leave Entitlement',
                    'module' => 'leave_entitlement',
                ],

                // overtime
                [
                    'code' => 'overtime_read',
                    'name' => 'Read Overtime',
                    'module' => 'overtime',
                ],
                [
                    'code' => 'overtime_create',
                    'name' => 'Create Overtime',
                    'module' => 'overtime',
                ],
                [
                    'code' => 'overtime_update',
                    'name' => 'Update Overtime',
                    'module' => 'overtime',
                ],
                [
                    'code' => 'overtime_delete',
                    'name' => 'Delete Overtime',
                    'module' => 'overtime',
                ],

                // movement
                [
                    'code' => 'movement_read',
                    'name' => 'Read Movement',
                    'module' => 'movement',
                ],
                [
                    'code' => 'movement_create',
                    'name' => 'Create Movement',
                    'module' => 'movement',
                ],
                [
                    'code' => 'movement_update',
                    'name' => 'Update Movement',
                    'module' => 'movement',
                ],
                [
                    'code' => 'movement_delete',
                    'name' => 'Delete Movement',
                    'module' => 'movement',
                ],
            ];

            $roles = [
                [
                    'name' => 'admin',
                ],
                [
                    'name' => 'employee',
                ],
            ];

            foreach($permissions as $permission)
            {
                Permission::create([
                    'uuid' => (string) Str::uuid(),
                    'code' => $permission['code'],
                    'name' => $permission['name'],
                    'module' => $permission['module'] ?? null,
                    'guard_name' => 'web',
                    'created_by' => 'system',
                    'created_at' => Carbon::now(),
                    'updated_by' => 'system',
                    'updated_at' => Carbon::now(),
                ]);
            }

            foreach($roles as $role)
            {
                Role::create([
                    'uuid' => (string) Str::uuid(),
                    'name' => $role['name'],
                    'guard_name' => 'web',
                    'created_by' => 'system',
                    'created_at' => Carbon::now(),
                    'updated_by' => 'system',
                    'updated_at' => Carbon::now(),
                ]);
            }

            // get roles
            $admin = Role::where('name', 'admin')->first();
            $employee = Role::where('name', 'employee')->first();

            // assign permissions to roles
            $admin->givePermissionTo(Permission::all());
            $employee->givePermissionTo(Permission::where('code', 'user_read')->first());

            // assign role to user
            $admin_user = User::where('email', 'admin@gmail.com')->first();
            $employee_user = User::where('email', 'employee@gmail.com')->first();
            $admin_user->assignRole($admin);
            $employee_user->assignRole($employee);
        }


    }
}
