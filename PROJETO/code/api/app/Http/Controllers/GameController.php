<?php

namespace App\Http\Controllers;

use App\Models\Game;
use Illuminate\Http\Request;
use App\Http\Requests\StoreGameRequest;
use App\Http\Resources\GameResource;

class GameController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Em vez de carregar TUDO (all), carregamos páginas de 10 jogos
        return GameResource::collection(Game::paginate(10));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreGameRequest $request)
    {   
        $user = $request->user();

        // --- NEW: Block Admins ---
        if ($user->type === 'A') {
            return response()->json(['message' => 'Administrators cannot play games.'], 403);
        }
        $game = Game::create($request->validated());
        return new GameResource($game);
    }

    /**
     * Display the specified resource.
     */
    public function show(Game $game)
    {
        return new GameResource($game);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreGameRequest $request, Game $game)
    {
        $game->update($request->validated());
        return new GameResource($game);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Game $game)
    {
        //
    }
}
