<?php

namespace Database\Seeders;

use App\Models\SessionTime;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SessionTimeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $lists = [
            '11:00' => '13:00',
            '14:00' => '16:00',
            '18:00' => '20:00'
        ];

        SessionTime::truncate();
        foreach ($lists as $start_time => $end_time) {
            SessionTime::create([
                'start_time' => $start_time,
                'end_time' => $end_time
            ]);
        }
    }
}
