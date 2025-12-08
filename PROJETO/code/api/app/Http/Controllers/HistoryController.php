<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\GameMatch;
use App\Models\User;
use App\Http\Resources\GameResource; // Teremos de criar/atualizar este
use App\Http\Resources\UserResource;
use App\Http\Resources\MatchResource; // Teremos de criar este
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HistoryController extends Controller
{
    // --- HISTÓRICO ---

    public function index(Request $request)
{
    $user = $request->user();

        // 1. Query Base
        $query = GameMatch::with(['player1', 'player2', 'winner', 'games'])
            ->whereNotNull('player2_user_id') // Apenas Multiplayer
            ->orderByDesc('ended_at');

        // 2. Lógica de Acesso
        if ($user->type !== 'A') {
            // Jogador Normal: Vê apenas os seus
            $query->where(function ($q) use ($user) {
                $q->where('player1_user_id', $user->id)
                  ->orWhere('player2_user_id', $user->id);
            });
        } else {
            // --- NOVO: Lógica de Pesquisa para Admin ---
            if ($request->has('search') && $request->search) {
                $term = $request->search;
                $query->where(function ($q) use ($term) {
                    // Pesquisa por ID exato ou Nickname parcial
                    if (is_numeric($term)) {
                        $q->where('player1_user_id', $term)
                          ->orWhere('player2_user_id', $term);
                    } else {
                        $q->whereHas('player1', fn($sub) => $sub->where('nickname', 'like', "%{$term}%"))
                          ->orWhereHas('player2', fn($sub) => $sub->where('nickname', 'like', "%{$term}%"));
                    }
                });
            }
        }

        $matches = $query->paginate(10);

    // 2. Transformar a Coleção para a Resposta (Substitui o MatchResource)
    $transformedMatches = $matches->getCollection()->map(function ($match) use ($user) {
        
        if ($match->player1_user_id === $user->id) {
            // CENÁRIO 1: O Utilizador logado é o Player 1 (P1)
            // Ordem: P1 (Eu) / P2 (Adv)
            $userMarks = $match->player1_marks;
            $opponentMarks = $match->player2_marks;

        } elseif ($match->player2_user_id === $user->id) {
            // CENÁRIO 2: O Utilizador logado é o Player 2 (P2)
            // Ordem: P2 (Eu) / P1 (Adv) - Troca propositada para a perspetiva "EU"
            $userMarks = $match->player2_marks;
            $opponentMarks = $match->player1_marks;

        } else {
            // CENÁRIO 3: Administrador a ver (Neutro)
            // Ordem: P1 / P2
            $userMarks = $match->player1_marks;
            $opponentMarks = $match->player2_marks;
        }

        return [
            'id' => $match->id,
            'type' => $match->type,
            'status' => $match->status,
            'began_at' => $match->began_at?->format('Y-m-d H:i:s'),
            'ended_at' => $match->ended_at?->format('Y-m-d H:i:s'),
            'total_time' => (float) $match->total_time,
            
            'player1' => $match->player1 ? new UserResource($match->player1) : null,
            'player2' => $match->player2 ? new UserResource($match->player2) : null,
            'winner' => $match->winner ? new UserResource($match->winner) : null,

            'games' => $match->games,

            'player1_points' => (int) $match->player1_points, // <-- PONTOS DO JOGADOR 1
            'player2_points' => (int) $match->player2_points, // <-- PONTOS DO JOGADOR 2
            'stake' => (int) $match->stake, // Adicionar stake para os detalhes
            
            // Campos calculados
            'user_marks_won' => (int) $userMarks,
            'opponent_marks_won' => (int) $opponentMarks,

            
            
            // Resultado (WIN/LOSS/DRAW)
            'result' => $match->status === 'Ended' 
                        ? ($match->winner_user_id === $user->id ? 'WIN' : ($match->winner_user_id !== null ? 'LOSS' : 'DRAW'))
                        : null,
        ];
    });

    // 3. Devolver a resposta final (com dados de paginação)
    return response()->json([
        'data' => $transformedMatches,
        'current_page' => $matches->currentPage(),
        'last_page' => $matches->lastPage(),
        'total' => $matches->total(),
        'per_page' => $matches->perPage(),
        'prev_page_url' => $matches->previousPageUrl(),
        'next_page_url' => $matches->nextPageUrl(),
    ]);
}

    // --- LEADERBOARDS ---

public function leaderboardGlobal(Request $request)
    {
        // --- 1. SUBQUERY BASE: Pontos Totais de TODOS os Jogadores ---
        // Usamos esta subquery (S) para fazer o JOIN e obter o total_score em todos os rankings
        $scoresQuery = DB::table('games')
            ->select('users.id', DB::raw('SUM(CASE 
                        WHEN users.id = games.player1_user_id THEN games.player1_points
                        WHEN users.id = games.player2_user_id THEN games.player2_points
                        ELSE 0
                      END) as total_score'))
            ->join('users', function ($join) {
                // Junta o utilizador com o jogo onde ele era P1 OU P2
                $join->on('users.id', '=', 'games.player1_user_id')
                     ->orOn('users.id', '=', 'games.player2_user_id');
            })
            ->where('games.status', 'Ended')
            ->whereNotNull('games.match_id')
            ->groupBy('users.id');

        // --- 2. TOP 10 por Vitórias em Partidas (Matches) ---
        $topMatches = User::select('users.nickname', 'users.photo_avatar_filename', 
                                DB::raw('count(T_M.id) as total_wins'), 
                                DB::raw('T_S.total_score')) // NOVO CAMPO DE SCORE
            ->from('users')
            ->join('matches AS T_M', 'users.id', '=', 'T_M.winner_user_id')
            ->joinSub($scoresQuery, 'T_S', 'users.id', '=', 'T_S.id') // JOIN com os scores
            
            ->where('T_M.status', 'Ended')
            ->whereNotNull('T_M.player2_user_id')
            
            ->groupBy('users.id', 'users.nickname', 'users.photo_avatar_filename', 'T_S.total_score')
            ->orderByDesc('total_wins')
            ->orderByDesc('T_S.total_score') // 👑 NOVO DESEMPATE
            ->orderBy('earliest_win_time', 'asc')
            ->limit(10)
            ->get();

        // --- 3. Top 10 por Vitórias em Jogos Parciais (Games) ---
        $topGames = User::select('users.nickname', 'users.photo_avatar_filename', 
                                DB::raw('count(T_G.id) as total_wins'),
                                DB::raw('T_S2.total_score')) // NOVO CAMPO DE SCORE
            ->from('users')
            ->join('games AS T_G', 'users.id', '=', 'T_G.winner_user_id')
            ->joinSub($scoresQuery, 'T_S2', 'users.id', '=', 'T_S2.id') // JOIN com os scores
            
            ->where('T_G.status', 'Ended')
            ->whereNotNull('T_G.match_id')
            
            ->groupBy('users.id', 'users.nickname', 'users.photo_avatar_filename', 'T_S2.total_score')
            ->orderByDesc('total_wins')
            ->orderByDesc('T_S2.total_score') // 🃏 NOVO DESEMPATE
            ->orderBy('earliest_win_time', 'asc')
            ->limit(10)
            ->get();
            
        // --- 4. Top 10 por Pontos Totais (Scores) ---
        // Este já usa total_score e, portanto, já está ordenado corretamente.
        $topScorers = DB::table('games')
            ->select(DB::raw('users.id, users.nickname, users.photo_avatar_filename, 
                     SUM(CASE 
                        WHEN users.id = games.player1_user_id THEN games.player1_points
                        WHEN users.id = games.player2_user_id THEN games.player2_points
                        ELSE 0
                      END) as total_score'))
            ->join('users', function ($join) {
                $join->on('users.id', '=', 'games.player1_user_id')
                     ->orOn('users.id', '=', 'games.player2_user_id');
            })
            ->where('games.status', 'Ended')
            ->whereNotNull('games.match_id')
            ->groupBy('users.id', 'users.nickname', 'users.photo_avatar_filename')
            ->orderByDesc('total_score') // Já ordenado
            ->orderBy('earliest_win_time', 'asc')
            ->limit(10)
            ->get();

        return response()->json([
            'top_matches' => $topMatches,
            'top_games' => $topGames,
            'top_scores' => $topScorers,
        ]);
    }



public function statisticsPersonal(Request $request)
{
    $user = $request->user();
    $id = $user->id;

    // --- CÁLCULO DO SCORE TOTAL (Multiplayer) ---
    // Soma os pontos do utilizador, independentemente de ser P1 ou P2
    $totalScore = Game::where(function($q) use ($id) {
            $q->where('player1_user_id', $id)
              ->orWhere('player2_user_id', $id);
        })
        ->where('status', 'Ended')
        ->whereNotNull('match_id') // Apenas jogos multiplayer terminados
        ->sum(DB::raw("CASE 
                        WHEN player1_user_id = {$id} THEN player1_points 
                        WHEN player2_user_id = {$id} THEN player2_points 
                        ELSE 0 
                      END"));
    
    // --- 1. QUERY COMPLEXA (Capotes/Bandeiras) ---
    $conquestStats = Game::select(
            DB::raw('SUM(CASE 
                WHEN winner_user_id = ? AND player1_points >= 120 THEN 1 
                WHEN winner_user_id = ? AND player2_points >= 120 THEN 1 
                ELSE 0 END) as total_bandeiras'),

            DB::raw('SUM(CASE 
                WHEN winner_user_id = ? AND player1_points >= 91 AND player1_points < 120 THEN 1 
                WHEN winner_user_id = ? AND player2_points >= 91 AND player2_points < 120 THEN 1 
                ELSE 0 END) as total_capotes')
        )
        ->whereNotNull('match_id') // FILTRO ROBUSTO (Só jogos com Partida)
        ->setBindings([$id, $id, $id, $id], 'select')
        ->first();
    
    // --- 2. TOTAIS PESSOAIS (Multiplayer Apenas) ---
    $stats = [
        'matches_played' => GameMatch::where(function($q) use ($id) {
            $q->where('player1_user_id', $id)->orWhere('player2_user_id', $id);
        })->whereNotNull('player2_user_id')->count(),
                                
        'matches_won' => GameMatch::where('winner_user_id', $id)
            ->whereNotNull('player2_user_id')->count(),
        
        'games_played' => Game::where(function($q) use ($id) {
            $q->where('player1_user_id', $id)->orWhere('player2_user_id', $id);
        })->whereNotNull('match_id')->count(),
                                
        'games_won' => Game::where('winner_user_id', $id)
            ->whereNotNull('match_id')->count(),
        
        'capotes' => (int) ($conquestStats->total_capotes ?? 0), 
        'bandeiras' => (int) ($conquestStats->total_bandeiras ?? 0),
        
        'total_score' => (int) $totalScore // 🎯 NOVO: Pontos totais multiplayer
    ];

    return response()->json($stats);
}

}