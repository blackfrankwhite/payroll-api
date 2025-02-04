<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PublicHolidaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        DB::table('georgian_public_holidays')->insert([
            // Fixed-date holidays (MM-DD format)
            ['month_day' => '01-01', 'name' => 'New Year\'s Day', 'full_date' => null],
            ['month_day' => '01-07', 'name' => 'Christmas Day', 'full_date' => null],
            ['month_day' => '05-09', 'name' => 'Victory Day', 'full_date' => null],
            ['month_day' => '08-28', 'name' => 'Saint Mary’s Day', 'full_date' => null],

            // Floating holidays (Full Date format for specific years)
            ['full_date' => '2024-05-05', 'name' => 'Easter Sunday', 'month_day' => null],
            ['full_date' => '2024-05-06', 'name' => 'Easter Monday', 'month_day' => null],
            ['full_date' => '2025-04-20', 'name' => 'Easter Sunday', 'month_day' => null],
            ['full_date' => '2025-04-21', 'name' => 'Easter Monday', 'month_day' => null],
        ]);
    }
}
