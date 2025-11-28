<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GameMatch extends Model
{
    use HasFactory;

    // Map this model to the 'matches' table
    protected $table = 'matches';

    protected $fillable = [
        'type',             // '3' or '9'
        'status',           // 'PE', 'PL', 'E', 'I'
        'player1_user_id',
        'player2_user_id',
        'winner_user_id',
        'loser_user_id',
        'stake',            // Coins wagered
        'player1_marks',
        'player2_marks',
        'player1_points',   // Cumulative points
        'player2_points',   // Cumulative points
        'began_at',
        'ended_at',
        'total_time',
        'custom'
    ];

    protected $casts = [
        'began_at' => 'datetime',
        'ended_at' => 'datetime',
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

    // Relationship to the Games inside this match
    public function games(): HasMany
    {
        return $this->hasMany(Game::class, 'match_id');
    }

    // Relationship to financial transactions involving this match
    public function transactions(): HasMany
    {
        return $this->hasMany(CoinTransaction::class, 'match_id');
    }
}