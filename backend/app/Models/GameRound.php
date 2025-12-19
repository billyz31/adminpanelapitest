<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameRound extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'game_type',
        'bet_amount',
        'payout_amount',
        'result_data',
        'status',
    ];

    protected $casts = [
        'result_data' => 'array',
        'bet_amount' => 'decimal:4',
        'payout_amount' => 'decimal:4',
    ];
}
