<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB; // Necessário para estatísticas
use App\Models\CoinPurchase;
use App\Models\CoinTransaction;
use App\Models\Game;

class AdminController extends Controller
{

    public function getUsersListAdmin(Request $request)
    {
        // 1. Definição da Query Base e Colunas
        $query = User::select(
            'id',
            'name',
            'email',
            'nickname',
            'blocked',
            'type',
            'photo_avatar_filename',
            'coins_balance',
            'created_at',
            'deleted_at'
        );

        // Obter Parâmetros
        $page = $request->query('page', 1);
        $filter = $request->query('filter', 'all'); // ⬅️ NOVO: Captura o filtro
        $search = $request->get('search');

        if ($search) {
        $query->where(function($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('nickname', 'like', "%{$search}%");
        });
    }

        // 2. Lógica de Filtragem (ADICIONADA)
        switch ($filter) {
            case 'active':
                // Ativos: Não bloqueados E Não soft-deleted
                $query->where('blocked', false)
                    ->whereNull('deleted_at');
                break;

            case 'blocked':
                // Bloqueados: Bloqueados (blocked=true) E Não soft-deleted
                $query->where('blocked', true)
                    ->whereNull('deleted_at');
                break;

            case 'deleted':
                // Desativados (Removidos): Apenas soft-deleted
                $query->onlyTrashed();
                break;

            case 'all':
            default:
                // Todos: Inclui ativos, bloqueados, e soft-deleted
                $query->withTrashed();
                break;
        }

        // 3. Executar Paginação (10 em 10)
        $Users = $query->orderBy('id', 'asc')->paginate(10, ['*'], 'page', $page);

        // 4. Devolver o Objeto de Paginação Completo
        return response()->json($Users);
    }

    public function toggleBlock(Request $request, User $user)
    {
        if ($request->user()->type !== 'A') {
            return response()->json(['error' => 'Acesso negado.'], 403);
        }

        $user->blocked = !$user->blocked;
        $user->save();

        return response()->json(['blocked' => $user->blocked]);
    }

    /**
     * Remove um utilizador (Soft Delete se tiver atividade, Hard Delete caso contrário).
     */
    public function removeAccount(Request $request, User $user)
    {
        // 1. VERIFICAÇÃO DE ADMIN
        if ($request->user()->type !== 'A') {
            return response()->json(['error' => 'Acesso negado.'], 403);
        }

        // 2. IMPEDIR AUTO-REMOÇÃO
        if ($request->user()->id === $user->id) {
            return response()->json(['error' => 'Não pode desativar a sua própria conta.'], 403);
        }

        // 💡 SOLUÇÃO FINAL: Soft Delete SEMPRE
        // O método delete() preenche a coluna deleted_at se o modelo usa o SoftDeletes.
        $user->delete();

        $message = 'Conta desativada com sucesso (Soft Delete). Os dados de histórico foram preservados.';

        return response()->json(['message' => $message]);
    }


    public function storeUserByAdmin(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'nickname' => 'required|string|unique:users,nickname',
            'password' => 'required|min:3',
            'type' => 'required|in:A,P', // A = Admin, P = Player
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'nickname' => $validated['nickname'],
            'password' => Hash::make($validated['password']),
            'type' => $validated['type'],
            'coins_balance' => 0, // Inicia com 0 moedas
        ]);

        return response()->json($user, 201);
    }

    public function getDashboardStats(Request $request)
{
    // Verificação de Segurança (G5)
    if ($request->user()->type !== 'A') {
        return response()->json(['message' => 'Não autorizado'], 403);
    }

    try {
        // Estatísticas Gerais (G6)
        $summary = [
        'total_users' => User::count(),
        'active_players' => User::where('type', 'P')->whereNull('deleted_at')->count(),
        'coins_in_circulation' => (int) User::where('type', 'P')->sum('coins_balance'),
        'total_revenue_euros' => (float) CoinPurchase::sum('euros'),

        // LÓGICA G4: Um jogo é multiplayer se houver um oponente (player 2)
        // Substitui 'player2_id' pelo nome real da tua coluna (ex: opponent_id)
        'total_multiplayer_games' => Game::whereNotNull('player2_id')->count(),
    ];

        // Lista de Transações Paginada (G5)
        // Seleção seletiva de colunas para não expor dados sensíveis e ganhar velocidade
        $transactions = CoinTransaction::with([
                'user:id,name,nickname', 
                'transactionType:id,name'
            ])
            ->orderByDesc('transaction_datetime')
            ->paginate(10);

        return response()->json([
            'summary' => $summary,
            'transactions' => $transactions
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Erro interno ao processar estatísticas',
            'error' => $e->getMessage()
        ], 500);
    }
}

}