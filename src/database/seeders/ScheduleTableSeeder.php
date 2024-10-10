<?php

namespace Database\Seeders;

use App\Models\Schedule;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ScheduleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //ここから追加
        $lists = [
            '11:00 ひまわり 早田、田中、山田',
            '14:00 星月夜 早田、田中',
            '16:00 レモンのある静物 山田'
        ];

        Schedule::truncate();
        foreach ($lists as $list) {
            Schedule::create([
                'uuid' => (string)Str::uuid(),
                'date' => '2024-6-8',
                'text' => $list,
                'created_at' => '2022-12-30 11:22:33',
                'updated_at' => '2022-12-31 23:58:59',
            ]);
        }
        //ここまで追加
    }
}
