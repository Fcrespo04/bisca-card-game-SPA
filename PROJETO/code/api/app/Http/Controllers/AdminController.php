<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB; // Necessário para estatísticas

class AdminController extends Controller
{

    public function getUsersListAdmin(Request $request) // Ou listAllUsers, use o nome que você definiu para a rota
    {
        // 1. Autorização (Assumindo que está num middleware ou check)
        if ($request->user()->type !== 'A') {
            return response()->json(['error' => 'Acesso negado.'], 403);
        }

        // 2. Obter Parâmetros
        $page = $request->get('page', 1);
        $filter = $request->get('filter', 'all'); // ⬅️ Captura o filtro do Frontend

        // 3. Definição da Query Base
        $query = User::select(
            'id',
            'name',
            'email',
            'nickname',
            'blocked',
            'photo_avatar_filename',
            'coins_balance',
            'created_at'
        );

        // 4. Lógica de Filtragem (ADICIONADO)
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
                // Todos: Inclui ativos, bloqueados, e soft-deleted (usa withTrashed)
                $query->withTrashed();
                break;
        }

        // 5. Ordenação e Paginação
        $Users = $query->orderBy('id', 'asc')->paginate(10, ['*'], 'page', $page);

        // 6. Devolver a resposta de paginação
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

    /**
     * Cria uma nova conta de Administrador.
     */
    public function createAdmin(Request $request)
    {
        if ($request->user()->type !== 'A') {
            return response()->json(['error' => 'Acesso negado.'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'nickname' => 'required|string|max:255|unique:users',
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'nickname' => $validated['nickname'],
            'password' => Hash::make($validated['password']),
            'type' => 'A',
            'coins_balance' => 0,
        ]);

        return response()->json(['message' => 'Novo Administrador criado com sucesso!']);
    }


}