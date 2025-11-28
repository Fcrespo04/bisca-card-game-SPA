<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Game extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',             // '3' (Bisca de 3) or '9' (Bisca de 9)
        'status',           // 'PE', 'PL', 'E', 'I'
        'player1_user_id',  // Renamed from player1_id
        'player2_user_id',  // Renamed from player2_id
        'winner_user_id',   // Renamed from winner_id
        'loser_user_id',    // New field
        'is_draw',          // New field
        'match_id',         // Link to the Match (set of games)
        'began_at',
        'ended_at',
        'total_time',
        'player1_points',   // New field (0-120)
        'player2_points',   // New field (0-120)
        'custom',
    ];

    protected $casts = [
        'began_at' => 'datetime',
        'ended_at' => 'datetime',
        'is_draw' => 'boolean',
        'custom' => 'array',
    ];

    // --- Relationships ---

    public function player1(): BelongsTo
    {
        return $this->belongsTo(User::class, 'player1_user_id');
    }

    public function player2(): BelongsTo
    {
        return $this->belongsTo(User::class, 'player2_user_id');
    }

    public function winner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'winner_user_id');
    }

    public function loser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'loser_user_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(CoinTransaction::class, 'game_id');
    }

    // Link to the Match (set of games)
    public function gameMatch(): BelongsTo
    {
        return $this->belongsTo(GameMatch::class, 'match_id');
    }

}