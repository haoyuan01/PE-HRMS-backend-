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
