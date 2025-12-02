<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCoinPurchaseRequest;
use App\Http\Resources\TransactionResource;
use App\Models\CoinTransaction;
use App\Models\CoinPurchase;
use App\Models\CoinTransactionType; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http; 
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    // G2: List Transactions
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->type === 'A') {
            // Admin sees ALL transactions
            $query = CoinTransaction::with(['transactionType', 'purchase', 'user'])->orderByDesc('transaction_datetime');
        } else {
            // Player sees only THEIR transactions
            $query = $user->transactions()->with(['transactionType', 'purchase'])->orderByDesc('transaction_datetime');
        }

        return TransactionResource::collection($query->paginate(20));
    }

    // G2: Buy Coins
    public function store(StoreCoinPurchaseRequest $request)
    {
        $validated = $request->validated();
        $user = $request->user();

        if ($user->type === 'A') {
            return response()->json(['message' => 'Administrators cannot participate in the economy.'], 403);
        }
        
        // Call External Payment Gateway
        $response = Http::post('https://dad-payments-api.vercel.app/api/debit', [
            'type' => $validated['payment_type'],
            'reference' => $validated['payment_reference'],
            'value' => (float) $validated['euros'],
        ]);

        // Handle Errors from Gateway
        if (!$response->successful()) {
            return response()->json([
                'message' => 'Payment failed',
                'error' => $response->json()['message'] ?? 'Unknown error from payment gateway'
            ], 422);
        }

        // Create Internal Transaction 
        return DB::transaction(function () use ($validated, $user) {
            // Calculate Coins (1 Euro = 10 Coins)
            $coinsAmount = $validated['euros'] * 10;
            
            // Get 'Coin purchase' type (ID 2 in seeders)
            $typeId = CoinTransactionType::where('name', 'Coin purchase')->first()->id ?? 2;

            // A. Create Transaction Record
            $transaction = CoinTransaction::create([
                'user_id' => $user->id,
                'coin_transaction_type_id' => $typeId,
                'transaction_datetime' => now(),
                'coins' => $coinsAmount,
            ]);

            // B. Create Purchase Record
            CoinPurchase::create([
                'user_id' => $user->id,
                'coin_transaction_id' => $transaction->id,
                'euros' => $validated['euros'],
                'payment_type' => $validated['payment_type'],
                'payment_reference' => $validated['payment_reference'],
                'purchase_datetime' => now(),
            ]);

            // C. Update User Balance
            $user->increment('coins_balance', $coinsAmount);

            return new TransactionResource($transaction);
        });
    }
}