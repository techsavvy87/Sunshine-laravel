<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('roles')->insert([
            'title' => 'Owner',
            'description' => 'Owner role with full access',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);
        DB::table('roles')->insert([
            'title' => 'Manager',
            'description' => 'Manager role with restricted access',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);
        DB::table('roles')->insert([
            'title' => 'Staff Member',
            'description' => 'Staff Member role with restricted access',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);
        DB::table('roles')->insert([
            'title' => 'Customer',
            'description' => 'Customer role',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);
    }
}

