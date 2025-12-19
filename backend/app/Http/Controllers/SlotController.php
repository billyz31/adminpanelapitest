<?php

namespace App\Http\Controllers;

use App\Models\GameRound;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SlotController extends Controller
{
    // 符號定義
    private $symbols = [
        ['id' => 0, 'name' => 'Cherry', 'icon' => '🍒', 'multiplier' => 2],
        ['id' => 1, 'name' => 'Lemon', 'icon' => '🍋', 'multiplier' => 3],
        ['id' => 2, 'name' => 'Grape', 'icon' => '🍇', 'multiplier' => 5],
        ['id' => 3, 'name' => 'Diamond', 'icon' => '💎', 'multiplier' => 10],
        ['id' => 4, 'name' => 'Seven', 'icon' => '7️⃣', 'multiplier' => 20],
    ];

    // 權重 (總和 100)
    private $weights = [
        0 => 40, // Cherry
        1 => 30, // Lemon
        2 => 20, // Grape
        3 => 8,  // Diamond
        4 => 2,  // Seven
    ];

    public function spin(Request $request)
    {
        $request->validate([
            'bet_amount' => 'required|numeric|min:1',
        ]);

        $user = $request->user();
        $betAmount = $request->bet_amount;

        // 檢查餘額
        if ($user->balance < $betAmount) {
            return response()->json(['message' => '餘額不足'], 400);
        }

        return DB::transaction(function () use ($user, $betAmount) {
            // 1. 扣除下注金額
            $balanceBefore = $user->balance;
            $user->balance -= $betAmount;
            $user->save();

            // 2. 產生遊戲結果 (3個轉軸)
            $reels = [];
            for ($i = 0; $i < 3; $i++) {
                $reels[] = $this->getRandomSymbol();
            }

            // 3. 計算中獎
            $winAmount = 0;
            $isWin = false;
            
            // 簡單規則：三個相同符號即中獎
            if ($reels[0]['id'] === $reels[1]['id'] && $reels[1]['id'] === $reels[2]['id']) {
                $isWin = true;
                $multiplier = $reels[0]['multiplier'];
                $winAmount = $betAmount * $multiplier;
            }

            // 4. 建立遊戲局數紀錄
            $gameRound = GameRound::create([
                'user_id' => $user->id,
                'game_type' => 'slot',
                'bet_amount' => $betAmount,
                'payout_amount' => $winAmount,
                'result_data' => $reels,
                'status' => 'completed',
            ]);

            // 5. 記錄下注交易
            Transaction::create([
                'user_id' => $user->id,
                'type' => 'bet',
                'amount' => -$betAmount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceBefore - $betAmount,
                'reference_id' => $gameRound->id,
                'description' => 'Slot Game Bet',
            ]);

            // 6. 如果中獎，派彩
            if ($isWin) {
                $balanceAfterWin = $user->balance + $winAmount;
                
                Transaction::create([
                    'user_id' => $user->id,
                    'type' => 'win',
                    'amount' => $winAmount,
                    'balance_before' => $user->balance,
                    'balance_after' => $balanceAfterWin,
                    'reference_id' => $gameRound->id,
                    'description' => 'Slot Game Win',
                ]);

                $user->balance = $balanceAfterWin;
                $user->save();
            }

            return response()->json([
                'reels' => $reels,
                'is_win' => $isWin,
                'win_amount' => $winAmount,
                'balance' => $user->balance,
            ]);
        });
    }

    private function getRandomSymbol()
    {
        $rand = rand(1, 100);
        $current = 0;
        foreach ($this->weights as $id => $weight) {
            $current += $weight;
            if ($rand <= $current) {
                return $this->symbols[$id];
            }
        }
        return $this->symbols[0]; // Fallback
    }
}
