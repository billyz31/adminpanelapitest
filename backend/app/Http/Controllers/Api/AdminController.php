<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard()
    {
        return response()->json([
            'total_users' => User::where('role', '!=', 'admin')->count(),
            'total_balance' => User::where('role', '!=', 'admin')->sum('balance'),
        ]);
    }

    public function users(Request $request)
    {
        $query = User::where('role', '!=', 'admin');

        // Search by username or email
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('username', 'ilike', "%{$search}%")
                  ->orWhere('email', 'ilike', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->where('is_active', $request->status === 'true' || $request->status === '1');
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(10);
            
        return response()->json($users);
    }

    public function updateUserBalance(Request $request, $id)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'type' => 'required|in:add,subtract'
        ]);

        $user = User::findOrFail($id);
        
        \Illuminate\Support\Facades\DB::transaction(function () use ($user, $request) {
            $balanceBefore = $user->balance;
            
            if ($request->type === 'add') {
                $user->balance += $request->amount;
                $transactionType = 'manual_add';
            } else {
                $user->balance -= $request->amount;
                $transactionType = 'manual_subtract';
            }
            
            $user->save();

            \App\Models\Transaction::create([
                'user_id' => $user->id,
                'type' => $transactionType,
                'amount' => $request->amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $user->balance,
                'reference_id' => 'ADMIN-' . uniqid(),
                'description' => 'Admin manual adjustment',
            ]);
        });

        return response()->json([
            'message' => 'User balance updated successfully',
            'balance' => $user->balance
        ]);
    }

    public function toggleUserStatus($id)
    {
        $user = User::findOrFail($id);
        $user->is_active = !$user->is_active;
        $user->save();

        return response()->json([
            'message' => 'User status updated successfully',
            'is_active' => $user->is_active
        ]);
    }

    public function gameRounds(Request $request)
    {
        $query = \App\Models\GameRound::with('user');

        if ($request->has('username') && !empty($request->username)) {
            $username = $request->username;
            $query->whereHas('user', function ($q) use ($username) {
                $q->where('username', 'ilike', "%{$username}%");
            });
        }

        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('created_at', [$request->start_date, $request->end_date]);
        }

        $rounds = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json($rounds);
    }

    public function transactions(Request $request)
    {
        $query = \App\Models\Transaction::with('user');

        if ($request->has('username') && !empty($request->username)) {
            $username = $request->username;
            $query->whereHas('user', function ($q) use ($username) {
                $q->where('username', 'ilike', "%{$username}%");
            });
        }

        if ($request->has('type') && !empty($request->type)) {
            $query->where('type', $request->type);
        }

        $transactions = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json($transactions);
    }
}
