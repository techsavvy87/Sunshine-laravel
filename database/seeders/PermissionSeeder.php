<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('permissions')->insert(['title' => 'Owner records', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
        DB::table('permissions')->insert(['title' => 'Pet records', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
        DB::table('permissions')->insert(['title' => 'Appointments', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
        DB::table('permissions')->insert(['title' => 'Inventory', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
        DB::table('permissions')->insert(['title' => 'Employee records', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
        DB::table('permissions')->insert(['title' => 'Roles and permissions', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
        DB::table('permissions')->insert(['title' => 'Holidays', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
        DB::table('permissions')->insert(['title' => 'Weight Ranges', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
        DB::table('permissions')->insert(['title' => 'Capacity', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
        DB::table('permissions')->insert(['title' => 'Credit Types', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
        DB::table('permissions')->insert(['title' => 'Service Categories', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
        DB::table('permissions')->insert(['title' => 'Services', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
        DB::table('permissions')->insert(['title' => 'Time Slots', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
        DB::table('permissions')->insert(['title' => 'Accept Payment', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
        DB::table('permissions')->insert(['title' => 'Send/Receive messages', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
        DB::table('permissions')->insert(['title' => 'Boardings', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
        DB::table('permissions')->insert(['title' => 'Groomings', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
        DB::table('permissions')->insert(['title' => 'Boarding Process', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
        DB::table('permissions')->insert(['title' => 'Customer Complaints Issues', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
        DB::table('permissions')->insert(['title' => 'Incidents', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
        DB::table('permissions')->insert(['title' => 'Maintenance', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
        DB::table('permissions')->insert(['title' => 'Notifications', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
        DB::table('permissions')->insert(['title' => 'Audit Log', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
        DB::table('permissions')->insert(['title' => 'Discounts', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
        DB::table('permissions')->insert(['title' => 'Pet Behaviors', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
        DB::table('permissions')->insert(['title' => 'Facility Address', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
        DB::table('permissions')->insert(['title' => 'Kennels', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
        DB::table('permissions')->insert(['title' => 'Rooms', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
    }
}