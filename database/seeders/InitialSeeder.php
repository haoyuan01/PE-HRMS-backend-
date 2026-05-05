<?php

namespace Database\Seeders;

use App\Constants\ConfigurationCodeConstants;
use App\Constants\StatusCodeConstants;
use App\Models\Configuration;
use App\Models\Department;
use App\Models\OfficeBranch;
use App\Models\Position;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class InitialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // department
        if (Schema::hasTable('departments'))
        {
            $departments = [
                'Admin',
                'Account',
                'Services',
                'Logistic',
                'Sales',
            ];
            
            foreach ($departments as $department)
            {
                Department::create([
                    'uuid'          => (string) Str::uuid(),
                    'name'          => $department,
                    'description'   => null,
                    'is_active'     => StatusCodeConstants::ACTIVE,
                    'created_by'    => 'system',
                    'created_at'    => Carbon::now(),
                    'updated_by'    => 'system',
                    'updated_at'    => Carbon::now(),
                ]);
            }
        }

        // position
        if (Schema::hasTable('positions'))
        {
            Position::create([
                'uuid'          => (string) Str::uuid(),
                'name'          => 'Technician',
                'description'   => 'Technical position',
                'is_active'     => StatusCodeConstants::ACTIVE,
                'created_by'    => 'system',
                'created_at'    => Carbon::now(),
                'updated_by'    => 'system',
                'updated_at'    => Carbon::now(),
            ]);
        }

        // office branch
        if (Schema::hasTable('office_branches'))
        {
            OfficeBranch::create([
                'uuid'          => (string) Str::uuid(),
                'name'          => 'Miri',
                'description'   => 'Petro-Excel Sdn Bhd provides installation & maintenance services for oil & gas related instruments & equipments.',
                'address_1'     => 'Lot 1236 & Lot 1237',
                'address_2'     => 'Jalan Lutong - Kuala Baram, Senadin',
                'address_3'     => 'Venture Light Industrial Park',
                'city'          => 'Miri',
                'state'         => 'Sarawak',
                'postcode'      => 98000,
                'country'       => 'Malaysia',
                'phone_number'  => 85652333,
                'phone_code'    => '+60',
                'phone_iso'     => 'MY',
                'fax_number'    => 85651222,
                'fax_code'      => '+60',
                'fax_iso'       => 'MY',
                'email'         => 'sales@petro-excel.com.my',
                'is_active'     => StatusCodeConstants::ACTIVE,
                'created_by'    => 'system',
                'created_at'    => Carbon::now(),
                'updated_by'    => 'system',
                'updated_at'    => Carbon::now(),
            ]);
        }

        // configurations
        if (Schema::hasTable('configurations'))
        {
            $configurations = [
                [
                    'key' => ConfigurationCodeConstants::AUTH_RATE_LIMIT,
                    'value' => 30,
                    'value_type' => ConfigurationCodeConstants::VALUE_TYPE_INTEGER,
                    'description' => 'Maximum number of login attempts allowed per hour',
                    'is_editable' => StatusCodeConstants::ACTIVE,
                ],
                [
                    'key' => ConfigurationCodeConstants::AUTH_TOKEN_EXPIRY_DAYS,
                    'value' => 30,
                    'value_type' => ConfigurationCodeConstants::VALUE_TYPE_INTEGER,
                    'description' => 'Number of days before authentication token expires',
                    'is_editable' => StatusCodeConstants::ACTIVE,
                ],
                [
                    'key' => ConfigurationCodeConstants::AUTH_LOGIN_MAX_ATTEMPTS,
                    'value' => 5,
                    'value_type' => ConfigurationCodeConstants::VALUE_TYPE_INTEGER,
                    'description' => 'Maximum number of login attempts before account is locked',
                    'is_editable' => StatusCodeConstants::ACTIVE,
                ],
                [
                    'key' => ConfigurationCodeConstants::AUTH_LOGIN_LOCKOUT_DURATION_MINUTES,
                    'value' => 5,
                    'value_type' => ConfigurationCodeConstants::VALUE_TYPE_INTEGER,
                    'description' => 'Number of minutes to lock account after maximum login attempts',
                    'is_editable' => StatusCodeConstants::ACTIVE,
                ],
                [
                    'key' => ConfigurationCodeConstants::IMAGE_MAX_SIZE_MB,
                    'value' => 5 * 1024, // 5 MB
                    'value_type' => ConfigurationCodeConstants::VALUE_TYPE_INTEGER,
                    'description' => 'Maximum image size in MB',
                    'is_editable' => StatusCodeConstants::ACTIVE,
                ],
                [
                    'key' => ConfigurationCodeConstants::IMAGE_ALLOWED_TYPES,
                    'value' => 'jpeg,png,jpg,gif,svg',
                    'value_type' => ConfigurationCodeConstants::VALUE_TYPE_STRING,
                    'description' => 'Allowed image types',
                    'is_editable' => StatusCodeConstants::INACTIVE,
                ],
            ];

            foreach($configurations as $configuration)
            {
                Configuration::create([
                    'uuid'          => Str::uuid(),
                    'key'           => $configuration['key'],
                    'value'         => $configuration['value'],
                    'value_type'    => $configuration['value_type'],
                    'description'   => null,
                    'is_viewable'   => StatusCodeConstants::ACTIVE,
                    'created_at'    => Carbon::now(),
                    'updated_at'    => Carbon::now(),
                ]);
            }
        }

    }
}
