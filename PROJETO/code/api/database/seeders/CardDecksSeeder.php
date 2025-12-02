<?php

namespace Database\Seeders;

use App\Models\CardDeck;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CardDecksSeeder extends Seeder
{
    public function run(): void
    {
        $decks = [
            [
                'name' => 'Standard',
                'slug' => 'default',
                'type' => 'FREE',
                'price' => 0,
                'wins_required' => 0,
                'image_filename' => 'Standard/general.jpg',
                'semFace' => 'Standard/semFace.jpg' 
            ],
            [
                'name' => 'Rainbow',
                'slug' => 'rainbow',
                'type' => 'COINS',
                'price' => 5,
                'wins_required' => 0,
                'image_filename' => 'Rainbow/general.jpg',
                'semFace' => 'Rainbow/semFace.jpg'
            ],
            [
                'name' => 'Carnival',
                'slug' => 'carnival',
                'type' => 'COINS',
                'price' => 10,
                'wins_required' => 0,
                'image_filename' => 'Carnival/general.jpg',
                'semFace' => 'Carnival/semFace.jpg'
            ],
            [
                'name' => 'Ancient',
                'slug' => 'ancient',
                'type' => 'COINS',
                'price' => 20,
                'wins_required' => 0,
                'image_filename' => 'Ancient/general.jpg',
                'semFace' => 'Ancient/semFace.jpg'
            ],
            [
                'name' => 'Gold',
                'slug' => 'gold',
                'type' => 'WINS',
                'price' => 0,
                'wins_required' => 5,
                'image_filename' => 'Gold/general.jpg',
                'semFace' => 'Gold/semFace.jpg'
            ],
            [
                'name' => 'Platinum',
                'slug' => 'platinum',
                'type' => 'WINS',
                'price' => 0,
                'wins_required' => 10,
                'image_filename' => 'Platinum/general.jpg',
                'semFace' => 'Platinum/semFace.jpg'
            ],
            [
                'name' => 'Master',
                'slug' => 'master',
                'type' => 'POINTS',
                'price' => 0,
                'wins_required' => 0,
                'min_points_required' => 100, 
                'image_filename' => 'Master/general.jpg',
                'semFace' => 'Master/semFace.jpg'
            ],
        ];

        foreach ($decks as $data) {
            CardDeck::firstOrCreate(['slug' => $data['slug']], $data);
        }

        // Give Default Deck to ALL existing users
        $defaultDeck = CardDeck::where('slug', 'default')->first();
        $users = User::all();
        foreach ($users as $user) {
            if (!$user->current_card_deck_id) {
                $user->current_card_deck_id = $defaultDeck->id;
                $user->save();
            }
            // Add to pivot table if not exists
            $user->cardDecks()->syncWithoutDetaching([$defaultDeck->id]);
        }
    }
}