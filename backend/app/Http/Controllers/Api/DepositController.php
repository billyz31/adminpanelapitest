<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class DepositController extends Controller
{
    /**
     * Handle the deposit request.
     */
    public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:255',
        ]);

        try {
            return DB::transaction(function () use ($request) {
                /** @var \App\Models\User $user */
                $user = Auth::user();

                if (!$user) {
                    throw new \Exception('User not authenticated');
                }

                // Lock the user row for update to prevent race conditions
                // Note: In some databases like MySQL, this requires a transaction to be active
                $user = DB::table('users')->where('id', $user->id)->lockForUpdate()->first();
                
                // Re-fetch user model instance or just use the data if we only need balance
                // To keep using Eloquent features comfortably, we can reload the model
                /** @var \App\Models\User $userModel */
                $userModel = \App\Models\User::find($user->id); 

                $amount = $request->amount;
                $balanceBefore = $userModel->balance;
                $balanceAfter = $balanceBefore + $amount;

                // Update user balance
                $userModel->update([
                    'balance' => $balanceAfter,
                ]);

                // Create transaction record
                $transaction = Transaction::create([
                    'user_id' => $userModel->id,
                    'type' => 'deposit',
                    'amount' => $amount,
                    'balance_before' => $balanceBefore,
                    'balance_after' => $balanceAfter,
                    'status' => 'completed',
                    'description' => $request->description ?? 'User deposit',
                ]);

                return response()->json([
                    'message' => 'Deposit successful',
                    'balance' => $balanceAfter,
                    'transaction' => $transaction,
                ], 200);
            });
        } catch (\Exception $e) {
            Log::error('Deposit failed: ' . $e->getMessage());
            return response()->json([
                'message' => 'Deposit failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
