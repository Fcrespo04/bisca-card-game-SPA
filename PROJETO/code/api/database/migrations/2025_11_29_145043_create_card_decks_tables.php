<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // The Card Decks (Packs) Table
        Schema::create('card_decks', function (Blueprint $table) {
            $table->id();
            $table->string('name'); 
            $table->string('slug')->unique(); 
            
            $table->enum('type', ['FREE', 'COINS', 'WINS', 'POINTS']); 
            
            $table->integer('price')->nullable(); // Coins needed
            $table->integer('wins_required')->nullable(); // Matches won needed
            $table->integer('min_points_required')->nullable(); // Minimum points needed
            $table->string('image_filename')->nullable(); // Pack Logo
            $table->string('semFace')->nullable();
            $table->timestamps();
        });

        // Add 'current_card_deck_id' to Users
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('current_card_deck_id')->nullable();
            $table->foreign('current_card_deck_id')->references('id')->on('card_decks');
        });

        // Pivot Table: Users <-> Card Decks (Ownership)
        Schema::create('user_card_deck', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('card_deck_id');
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('card_deck_id')->references('id')->on('card_decks')->onDelete('cascade');
            
            $table->unique(['user_id', 'card_deck_id']); // Prevent duplicate ownership
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_card_deck');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('current_card_deck_id');
        });
        Schema::dropIfExists('card_decks');
    }
};