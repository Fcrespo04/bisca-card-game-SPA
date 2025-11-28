<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CoinTransaction extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'transaction_datetime',
        'user_id',
        'coin_transaction_type_id',
        'match_id',
        'game_id',
        'coins',
        'custom'
    ];

    protected $casts = [
        'transaction_datetime' => 'datetime',
        'custom' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Link to the Type (Bonus, Purchase, etc)
    public function transactionType(): BelongsTo
    {
        return $this->belongsTo(CoinTransactionType::class, 'coin_transaction_type_id');
    }

    // Link to the Match (Using GameMatch model)
    public function gameMatch(): BelongsTo
    {
        return $this->belongsTo(GameMatch::class, 'match_id');
    }

    // Link to the Game
    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    // Link to the Purchase Details (if applicable)
    public function purchase(): HasOne
    {
        return $this->hasOne(CoinPurchase::class);
    }
}