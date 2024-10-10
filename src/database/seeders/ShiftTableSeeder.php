<?php

namespace Database\Seeders;

use App\Models\Shift;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ShiftTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //ここから追加
        $users = [
            '1' => '⚪︎⚪︎×',
            '2' => '×⚪︎×',
            '3' => '××⚪︎',
            // '1' => '早田：出勤可能',
        ];

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Shift::truncate();
        foreach ($users as $user_id => $text) {

            Shift::create([
                'uuid' => (string)Str::uuid(),
                'user_id' => $user_id,
                // 'email' => $email.'@example.com',
                'date' => '2024-6-9',
                'text' => $text,
            ]);
        }
        Shift::create([
            'uuid' => (string)Str::uuid(),
            'user_id' => '1',
            // 'email' => $email.'@example.com',
            'date' => '2024-6-8',
            'text' => '××⚪︎',
        ]);
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        //ここまで追加
    }
}
