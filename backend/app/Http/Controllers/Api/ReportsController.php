<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GameRound;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportsController extends Controller
{
    public function stats(Request $request)
    {
        $today = Carbon::today();

        // 1. Total Bet (Today)
        $totalBet = GameRound::whereDate('created_at', $today)->sum('bet_amount');

        // 2. Total Payout (Today)
        $totalPayout = GameRound::whereDate('created_at', $today)->sum('payout_amount');

        // 3. GGR
        $ggr = $totalBet - $totalPayout;

        // 4. DAU (Unique users who played today)
        $dau = GameRound::whereDate('created_at', $today)->distinct('user_id')->count('user_id');

        // 5. Total Users
        $totalUsers = User::where('role', '!=', 'admin')->count();

        // 6. System Float (Total User Balance)
        $totalBalance = User::where('role', '!=', 'admin')->sum('balance');

        return response()->json([
            'today' => [
                'total_bet' => $totalBet,
                'total_payout' => $totalPayout,
                'ggr' => $ggr,
                'dau' => $dau,
            ],
            'system' => [
                'total_users' => $totalUsers,
                'total_balance' => $totalBalance,
            ]
        ]);
    }

    public function trends(Request $request)
    {
        $days = 30;
        $startDate = Carbon::today()->subDays($days);

        $stats = GameRound::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('SUM(bet_amount) as total_bet'),
            DB::raw('SUM(payout_amount) as total_payout'),
            DB::raw('COUNT(DISTINCT user_id) as dau')
        )
        ->where('created_at', '>=', $startDate)
        ->groupBy(DB::raw('DATE(created_at)'))
        ->orderBy('date', 'asc')
        ->get();

        // Fill missing dates with 0
        $data = [];
        $currentDate = $startDate->copy();
        $today = Carbon::today();

        while ($currentDate <= $today) {
            $dateStr = $currentDate->format('Y-m-d');
            $record = $stats->firstWhere('date', $dateStr);

            $data[] = [
                'date' => $dateStr,
                'total_bet' => $record ? (float)$record->total_bet : 0,
                'total_payout' => $record ? (float)$record->total_payout : 0,
                'ggr' => $record ? ((float)$record->total_bet - (float)$record->total_payout) : 0,
                'dau' => $record ? (int)$record->dau : 0,
            ];

            $currentDate->addDay();
        }

        return response()->json($data);
    }

    public function gameStats(Request $request)
    {
        $stats = GameRound::select(
            'game_type',
            DB::raw('SUM(bet_amount) as total_bet'),
            DB::raw('COUNT(*) as total_rounds')
        )
        ->groupBy('game_type')
        ->orderBy('total_bet', 'desc')
        ->get();

        return response()->json($stats);
    }
}
