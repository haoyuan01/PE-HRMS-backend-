<?php

namespace Database\Seeders;

use App\Constants\StatusCodeConstants;
use App\Models\Department;
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
                'name'          => 'Account',
                'description'   => 'Account Department',
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
                'description'   => 'Technician Position',
                'is_active'     => StatusCodeConstants::ACTIVE,
                'created_by'    => 'system',
                'created_at'    => now(),
                'updated_by'    => 'system',
                'updated_at'    => now(),
            ]);
        }

    }
}
