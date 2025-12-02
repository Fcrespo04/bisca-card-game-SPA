<template>
  <div class="max-w-5xl mx-auto p-6 space-y-12">
    
    <!-- Cabeçalho Principal -->
    <div class="text-center space-y-2">
      <h1 class="text-4xl font-bold text-slate-800">🏆 Leaderboards</h1>
      <p class="text-slate-600 text-lg">Os melhores jogadores de Bisca</p>
    </div>

    <!-- Estatísticas Pessoais (Visível apenas se logado) -->
    <!-- Mantém a tua secção existente aqui... -->
    <Card v-if="authStore.isLoggedIn && personalStats" class="bg-gradient-to-r from-blue-50 to-indigo-50 border-blue-200">
        <!-- ... o teu código de stats pessoais ... -->
        <CardHeader>
            <CardTitle class="text-blue-900">As Minhas Estatísticas</CardTitle>
        </CardHeader>
        <CardContent>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
                <div class="bg-white p-4 rounded-lg shadow-sm">
                    <div class="text-2xl font-bold text-slate-800">{{ personalStats.matches_won }}</div>
                    <div class="text-xs text-slate-500 uppercase">Partidas Ganhas</div>
                </div>
                <div class="bg-white p-4 rounded-lg shadow-sm">
                    <div class="text-2xl font-bold text-slate-800">{{ personalStats.matches_played }}</div>
                    <div class="text-xs text-slate-500 uppercase">Partidas Jogadas</div>
                </div>
                <div class="bg-white p-4 rounded-lg shadow-sm">
                    <div class="text-2xl font-bold text-green-600">{{ personalStats.games_won }}</div>
                    <div class="text-xs text-slate-500 uppercase">Jogos Ganhos</div>
                </div>
                <div class="bg-white p-4 rounded-lg shadow-sm">
                    <div class="text-2xl font-bold text-yellow-600">{{ personalStats.games_played }}</div>
                    <div class="text-xs text-slate-500 uppercase">Jogos Totais</div>
                </div>
            </div>
        </CardContent>
    </Card>

    <!-- SECÇÃO DE RANKINGS (Lado a Lado) -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
      
      <!-- 1. TOP VENCEDORES (PARTIDAS) -->
      <Card class="h-full border-t-4 border-t-yellow-500 shadow-md">
        <CardHeader>
          <CardTitle class="flex items-center gap-2">
            <span>🥇</span> Top Vencedores (Partidas)
          </CardTitle>
          <CardDescription>Jogadores com mais partidas ganhas</CardDescription>
        </CardHeader>
        <CardContent class="p-0">
          <table class="w-full text-sm text-left">
            <thead class="bg-slate-50 text-xs uppercase text-slate-500">
              <tr>
                <th class="px-4 py-3 w-12 text-center">#</th>
                <th class="px-4 py-3">Jogador</th>
                <th class="px-4 py-3 text-right">Vitórias</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <!-- Mensagem se vazio -->
              <tr v-if="leaderboard.top_matches.length === 0">
                <td colspan="3" class="px-4 py-8 text-center text-gray-400 italic">
                  Ainda sem registos...
                </td>
              </tr>

              <!-- Linhas da Tabela -->
              <tr v-for="(player, index) in leaderboard.top_matches" :key="index" class="hover:bg-slate-50 transition-colors">
                <td class="px-4 py-3 text-center font-bold text-slate-600">
                    <!-- Medalhas para o Top 3 -->
                    <span v-if="index === 0" class="text-xl" title="1º Lugar">🥇</span>
                    <span v-else-if="index === 1" class="text-lg" title="2º Lugar">🥈</span>
                    <span v-else-if="index === 2" class="text-lg" title="3º Lugar">🥉</span>
                    <span v-else>{{ index + 1 }}</span>
                </td>
                <td class="px-4 py-3 font-medium text-slate-800">
                    {{ player.nickname }}
                </td>
                <td class="px-4 py-3 text-right font-bold text-yellow-700">
                    {{ player.total_wins }}
                </td>
              </tr>
            </tbody>
          </table>
        </CardContent>
      </Card>

      <!-- 2. TOP VENCEDORES (JOGOS) -->
      <Card class="h-full border-t-4 border-t-blue-500 shadow-md">
        <CardHeader>
          <CardTitle class="flex items-center gap-2">
            <span>🃏</span> Top Vencedores (Jogos)
          </CardTitle>
          <CardDescription>Jogadores com mais jogos parciais ganhos</CardDescription>
        </CardHeader>
        <CardContent class="p-0">
          <table class="w-full text-sm text-left">
            <thead class="bg-slate-50 text-xs uppercase text-slate-500">
              <tr>
                <th class="px-4 py-3 w-12 text-center">#</th>
                <th class="px-4 py-3">Jogador</th>
                <th class="px-4 py-3 text-right">Vitórias</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-if="leaderboard.top_games.length === 0">
                <td colspan="3" class="px-4 py-8 text-center text-gray-400 italic">
                  Ainda sem registos...
                </td>
              </tr>

              <tr v-for="(player, index) in leaderboard.top_games" :key="index" class="hover:bg-slate-50 transition-colors">
                <td class="px-4 py-3 text-center font-bold text-slate-600">
                    <span v-if="index === 0" class="text-xl">🥇</span>
                    <span v-else-if="index === 1" class="text-lg">🥈</span>
                    <span v-else-if="index === 2" class="text-lg">🥉</span>
                    <span v-else>{{ index + 1 }}</span>
                </td>
                <td class="px-4 py-3 font-medium text-slate-800">
                    {{ player.nickname }}
                </td>
                <td class="px-4 py-3 text-right font-bold text-blue-700">
                    {{ player.total_wins }}
                </td>
              </tr>
            </tbody>
          </table>
        </CardContent>
      </Card>

    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useAPIStore } from '@/stores/api'
import { useAuthStore } from '@/stores/auth'
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/card'

const apiStore = useAPIStore()
const authStore = useAuthStore()

// Inicializa com arrays vazios para não dar erro no v-for
const leaderboard = ref({ top_matches: [], top_games: [] })
const personalStats = ref(null)

onMounted(async () => {
  // 1. Carregar Leaderboard Global
  try {
    const res = await apiStore.getGlobalLeaderboard()
    // O Laravel retorna { top_matches: [...], top_games: [...] }
    if (res.data) {
        leaderboard.value = res.data
    }
  } catch (e) {
    console.error("Erro Leaderboard:", e)
  }

  // 2. Carregar Stats Pessoais (Só se logado)
  if (authStore.isLoggedIn) {
    try {
      const res = await apiStore.getPersonalStats()
      personalStats.value = res.data
    } catch (e) {
      console.error("Erro Stats Pessoais:", e)
    }
  }
})
</script>