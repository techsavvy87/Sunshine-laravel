<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class ServiceCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('service_categories')->insert([
            'name' => 'Grooming',
            'description' => 'Pet grooming services',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);
        DB::table('service_categories')->insert([
            'name' => 'Boarding',
            'description' => 'Pet boarding services',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);
    }
}
