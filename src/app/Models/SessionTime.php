<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SessionTime extends Model
{
    use HasFactory;

    public static function getSessionTimes()
    {
        return self::select('start_time', 'end_time')
            ->orderBy('start_time', 'asc')
            ->get()
            ->map(function ($session) {
                return ['start_time' => $session['start_time'], 'end_time' => $session['end_time']];
            })
            ->all();
    }
}
