<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Transaction;
use App\Models\GameRound;

class AdminController extends Controller
{
    /**
     * Get all users.
     */
    public function getUsers()
    {
        $users = User::select('id', 'username', 'name', 'email', 'role', 'balance', 'is_active', 'created_at')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($users);
    }

    /**
     * Get system statistics.
     */
    public function getSystemStats()
    {
        $stats = [
            'total_users' => User::count(),
            'total_transactions' => Transaction::count(),
            'total_game_rounds' => GameRound::count(),
            'total_deposits' => Transaction::where('type', 'deposit')->sum('amount'),
            'total_withdrawals' => Transaction::where('type', 'withdraw')->sum('amount'),
        ];

        return response()->json($stats);
    }
}
