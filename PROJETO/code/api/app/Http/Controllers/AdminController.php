<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB; // Necessário para estatísticas

class AdminController extends Controller
{

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