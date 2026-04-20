<?php

namespace Database\Seeders;

use App\Constants\StatusCodeConstants;
use App\Models\Department;
use App\Models\OfficeBranch;
use App\Models\Position;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;

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
            Department::create([
                'uuid'          => Str::uuid(),
                'name'          => 'HR',
                'description'   => 'Human Resource Department',
                'is_active'     => StatusCodeConstants::ACTIVE,
                'created_by'    => 'system',
                'created_at'    => now(),
                'updated_by'    => 'system',
                'updated_at'    => now(),
            ]);
        }

        // position
        if (Schema::hasTable('positions'))
        {
            Position::create([
                'uuid'          => Str::uuid(),
                'name'          => 'Technician',
                'description'   => 'Technical position',
                'is_active'     => StatusCodeConstants::ACTIVE,
                'created_by'    => 'system',
                'created_at'    => now(),
                'updated_by'    => 'system',
                'updated_at'    => now(),
            ]);
        }

        // office branch
        if (Schema::hasTable('office_branches'))
        {
            OfficeBranch::create([
                'uuid'          => Str::uuid(),
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
                'created_at'    => now(),
                'updated_by'    => 'system',
                'updated_at'    => now(),
            ]);
        }

    }
}
