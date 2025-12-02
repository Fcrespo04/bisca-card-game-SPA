<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\GameMatch;
use App\Models\User;
use App\Http\Resources\GameResource; // Teremos de criar/atualizar este
use App\Http\Resources\MatchResource; // Teremos de criar este
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HistoryController extends Controller
{
    // --- HISTÓRICO ---

    public function index(Request $request)
    {
        $user = $request->user();

        // Admin vê tudo, Player vê só o seu
        if ($user->type === 'A') {
            $matches = GameMatch::with(['player1', 'player2', 'winner'])
                ->orderByDesc('ended_at');
        } else {
            $matches = GameMatch::where(function ($q) use ($user) {
                $q->where('player1_user_id', $user->id)
                  ->orWhere('player2_user_id', $user->id);
            })
            ->with(['player1', 'player2', 'winner'])
            ->orderByDesc('ended_at');
        }

        return response()->json($matches->paginate(10));
    }

    // --- LEADERBOARDS ---

    public function leaderboardGlobal()
    {
        // Top 10 por Vitórias em Partidas (Matches)
        $topMatches = User::select('nickname', 'photo_avatar_filename', DB::raw('count(*) as total_wins'))
            ->join('matches', 'users.id', '=', 'matches.winner_user_id')
            ->where('matches.status', 'E') // Apenas terminados
            ->groupBy('users.id', 'users.nickname', 'users.photo_avatar_filename')
            ->orderByDesc('total_wins')
            ->limit(10)
            ->get();

        // Top 10 por Vitórias em Jogos (Games)
        $topGames = User::select('nickname', 'photo_avatar_filename', DB::raw('count(*) as total_wins'))
            ->join('games', 'users.id', '=', 'games.winner_user_id')
            ->where('games.status', 'E')
            ->groupBy('users.id', 'users.nickname', 'users.photo_avatar_filename')
            ->orderByDesc('total_wins')
            ->limit(10)
            ->get();

        return response()->json([
            'top_matches' => $topMatches,
            'top_games' => $topGames
        ]);
    }

    public function statisticsPersonal(Request $request)
    {
        $user = $request->user();
        $id = $user->id;

        // Totais Pessoais
        $stats = [
            'matches_played' => GameMatch::where('player1_user_id', $id)->orWhere('player2_user_id', $id)->count(),
            'matches_won' => GameMatch::where('winner_user_id', $id)->count(),
            'games_played' => Game::where('player1_user_id', $id)->orWhere('player2_user_id', $id)->count(),
            'games_won' => Game::where('winner_user_id', $id)->count(),
            
            // Exemplo de como calcular Capotes/Bandeiras (assumindo que guardas isso no 'custom' ou lógica de pontos)
            // Se não guardares explicitamente, terias de inferir pelos pontos (120-0)
            // Para simplificar, vou deixar a 0 por agora, mas podes expandir.
            'capotes' => 0, 
            'bandeiras' => 0 
        ];

        return response()->json($stats);
    }
}
