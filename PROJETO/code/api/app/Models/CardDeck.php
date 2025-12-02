<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CardDeck extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'type', 'price', 'wins_required','min_points_required', 'image_filename', 'semFace'];

    public function owners()
    {
        return $this->belongsToMany(User::class, 'user_card_deck');
    }
}