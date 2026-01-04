<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // 註冊
    public function register(Request $request)
    {
        \Illuminate\Support\Facades\Log::info('Register endpoint hit', $request->all());
        try {
            $rules = [
                'username' => 'required|string|unique:users',
                'password' => 'required|string|min:6|confirmed',
            ];

            $request->validate($rules);

            return DB::transaction(function () use ($request) {
                $user = User::create([
                    'username' => $request->username,
                    'name' => $request->username,
                    'password' => Hash::make($request->password),
                    'balance' => 0, // 初始餘額
                ]);

                $token = $user->createToken('auth_token')->plainTextToken;

                return response()->json([
                    'message' => '註冊成功',
                    'user' => $user,
                    'token' => $token,
                ], 201);
            });
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Register error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Register failed: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // 登入
    public function login(Request $request)
    {
        try {
            $request->validate([
                'username' => 'required|string',
                'password' => 'required|string',
            ]);

            $user = User::where('username', $request->username)->first();

            if (! $user || ! Hash::check($request->password, $user->password)) {
                throw ValidationException::withMessages([
                    'username' => ['帳號或密碼錯誤'],
                ]);
            }
            
            if (! $user->is_active) {
                return response()->json(['message' => '帳號已被停用'], 403);
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => '登入成功',
                'user' => $user,
                'token' => $token,
            ]);
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Login error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Login failed: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // 登出
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => '登出成功']);
    }

    // 獲取當前用戶資訊
    public function me(Request $request)
    {
        return response()->json($request->user());
    }
}
