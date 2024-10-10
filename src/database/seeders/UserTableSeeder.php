<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        //ここから追加
        $users = [
            '早田' => 'red',
            '山田' => 'blue',
            '田中' => 'green'
        ];

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        User::truncate();
        foreach ($users as $user => $color) {

            User::create([
                'name' => $user,
                // 'email' => $email.'@example.com',
                'password' => Hash::make('0000'),
                'color' => $color,
                'created_at' => '2022-12-30 11:22:33',
                'updated_at' => '2022-12-31 23:58:59',
            ]);
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        //ここまで追加
    }
}
