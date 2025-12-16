<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\CardDeck;
use App\Models\User;

class StatisticsController extends Controller
{
    // --- 1. ESTATÍSTICAS PÚBLICAS ---
    public function publicStats()
    {
        $totalPlayers = DB::table('users')->where('type', 'P')->count();
        $totalGames = DB::table('games')->where('status', 'Ended')->count();
        $totalMatches = DB::table('matches')->where('status', 'Ended')->count();
        $grandTotalGames = $totalGames + $totalMatches;

        $topWinners = DB::select("
            SELECT u.nickname, u.photo_avatar_filename,
            ((SELECT COUNT(*) FROM games WHERE winner_user_id = u.id AND status = 'Ended') + 
             (SELECT COUNT(*) FROM matches WHERE winner_user_id = u.id AND status = 'Ended')) as total_wins
            FROM users u WHERE u.type = 'P'
            ORDER BY total_wins DESC LIMIT 3
        ");

        return response()->json([
            'total_players' => $totalPlayers,
            'total_games_played' => $grandTotalGames,
            'top_winners' => $topWinners
        ]);
    }

    // --- 2. ESTATÍSTICAS DE ADMIN ---
    public function adminStats()
    {
        $isSqlite = DB::connection()->getDriverName() === 'sqlite';

        // A. Vendas
        $monthExpression = $isSqlite ? "strftime('%Y-%m', purchase_datetime)" : "DATE_FORMAT(purchase_datetime, '%Y-%m')";
        $purchases = DB::table('coin_purchases')
            ->select(DB::raw("$monthExpression as month"), DB::raw('SUM(euros) as total'))
            ->groupByRaw($monthExpression)
            ->orderBy('month')
            ->get();

        // B. Economia
        $earned = DB::table('coin_transactions')->where('coins', '>', 0)->sum('coins');
        $spent = DB::table('coin_transactions')->where('coins', '<', 0)->sum('coins');

        // C. Atividade (IMPORTANTE: Agrupar por Data E TIPO)
        // Isto permite ao Vue separar a linha de "Total" das linhas "3 vs 9"
        $dayExpression = $isSqlite ? "strftime('%Y-%m-%d', began_at)" : "DATE(began_at)";
        
        $gamesPerDay = DB::table('games')
            ->select(DB::raw("$dayExpression as date"), 'type', DB::raw('COUNT(*) as count'))
            ->whereNotNull('began_at')
            ->groupByRaw("$dayExpression, type") // <--- O segredo está aqui
            ->orderBy('date')
            ->get();

        // D. Outros
        $paymentTypes = DB::table('coin_purchases')
            ->select('payment_type', DB::raw('count(*) as count'))
            ->groupBy('payment_type')
            ->get();

        $avgGameTime = DB::table('games')->where('status', 'Ended')->avg('total_time');

        return response()->json([
            'purchases_by_month' => $purchases,
            'coins' => ['earned' => (int) $earned, 'spent' => (int) abs($spent)],
            'games_per_day' => $gamesPerDay,
            'average_game_time' => $avgGameTime ? round($avgGameTime, 1) : 0,
            'payment_types' => $paymentTypes
        ]);
    }

    // --- 3. ESTATÍSTICAS DO JOGADOR (Corrigido contagem de jogos) ---
    public function playerStats(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $userId = $user->id;

        // A. Totais Gerais
        // Soma jogos como Player 1 + Player 2
        $gamesAsP1 = DB::table('games')->where('player1_user_id', $userId)->where('status', 'Ended')->count();
        $gamesAsP2 = DB::table('games')->where('player2_user_id', $userId)->where('status', 'Ended')->count();
        $totalGames = $gamesAsP1 + $gamesAsP2;

        $totalWins = DB::table('games')
            ->where('winner_user_id', $userId)
            ->where('status', 'Ended')
            ->count();
            
        $totalLosses = $totalGames - $totalWins;
        if ($totalLosses < 0) $totalLosses = 0;

        // B. Decks
        // CORREÇÃO: Usar a relação Eloquent em vez de DB::table para evitar erros de nome de tabela
        $decks = CardDeck::whereIn('type', ['WINS', 'POINTS'])->get();
        
        $totalMatchWins = DB::table('matches')
            ->where('winner_user_id', $userId)
            ->where('status', 'Ended')
            ->count();
            
        // AQUI ESTAVA O ERRO: Mudámos para usar a relação do Model User
        $ownedDeckIds = $user->cardDecks()->pluck('card_decks.id')->toArray();

        $decksData = $decks->map(function ($deck) use ($userId, $totalMatchWins, $ownedDeckIds) {
            $isOwned = in_array($deck->id, $ownedDeckIds);
            $progress = 0;

            if ($deck->type === 'WINS') {
                $progress = $totalMatchWins;
            } elseif ($deck->type === 'POINTS') {
                $minPoints = $deck->min_points_required ?? 120;
                $progress = DB::table('games')
                    ->where('status', 'Ended')
                    ->where(function($q) use ($userId, $minPoints) {
                        $q->where(function($sub) use ($userId, $minPoints) {
                            $sub->where('player1_user_id', $userId)->where('player1_points', '>=', $minPoints);
                        })->orWhere(function($sub) use ($userId, $minPoints) {
                            $sub->where('player2_user_id', $userId)->where('player2_points', '>=', $minPoints);
                        });
                    })->count();
            }

            return [
                'id' => $deck->id,
                'name' => $deck->name,
                // Fallback para image se image_filename for null
                'image' => $deck->image_filename ?? $deck->image,
                'type' => $deck->type,
                'wins_required' => $deck->wins_required,
                'user_progress' => $progress,
                'is_owned' => $isOwned,
                'can_claim' => ($progress >= $deck->wins_required) && !$isOwned
            ];
        });

        // C. Histórico
        $history = DB::table('games')
            ->join('users as p1', 'games.player1_user_id', '=', 'p1.id')
            ->join('users as p2', 'games.player2_user_id', '=', 'p2.id')
            ->select(
                'games.began_at', 
                'games.winner_user_id', 
                'games.player1_points', 
                'games.player2_points', 
                'games.player1_user_id', 
                'p1.nickname as p1_nick', 
                'p2.nickname as p2_nick'
            )
            ->where('games.status', 'Ended') // Garante que só busca jogos terminados
            ->where(function($q) use ($userId) {
                $q->where('games.player1_user_id', $userId)
                  ->orWhere('games.player2_user_id', $userId);
            })
            ->orderBy('games.began_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($game) use ($userId) {
                $isP1 = $game->player1_user_id == $userId;
                return [
                    'date' => $game->began_at,
                    'opponent' => $isP1 ? $game->p2_nick : $game->p1_nick,
                    'my_points' => $isP1 ? $game->player1_points : $game->player2_points,
                    'opponent_points' => $isP1 ? $game->player2_points : $game->player1_points,
                    'result' => $game->winner_user_id == $userId ? 'Win' : 'Loss'
                ];
            });

        return response()->json([
            'totalGames' => $totalGames,
            'totalWins' => $totalWins,
            'totalLosses' => $totalLosses,
            'decks' => $decksData,
            'history' => $history
        ]);
    }
}