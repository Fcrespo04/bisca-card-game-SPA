<?php

namespace App\Http\Controllers;

use App\Models\CardDeck;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CardDeckController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $totalWins = $user->matchesWon()->count(); // For 'WINS' type (Gold/Platinum)

        if ($user->type === 'A') {
            return response()->json([]); // Return empty list
        }
        
        // Get all games won by the user
        $wonGames = $user->gamesWon; 

        $decks = CardDeck::all()->map(function ($deck) use ($user, $totalWins, $wonGames) {
            $isOwned = $user->cardDecks->contains($deck->id);
            
            $canClaim = false;
            $progress = 0;

            // Logic for standard 'WINS' decks (Gold/Platinum)
            if ($deck->type === 'WINS' && !$isOwned) {
                $canClaim = $totalWins >= $deck->wins_required;
                $progress = $totalWins;
            }
            // Logic for 'POINTS' decks (Master)
            elseif ($deck->type === 'POINTS' && !$isOwned) {
                // Count how many games meet the point requirement
                $highScoreCount = $wonGames->filter(function ($game) use ($user, $deck) {
                    $myPoints = $game->player1_user_id == $user->id 
                        ? $game->player1_points 
                        : $game->player2_points;
                    return $myPoints >= $deck->min_points_required;
                })->count();

                $canClaim = $highScoreCount >= $deck->wins_required; // e.g., 3 games
                $progress = $highScoreCount;
            }

            return [
                'id' => $deck->id,
                'name' => $deck->name,
                'slug' => $deck->slug,
                'type' => $deck->type,
                'price' => $deck->price,
                'wins_required' => $deck->wins_required,
                'min_points_required' => $deck->min_points_required,
                'image' => $deck->image_filename,
                'semFace' => $deck->semFace,
                'is_owned' => $isOwned,
                'is_active' => $user->current_card_deck_id === $deck->id,
                'user_progress' => $progress,
                'can_claim' => $canClaim
            ];
        });

        return response()->json($decks);
    }

    public function purchase(Request $request, CardDeck $deck)
    {
        $user = $request->user();

        if ($user->type === 'A') {
            return response()->json(['message' => 'Administrators cannot buy decks.'], 403);
        }

        if ($user->cardDecks->contains($deck->id)) {
            return response()->json(['message' => 'You already own this deck.'], 400);
        }

        if ($deck->type === 'COINS') {
            if ($user->coins_balance < $deck->price) {
                return response()->json(['message' => 'Insufficient coins.'], 402);
            }
            
            DB::transaction(function () use ($user, $deck) {
                $user->decrement('coins_balance', $deck->price);
                \App\Models\CoinTransaction::create([
                    'user_id' => $user->id,
                    'coin_transaction_type_id' => 7, 
                    'transaction_datetime' => now(),
                    'coins' => -1 * abs($deck->price),
                    'custom' => ['deck_name' => $deck->name]
                ]);
                $user->cardDecks()->attach($deck->id);
            });
        } 
        elseif ($deck->type === 'WINS') {
            if ($user->matchesWon()->count() < $deck->wins_required) {
                return response()->json(['message' => 'Not enough wins yet.'], 403);
            }
            $user->cardDecks()->attach($deck->id);
        }
        elseif ($deck->type === 'POINTS') {
            $highScoreCount = $user->gamesWon->filter(function ($game) use ($user, $deck) {
                $myPoints = $game->player1_user_id == $user->id 
                    ? $game->player1_points 
                    : $game->player2_points;
                return $myPoints >= $deck->min_points_required;
            })->count();

            if ($highScoreCount < $deck->wins_required) {
                return response()->json(['message' => "You need {$deck->wins_required} legendary games to claim this!"], 403);
            }
            $user->cardDecks()->attach($deck->id);
        }

        return response()->json(['message' => 'Deck acquired!', 'balance' => $user->coins_balance]);
    }

    public function equip(Request $request, CardDeck $deck)
    {
        $user = $request->user();

        if (!$user->cardDecks->contains($deck->id)) {
            return response()->json(['message' => 'You do not own this deck.'], 403);
        }

        $user->current_card_deck_id = $deck->id;
        $user->save();

        return response()->json(['message' => 'Deck equipped!']);
    }
}