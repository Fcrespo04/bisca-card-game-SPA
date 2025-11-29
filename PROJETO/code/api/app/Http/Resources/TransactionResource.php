<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'datetime' => $this->transaction_datetime->format('Y-m-d H:i:s'),
            'type' => $this->transactionType->name, // "Coin purchase", "Bonus"
            'coins' => $this->coins,
            'new_balance' => null, // compute running balance if needed
            // Only include purchase details if they exist
            'purchase' => $this->purchase ? [
                'euros' => $this->purchase->euros,
                'payment_type' => $this->purchase->payment_type,
                'payment_reference' => $this->purchase->payment_reference,
            ] : null,
        ];
    }
}