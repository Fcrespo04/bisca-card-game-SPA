<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\CoinTransaction; 
use App\Models\CoinTransactionType; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (! Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $user = Auth::user();
        
        // G1: Check if user is blocked/soft-deleted (optional safety check)
        if ($user->blocked) {
            Auth::guard('web')->logout();
            return response()->json(['message' => 'Your account is blocked.'], 403);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user, // Useful to return user info immediately
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'message' => 'Logged out successfully',
        ]);
    }

    /**
     * G1: Public Registration with Welcome Bonus
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:3|confirmed', // 'confirmed' expects password_confirmation field
            'nickname' => 'required|string|max:20|unique:users', // G1: Unique Nickname
            // 'photo_avatar_filename' => 'nullable|image' // If you handle file upload here
        ]);

        // Use a transaction to ensure User and Bonus are created together
        return DB::transaction(function () use ($validated) {
            
            // 1. Create the User
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'nickname' => $validated['nickname'],
                'type' => 'P', // Default to Player
                'blocked' => false,
                'coins_balance' => 10, // G1: Welcome Bonus
            ]);

            // 2. Log the Welcome Bonus Transaction
            // Assuming you have seeded a 'Bonus' type in coin_transaction_types
            $bonusType = CoinTransactionType::where('name', 'Bonus')->first();
            
            if ($bonusType) {
                CoinTransaction::create([
                    'user_id' => $user->id,
                    'coin_transaction_type_id' => $bonusType->id,
                    'transaction_datetime' => now(),
                    'coins' => 10,
                    'custom' => json_encode(['description' => 'Welcome Bonus']),
                ]);
            }

            // 3. Auto-login (Generate Token)
            $token = $user->createToken('auth-token')->plainTextToken;

            return response()->json([
                'token' => $token,
                'user' => $user,
                'message' => 'User registered successfully with 10 bonus coins!'
            ], 201);
        });
    }
}