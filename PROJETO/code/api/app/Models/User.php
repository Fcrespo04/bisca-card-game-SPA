<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes; 

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes; 

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'nickname',              // <-- New Bisca field
        'type',                  // <-- Replaces 'role' ('A' for Admin, 'P' for Player)
        'blocked',               // <-- To block users
        'photo_avatar_filename', // <-- Replaces 'photo_url'
        'coins_balance',         // <-- Economy field
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'blocked' => 'boolean',
        ];
    }

    // --- Games Relationships ---

    public function gamesAsPlayer1(): HasMany
    {
        return $this->hasMany(Game::class, 'player1_user_id'); // Updated FK
    }

    public function gamesAsPlayer2(): HasMany
    {
        return $this->hasMany(Game::class, 'player2_user_id'); // Updated FK
    }

    public function gamesWon(): HasMany
    {
        return $this->hasMany(Game::class, 'winner_user_id'); // Updated FK
    }

    // --- Matches Relationships ---

    public function matchesAsPlayer1(): HasMany
    {
        return $this->hasMany(GameMatch::class, 'player1_user_id');
    }

    public function matchesAsPlayer2(): HasMany
    {
        return $this->hasMany(GameMatch::class, 'player2_user_id');
    }

    public function matchesWon(): HasMany
    {
        return $this->hasMany(GameMatch::class, 'winner_user_id');
    }

    // --- Coin Transactions and Purchases Relationships ---

    public function transactions(): HasMany
    {
        return $this->hasMany(CoinTransaction::class, 'user_id');
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(CoinPurchase::class, 'user_id');
    }

    // --- Card Decks Relationships ---
    
    public function cardDecks()
    {
        return $this->belongsToMany(CardDeck::class, 'user_card_deck');
    }

    public function currentDeck()
    {
        return $this->belongsTo(CardDeck::class, 'current_card_deck_id');
    }

    // Helper for "My Games"
    public function gamesQuery()
    {
        return Game::where('player1_user_id', $this->id)
            ->orWhere('player2_user_id', $this->id);
    }
}