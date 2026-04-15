<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CoatTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('coat_types')->insert(['name' => 'Wire Haired', 'is_double_coated' => false, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
        DB::table('coat_types')->insert(['name' => 'Short Haired', 'is_double_coated' => false, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
        DB::table('coat_types')->insert(['name' => 'Smooth', 'is_double_coated' => false, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
        DB::table('coat_types')->insert(['name' => 'Corded', 'is_double_coated' => false, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
        DB::table('coat_types')->insert(['name' => 'Double Coat', 'is_double_coated' => true, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
        DB::table('coat_types')->insert(['name' => 'Fluffy', 'is_double_coated' => true, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
        DB::table('coat_types')->insert(['name' => 'Long hair', 'is_double_coated' => true, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
        DB::table('coat_types')->insert(['name' => 'Medium Hair', 'is_double_coated' => true, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
        DB::table('coat_types')->insert(['name' => 'Fluffy Short', 'is_double_coated' => true, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
        DB::table('coat_types')->insert(['name' => 'Fluffy Long', 'is_double_coated' => true, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
        DB::table('coat_types')->insert(['name' => 'Feathery Short', 'is_double_coated' => false, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
        DB::table('coat_types')->insert(['name' => 'Feathery Long', 'is_double_coated' => true, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
        DB::table('coat_types')->insert(['name' => 'Naked', 'is_double_coated' => false, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
        DB::table('coat_types')->insert(['name' => 'Fuzzy Short', 'is_double_coated' => false, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
        DB::table('coat_types')->insert(['name' => 'Thick Medium', 'is_double_coated' => true, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
        DB::table('coat_types')->insert(['name' => 'Silky', 'is_double_coated' => false, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
        DB::table('coat_types')->insert(['name' => 'Scruffy', 'is_double_coated' => false, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
        DB::table('coat_types')->insert(['name' => 'Fluffy Medium', 'is_double_coated' => true, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
        DB::table('coat_types')->insert(['name' => 'Shaved', 'is_double_coated' => false, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
        DB::table('coat_types')->insert(['name' => 'Curly Short', 'is_double_coated' => false, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
        DB::table('coat_types')->insert(['name' => 'Curly Medium', 'is_double_coated' => false, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
        DB::table('coat_types')->insert(['name' => 'Curly Long', 'is_double_coated' => false, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
        DB::table('coat_types')->insert(['name' => 'Feathery Medium', 'is_double_coated' => true, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
        DB::table('coat_types')->insert(['name' => 'Fuzzy Long', 'is_double_coated' => true, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
        DB::table('coat_types')->insert(['name' => 'Wavy', 'is_double_coated' => false, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
        DB::table('coat_types')->insert(['name' => 'Fuzzy Medium', 'is_double_coated' => true, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
        DB::table('coat_types')->insert(['name' => 'Short and Smooth', 'is_double_coated' => false, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
    }
}
