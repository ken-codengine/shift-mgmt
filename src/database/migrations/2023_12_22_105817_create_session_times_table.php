<?php

use Carbon\CarbonImmutable;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('session_times', function (Blueprint $table) {
            $table->id();
            $table->string('start_time');
            $table->string('end_time');
            $table->timestamps();
        });

        // $now = CarbonImmutable::now();
        // DB::table('session_times')->insert(
        //     [
        //         'id' => '1',
        //         'settion_time' => '11:00~13:00',
        //         'created_at' => $now,
        //         'updated_at' => $now,
        //     ],
        //     [
        //         'id' => '2',
        //         'settion_time' => '14:00~16:00',
        //         'created_at' => $now,
        //         'updated_at' => $now,
        //     ],
        // );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('session_times');
    }
};
