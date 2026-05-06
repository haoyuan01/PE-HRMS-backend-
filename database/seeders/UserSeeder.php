<?php

namespace Database\Seeders;

use App\Constants\StatusCodeConstants;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
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
                    'username' => 'admin',
                    'email' => 'admin@gmail.com',
                    'password' => '123456',
                ],
                [
                    'username' => 'employee',
                    'email' => 'employee@gmail.com',
                    'password' => '123456',
                ],
            ];

            foreach($users as $user)
            {
                User::create([
                    'uuid'          => (string) Str::uuid(),
                    'username'      => $user['username'],
                    'email'         => $user['email'],
                    'password'      => bcrypt($user['password']),
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
                ],

                // request log
                [
                    'code' => 'request_log_read',
                    'name' => 'Read Request Log',
                ],

                // error log
                [
                    'code' => 'error_log_read',
                    'name' => 'Read Error Log',
                ],

                // configuration
                [
                    'code' => 'configuration_read',
                    'name' => 'Read Configuration',
                ],
                [
                    'code' => 'configuration_update',
                    'name' => 'Update Configuration',
                ],

                // role
                [
                    'code' => 'role_read',
                    'name' => 'Read Role',
                ],
                [
                    'code' => 'role_create',
                    'name' => 'Create Role',
                ],
                [
                    'code' => 'role_update',
                    'name' => 'Update Role',
                ],
                [
                    'code' => 'role_delete',
                    'name' => 'Delete Role',
                ],

                // department
                [
                    'code' => 'department_read',
                    'name' => 'Read Department',
                ],
                [
                    'code' => 'department_create',
                    'name' => 'Create Department',
                ],
                [
                    'code' => 'department_update',
                    'name' => 'Update Department',
                ],
                [
                    'code' => 'department_delete',
                    'name' => 'Delete Department',
                ],

                // position
                [
                    'code' => 'position_read',
                    'name' => 'Read Position',
                ],
                [
                    'code' => 'position_create',
                    'name' => 'Create Position',
                ],
                [
                    'code' => 'position_update',
                    'name' => 'Update Position',
                ],
                [
                    'code' => 'position_delete',
                    'name' => 'Delete Position',
                ],

                // office
                [
                    'code' => 'office_read',
                    'name' => 'Read Office',
                ],
                [
                    'code' => 'office_create',
                    'name' => 'Create Office',
                ],
                [
                    'code' => 'office_update',
                    'name' => 'Update Office',
                ],
                [
                    'code' => 'office_delete',
                    'name' => 'Delete Office',
                ],
                
                //announcement
                [
                    'code' => 'announcement_read',
                    'name' => 'Read Announcement',
                ],
                [
                    'code' => 'announcement_create',
                    'name' => 'Create Announcement',
                ],
                [
                    'code' => 'announcement_update',
                    'name' => 'Update Announcement',
                ],
                [
                    'code' => 'announcement_delete',
                    'name' => 'Delete Announcement',
                ],

                // user
                [
                    'code' => 'user_read',
                    'name' => 'Read User',
                ],
                [
                    'code' => 'user_create',
                    'name' => 'Create User',
                ],
                [
                    'code' => 'user_update',
                    'name' => 'Update User',
                ],
                [
                    'code' => 'user_delete',
                    'name' => 'Delete User',
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
            $admin_user = User::where('username', 'admin')->first();
            $employee_user = User::where('username', 'employee')->first();
            $admin_user->assignRole($admin);
            $employee_user->assignRole($employee);
        }


    }
}
