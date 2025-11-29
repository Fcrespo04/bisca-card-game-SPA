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
        // Count matches won (Winner ID = User ID)
        $wins = $user->matchesWon()->count(); 

        // looks at all matches the user won, and find the max points they scored
        $bestScore = $user->matchesWon->map(function ($match) use ($user) {
            // If user was player 1, get player1_points, else player2_points
            return $match->player1_user_id === $user->id 
                ? $match->player1_points 
                : $match->player2_points;
        })->max() ?? 0;

        $decks = CardDeck::all()->map(function ($deck) use ($user, $wins, $bestScore) {
            $isOwned = $user->cardDecks->contains($deck->id);
            
            $canClaim = false;
            if ($deck->type === 'WINS' && !$isOwned) {
                $canClaim = $wins >= $deck->wins_required;
            }
            elseif ($deck->type === 'POINTS' && !$isOwned) {
                $canClaim = $bestScore >= $deck->min_points_required;
                $progress = $bestScore; 
            }

            return [
                'id' => $deck->id,
                'name' => $deck->name,
                'slug' => $deck->slug,
                'type' => $deck->type,
                'price' => $deck->price,
                'wins_required' => $deck->wins_required,
                'image' => $deck->image_filename,
                'is_owned' => $isOwned,
                'is_active' => $user->current_card_deck_id === $deck->id,
                'user_progress' => $wins, 
                'can_claim' => $canClaim,
                'min_points_required' => $deck->min_points_required,
            ];
        });

        return response()->json($decks);
    }

    public function purchase(Request $request, CardDeck $deck)
    {
        $user = $request->user();

        if ($user->cardDecks->contains($deck->id)) {
            return response()->json(['message' => 'You already own this deck.'], 400);
        }

        if ($deck->type === 'COINS') {
            if ($user->coins_balance < $deck->price) {
                return response()->json(['message' => 'Insufficient coins.'], 402);
            }
            
            DB::transaction(function () use ($user, $deck) {
                $user->decrement('coins_balance', $deck->price);
                $user->cardDecks()->attach($deck->id);
            });
        } 
        elseif ($deck->type === 'WINS') {
            $wins = $user->matchesWon()->count();
            if ($wins < $deck->wins_required) {
                return response()->json(['message' => 'Not enough wins yet.'], 403);
            }
            $user->cardDecks()->attach($deck->id);
        }
        elseif ($deck->type === 'POINTS') {
            $bestScore = $user->matchesWon->map(function ($match) use ($user) {
                return $match->player1_user_id === $user->id 
                    ? $match->player1_points 
                    : $match->player2_points;
            })->max() ?? 0;

            if ($bestScore < $deck->min_points_required) {
                return response()->json(['message' => 'You strictly need a legendary win (100+ pts) to claim this!'], 403);
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