<template>
  <div class="max-w-6xl mx-auto p-6 space-y-12">

    <div class="text-center space-y-2">
      <h1 class="text-4xl font-bold text-slate-800">🏆 Leaderboards</h1>
      <p class="text-slate-600 text-lg">Os melhores jogadores de Bisca</p>
    </div>

    <Card v-if="authStore.isLoggedIn && personalStats && !(authStore.currentUser?.type === 'A')"
      class="bg-gradient-to-r from-blue-50 to-indigo-50 border-blue-200 shadow-sm">
      <CardHeader>
        <CardTitle class="text-blue-900 text-center text-xl">As Minhas Estatísticas</CardTitle>
      </CardHeader>
      <CardContent>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-7 gap-4 text-center">

          <div class="bg-white p-4 rounded-xl shadow-sm border border-blue-100">
            <div class="text-2xl font-bold text-slate-800">{{ personalStats.matches_won }}</div>
            <div class="text-xs text-slate-500 uppercase tracking-wide mt-1">Partidas Ganhas</div>
          </div>
          <div class="bg-white p-4 rounded-xl shadow-sm border border-blue-100">
            <div class="text-2xl font-bold text-slate-800">{{ personalStats.matches_played }}</div>
            <div class="text-xs text-slate-500 uppercase tracking-wide mt-1">Partidas Jogadas</div>
          </div>

          <div class="bg-white p-4 rounded-xl shadow-sm border border-blue-100">
            <div class="text-2xl font-bold text-green-600">{{ personalStats.games_won }}</div>
            <div class="text-xs text-slate-500 uppercase tracking-wide mt-1">Jogos Ganhos</div>
          </div>
          <div class="bg-white p-4 rounded-xl shadow-sm border border-blue-100">
            <div class="text-2xl font-bold text-slate-600">{{ personalStats.games_played }}</div>
            <div class="text-xs text-slate-500 uppercase tracking-wide mt-1">Jogos Totais</div>
          </div>

          <div class="bg-white p-4 rounded-xl shadow-sm border border-purple-100">
            <div class="text-2xl font-bold text-purple-600 flex items-center justify-center gap-1">
              {{ personalStats.capotes || 0 }} <span>♦️</span>
            </div>
            <div class="text-xs text-slate-500 uppercase tracking-wide mt-1">Capotes</div>
          </div>
          <div class="bg-white p-4 rounded-xl shadow-sm border border-yellow-100">
            <div class="text-2xl font-bold text-yellow-600 flex items-center justify-center gap-1">
              {{ personalStats.bandeiras || 0 }} <span>🚩</span>
            </div>
            <div class="text-xs text-slate-500 uppercase tracking-wide mt-1">Bandeiras</div>
          </div>
          <div class="bg-white p-4 rounded-xl shadow-sm border border-yellow-100">
            <div class="text-2xl font-bold text-yellow-600 flex items-center justify-center gap-1">
              {{personalStats.total_score}}
            </div>
            <div class="text-xs text-slate-500 uppercase tracking-wide mt-1">Score Total</div>
          </div>

        </div>
      </CardContent>
    </Card>

    

    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">

      <Card class="h-full border-t-4 border-t-yellow-500 shadow-md">
        <CardHeader>
          <CardTitle class="flex items-center gap-2">
            <span>🥇</span> Top Vencedores (Partidas)
          </CardTitle>
          <CardDescription>Jogadores com mais partidas ganhas</CardDescription>
        </CardHeader>
        <div v-if="authStore.isLoggedIn && leaderboard.personal_match_rank"
          class="p-4 text-center bg-yellow-50 border-y border-yellow-200">
          <span class="text-xs font-semibold text-slate-600 uppercase block mb-1">A Sua Posição</span>
          <span class="text-2xl font-extrabold text-yellow-800">
            #{{ leaderboard.personal_match_rank.rank }} 
          </span>
        </div>
        
        <CardContent class="p-0">
          <table class="w-full text-sm text-left">
            <thead class="bg-slate-50 text-xs uppercase text-slate-500">
              <tr>
                <th class="px-4 py-3 w-12 text-center">#</th>
                <th class="px-4 py-3">Jogador</th>
                <th class="px-4 py-3 text-right">Vitórias</th>
                <th class="px-4 py-3 text-right">Score</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-if="leaderboard.top_matches.length === 0">
                <td colspan="3" class="px-4 py-8 text-center text-gray-400 italic">
                  Ainda sem registos...
                </td>
              </tr>

              <tr v-for="(player, index) in leaderboard.top_matches" :key="index"
                class="hover:bg-slate-50 transition-colors">
                <td class="px-4 py-3 text-center font-bold text-slate-600">
                  <span v-if="index === 0" class="text-xl" title="1º Lugar">🥇</span>
                  <span v-else-if="index === 1" class="text-lg" title="2º Lugar">🥈</span>
                  <span v-else-if="index === 2" class="text-lg" title="3º Lugar">🥉</span>
                  <span v-else>{{ index + 1 }}</span>
                </td>
                <td class="px-4 py-3 flex items-center gap-3">
                  <Avatar class="h-8 w-8 border border-slate-200">
                    <AvatarImage v-if="player.photo_avatar_filename"
                      :src="`${serverBaseURL}/storage/photos_avatars/${player.photo_avatar_filename}`" />
                    <AvatarFallback>{{ player.nickname?.charAt(0).toUpperCase() }}</AvatarFallback>
                  </Avatar>
                  <span class="font-medium text-slate-800">{{ player.nickname }}</span>
                </td>
                <td class="px-4 py-3 text-right font-bold text-yellow-700">
                  {{ player.total_wins }}
                </td>
                <td class="px-4 py-3 text-right font-medium text-slate-500">
                {{ player.total_score }} 
            </td>
              </tr>
            </tbody>
          </table>
        </CardContent>
      </Card>

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
                <th class="px-4 py-3 text-right">Score</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-if="leaderboard.top_games.length === 0">
                <td colspan="3" class="px-4 py-8 text-center text-gray-400 italic">
                  Ainda sem registos...
                </td>
              </tr>

              <tr v-for="(player, index) in leaderboard.top_games" :key="index"
                class="hover:bg-slate-50 transition-colors">
                <td class="px-4 py-3 text-center font-bold text-slate-600">
                  <span v-if="index === 0" class="text-xl">🥇</span>
                  <span v-else-if="index === 1" class="text-lg">🥈</span>
                  <span v-else-if="index === 2" class="text-lg">🥉</span>
                  <span v-else>{{ index + 1 }}</span>
                </td>
                <td class="px-4 py-3 flex items-center gap-3">
                  <Avatar class="h-8 w-8 border border-slate-200">
                    <AvatarImage v-if="player.photo_avatar_filename"
                      :src="`${serverBaseURL}/storage/photos_avatars/${player.photo_avatar_filename}`" />
                    <AvatarFallback>{{ player.nickname?.charAt(0).toUpperCase() }}</AvatarFallback>
                  </Avatar>
                  <span class="font-medium text-slate-800">{{ player.nickname }}</span>
                </td>
                <td class="px-4 py-3 text-right font-bold text-blue-700">
                  {{ player.total_wins }}
                </td>
                <td class="px-4 py-3 text-right font-medium text-slate-500">
                {{ player.total_score }} 
            </td>
              </tr>
            </tbody>
          </table>
        </CardContent>
      </Card>

      <Card class="h-full border-t-4 border-t-green-500 shadow-md">
        <CardHeader>
          <CardTitle class="flex items-center gap-2">
            <span>🎯</span> Top Pontuadores (Pontos Totais)
          </CardTitle>
          <CardDescription>Jogadores com a maior soma de pontos</CardDescription>
        </CardHeader>
        <CardContent class="p-0">
          <table class="w-full text-sm text-left">
            <thead class="bg-slate-50 text-xs uppercase text-slate-500">
              <tr>
                <th class="px-4 py-3 w-12 text-center">#</th>
                <th class="px-4 py-3">Jogador</th>
                <th class="px-4 py-3 text-right">Pontos</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-if="leaderboard.top_scores.length === 0">
                <td colspan="3" class="px-4 py-8 text-center text-gray-400 italic">
                  Ainda sem registos...
                </td>
              </tr>

              <tr v-for="(player, index) in leaderboard.top_scores" :key="index"
                class="hover:bg-slate-50 transition-colors">
                <td class="px-4 py-3 text-center font-bold text-slate-600">
                  <span v-if="index === 0" class="text-xl">🥇</span>
                  <span v-else-if="index === 1" class="text-lg">🥈</span>
                  <span v-else-if="index === 2" class="text-lg">🥉</span>
                  <span v-else>{{ index + 1 }}</span>
                </td>
                <td class="px-4 py-3 flex items-center gap-3">
                  <Avatar class="h-8 w-8 border border-slate-200">
                    <AvatarImage v-if="player.photo_avatar_filename"
                      :src="`${serverBaseURL}/storage/photos_avatars/${player.photo_avatar_filename}`" />
                    <AvatarFallback>{{ player.nickname?.charAt(0).toUpperCase() }}</AvatarFallback>
                  </Avatar>
                  <span class="font-medium text-slate-800">{{ player.nickname }}</span>
                </td>
                <td class="px-4 py-3 text-right font-bold text-green-700">
                  {{ player.total_score }}
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
import { ref, onMounted, inject } from 'vue'
import { useAPIStore } from '@/stores/api'
import { useAuthStore } from '@/stores/auth'
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/card'
import { Avatar, AvatarImage, AvatarFallback } from '@/components/ui/avatar'

const apiStore = useAPIStore()
const authStore = useAuthStore()
const serverBaseURL = inject('serverBaseURL')

// Inicializa com arrays vazios para não dar erro no v-for
const leaderboard = ref({ top_matches: [], top_games: [], top_scores: []})
const personalStats = ref(null)

onMounted(async () => {
  // 1. Carregar Leaderboard Global
  try {
    const res = await apiStore.getGlobalLeaderboard()
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