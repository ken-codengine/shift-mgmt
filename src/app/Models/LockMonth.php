<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LockMonth extends Model
{
    use HasFactory;

    public static function getLockMonths()
    {
        return self::select('year', 'month')
            ->get()
            ->map(function ($lockMonth) {
                $date = Carbon::create($lockMonth->year, $lockMonth->month, 1);
                return $date->format('Y年n月');
            })
            ->toArray();
    }
}
