<template>
  <div class="max-w-6xl mx-auto p-6 space-y-6">

    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
      <div>
        <h1 class="text-3xl font-bold text-slate-800">
          {{ isAdmin ? '🌍 Global History' : '📜 My History' }}
        </h1>
        <p class="text-slate-500 text-sm">
          {{ isAdmin ? 'Management and consultation of all games' : 'Click on a row to see technical details' }}
        </p>
      </div>

      <div v-if="isAdmin" class="flex items-center gap-2 w-full md:w-auto">
        <div class="relative w-full md:w-64">
          <input v-model="searchQuery" @keyup.enter="loadHistory(1)" type="text" placeholder="Search Player..."
            class="pl-3 pr-10 py-2 border border-slate-300 rounded-md text-sm w-full focus:outline-none focus:ring-2 focus:ring-blue-500" />
          <button @click="loadHistory(1)"
            class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 hover:text-blue-600">
            🔍
          </button>
        </div>
      </div>
    </div>

    <Card>
      <CardContent class="p-0">
        <div v-if="isLoading" class="p-12 text-center text-gray-500">
          Loading history...
        </div>

        <div v-else-if="matches.length === 0" class="p-12 text-center text-gray-500">
          {{ searchQuery ? 'No results found.' : 'You have no multiplayer game history yet.' }}
        </div>

        <div v-else class="overflow-x-auto">
          <table class="w-full text-sm text-left">
            <thead class="text-xs text-gray-700 uppercase bg-slate-50 border-b">
              <tr>
                <th class="px-6 py-3 w-10"></th>
                <th class="px-6 py-3">Date</th>
                <th class="px-6 py-3">Type</th>
                <th class="px-6 py-3">Players</th>
                <th class="px-6 py-3">Winner</th>

                <th class="px-6 py-3 text-center">
                  {{ isAdmin ? 'Score (P1 / P2)' : 'Score (Marks)' }}
                </th>

                <th class="px-6 py-3 text-right">Result</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">

              <template v-for="match in matches" :key="match.id">

                <tr @click="toggleDetails(match.id)" class="hover:bg-slate-50 transition-colors cursor-pointer group"
                  :class="{ 'bg-slate-100/70': isExpanded(match.id) }">

                  <td class="px-6 py-4 text-slate-400">
                    <span class="inline-block transition-transform duration-200"
                      :class="{ 'rotate-90': isExpanded(match.id) }">
                      ▶
                    </span>
                  </td>

                  <td class="px-6 py-4 text-slate-700">
                    {{ new Date(match.ended_at).toLocaleDateString() }}
                  </td>

                  <td class="px-6 py-4">
                    <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded">
                      Bisca {{ match.type }}
                    </span>
                  </td>

                  <td class="px-6 py-4">
                    <div class="flex flex-col">
                      <span class="font-medium"
                        :class="{ 'text-blue-600 font-bold': !isAdmin && match.player1?.id === authStore.currentUser?.id }">
                        {{ match.player1?.nickname || '???' }}
                      </span>
                      <span class="text-gray-500 text-xs font-bold">VS</span>
                      <span class="font-medium"
                        :class="{ 'text-blue-600 font-bold': !isAdmin && match.player2?.id === authStore.currentUser?.id }">
                        {{ match.player2?.nickname || '???' }}
                      </span>
                    </div>
                  </td>

                  <td class="px-6 py-4 font-bold text-slate-700">
                    {{ match.winner?.nickname || 'Draw/N/A' }}
                  </td>

                  <td class="px-6 py-4 text-center">
                    <div
                      class="inline-flex items-center gap-3 bg-slate-100 px-3 py-1 rounded-lg border border-slate-200">

                      <div class="flex gap-1" :title="isAdmin ? 'Player 1' : 'You'">
                        <span v-for="n in (match.user_marks_won || 0)" :key="'u' + n" 
                        :class="['w-1 h-4 rounded-sm', isAdmin ? 'bg-gray-500' : 'bg-blue-600']">
                        </span>
                        <span v-if="!match.user_marks_won" class="text-gray-400 font-mono text-xs">0</span>
                      </div>

                      <span class="text-slate-400">/</span>

                      <div class="flex gap-1" :title="isAdmin ? 'Player 2' : 'Opponent'">
                        <span v-for="n in (match.opponent_marks_won || 0)" :key="'o' + n" :class="['w-1 h-4 rounded-sm', 'bg-gray-500' ]"> 
                        </span>
                        <span v-if="!match.opponent_marks_won" class="text-gray-400 font-mono text-xs">0</span>
                      </div>
                    </div>
                  </td>

                  <td class="px-6 py-4 text-right">
                    <template v-if="isAdmin">
                      <span
                        class="text-gray-700 font-bold bg-gray-200 px-3 py-1 rounded-full text-xs uppercase border border-gray-300">
                        {{ match.status === 'Ended' ? 'Finished' : match.status }}
                      </span>
                    </template>

                    <template v-else>
                      <span v-if="match.result === 'WIN'"
                        class="text-green-700 font-bold bg-green-50 px-3 py-1 rounded-full text-xs uppercase border border-green-100">
                        WIN
                      </span>
                      <span v-else-if="match.result === 'LOSS'"
                        class="text-red-700 font-bold bg-red-50 px-3 py-1 rounded-full text-xs uppercase border border-red-100">
                        LOSS
                      </span>
                      <span v-else class="text-gray-600 bg-gray-100 px-3 py-1 rounded-full text-xs uppercase">
                        {{ match.status }}
                      </span>
                    </template>
                  </td>
                </tr>

                <tr v-if="isExpanded(match.id)" class="bg-slate-50/80 shadow-inner">
                  <td colspan="7" class="px-6 py-4">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 text-sm">

                      <div class="space-y-2 border-r border-slate-200 pr-4 max-h-40 overflow-y-auto">
                        <h4 class="font-bold text-slate-700 uppercase text-xs mb-2">Score per Game (Hand)</h4>
                        <ul v-if="match.games && match.games.length" class="text-xs space-y-2">
                          <li v-for="(game, index) in match.games" :key="game.id"
                            class="p-2 rounded flex justify-between items-center border border-slate-100 bg-white"
                            :class="{
                              'bg-green-50': (isAdmin && game.winner_user_id === match.player1?.id) || (!isAdmin && game.winner_user_id === authStore.currentUser?.id),
                              'bg-red-50': (isAdmin && game.winner_user_id === match.player2?.id) || (!isAdmin && game.winner_user_id && game.winner_user_id !== authStore.currentUser?.id),
                              'bg-gray-100': !game.winner_user_id
                            }">

                            <span class="font-semibold text-slate-700">Game #{{ index + 1 }}</span>

                            <div class="flex items-center gap-2">
                              <span class="font-mono text-slate-800">
                                {{ game.player1_points || 0 }} / {{ game.player2_points || 0 }} pts
                              </span>

                              <span v-if="game.player1_points == 120 || game.player2_points == 120"
                                class="ml-1 text-xl text-yellow-600" title="Flag (Bandeira)">
                                🚩
                              </span>
                              <span v-else-if="game.player1_points >= 91 || game.player2_points >= 91"
                                class="ml-1 text-lg text-purple-600" title="Capote (2 Marks)">
                                ♦️
                              </span>
                              <span v-else-if="game.player1_points >= 61 || game.player2_points >= 61"
                                class="ml-1 text-lg text-blue-400 font-bold" title="Mark">
                                |
                              </span>

                              <span class="text-xs font-bold px-2 py-0.5 rounded" :class="{
                                'text-green-700 bg-green-200': game.winner_user_id && ((isAdmin && game.winner_user_id === match.player1?.id) || (!isAdmin && game.winner_user_id === authStore.currentUser?.id)),
                                'text-red-700 bg-red-200': game.winner_user_id && ((isAdmin && game.winner_user_id === match.player2?.id) || (!isAdmin && game.winner_user_id !== authStore.currentUser?.id)),
                                'text-gray-500 bg-gray-300': !game.winner_user_id
                              }">

                                {{ isAdmin ? (game.winner_user_id === match.player1?.id ? 'P1' : (game.winner_user_id ?
                                  'P2' : '-')) :
                                  (game.winner_user_id === authStore.currentUser?.id ? 'YOU' : (game.winner_user_id ?
                                    'OPP.' : '-')) }}
                              </span>
                            </div>
                          </li>
                        </ul>
                        <p v-else class="text-gray-400 italic text-center py-4">Per-game details unavailable.</p>
                      </div>

                      <div class="space-y-2 border-r border-slate-200 pr-4">
                        <h4 class="font-bold text-slate-700 uppercase text-xs mb-2">Total Points (Match)</h4>
                        <ul class="text-xs space-y-2">
                          <li class="font-bold text-base">
                            <span
                              :class="{ 'text-blue-600': match.player1?.id === authStore.currentUser?.id, 'text-gray-600': match.player1?.id !== authStore.currentUser?.id }">{{
                                match.player1?.nickname || 'P1' }}:</span>
                            <span class="font-bold">{{ match.player1_points || 0 }} pts</span>
                          </li>
                          <li class="font-bold text-base">
                            <span
                              :class="{ 'text-blue-600': match.player2?.id === authStore.currentUser?.id, 'text-gray-600': match.player2?.id !== authStore.currentUser?.id }">{{
                                match.player2?.nickname || 'P2' }}:</span>
                            <span class="font-bold">{{ match.player2_points || 0 }} pts</span>
                          </li>
                          <li class="mt-3 text-sm font-semibold text-slate-700">
                            Rules: 1 Mark (61 and 90 points), Capote (91 to 119 points), Flag (120 Points)
                          </li>
                        </ul>
                      </div>

                      <div class="space-y-2 border-r border-slate-200 pr-4">
                        <h4 class="font-bold text-slate-700 uppercase text-xs mb-2">Duration and Times</h4>
                        <div class="text-xs text-gray-500">
                          Start: <span class="font-medium text-slate-700 block">{{ match.began_at ? new
                            Date(match.began_at).toLocaleString() : '-' }}</span>
                        </div>
                        <div class="text-xs text-gray-500">
                          End: <span class="font-medium text-slate-700 block">{{ match.ended_at ? new
                            Date(match.ended_at).toLocaleString() : '-' }}</span>
                        </div>
                        <div class="text-xs text-gray-500 mt-2">
                          Total Time: <span class="font-mono bg-slate-200 px-2 py-1 rounded text-xs">{{
                            formatDuration(match.total_time) }}</span>
                        </div>
                      </div>

                      <div class="space-y-2">
                        <h4 class="font-bold text-slate-700 uppercase text-xs">Technical Info</h4>
                        <ul class="space-y-1 text-xs text-gray-600">
                          <li><span class="font-semibold">Match ID:</span> #{{ match.id }}</li>
                          <li><span class="font-semibold">Stake:</span> {{ match.stake || 0 }} Coins</li>
                          <li><span class="font-semibold">Winner:</span> {{ match.winner?.nickname || 'N/A' }}</li>
                        </ul>
                      </div>
                    </div>
                  </td>
                </tr>
              </template>

            </tbody>
          </table>
        </div>
      </CardContent>
    </Card>

    <div class="flex justify-center items-center gap-4 pt-4" v-if="pagination.total > pagination.per_page">
      <Button variant="outline" :disabled="!pagination.prev_page_url" @click="changePage(pagination.current_page - 1)">
        &larr; Previous
      </Button>
      <span class="text-sm font-medium text-slate-600">
        Page {{ pagination.current_page }}
      </span>
      <Button variant="outline" :disabled="!pagination.next_page_url" @click="changePage(pagination.current_page + 1)">
        Next &rarr;
      </Button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue' // Computed importado
import { useAPIStore } from '@/stores/api'
import { useAuthStore } from '@/stores/auth'
import { Card, CardContent } from '@/components/ui/card'
import { Button } from '@/components/ui/button'

const apiStore = useAPIStore()
const authStore = useAuthStore()

// --- COMPUTED PARA ADMIN ---
const isAdmin = computed(() => authStore.currentUser?.type === 'A')
const searchQuery = ref('')
// ---------------------------

const matches = ref([])
const pagination = ref({})
const isLoading = ref(true)
const expandedMatchIds = ref(new Set())

const formatDuration = (seconds) => {
  if (!seconds || seconds < 0) return 'N/A';
  seconds = Math.round(parseFloat(seconds));
  const minutes = Math.floor(seconds / 60);
  const remainingSeconds = seconds % 60;
  const pad = (num) => String(num).padStart(2, '0');
  return `${pad(minutes)}m ${pad(remainingSeconds)}s`;
}

const toggleDetails = (id) => {
  if (expandedMatchIds.value.has(id)) {
    expandedMatchIds.value.delete(id)
  } else {
    expandedMatchIds.value.add(id)
  }
  expandedMatchIds.value = new Set(expandedMatchIds.value)
}

const isExpanded = (id) => {
  return expandedMatchIds.value.has(id)
}

const loadHistory = async (page = 1) => {
  isLoading.value = true
  try {
    // Envia o termo de pesquisa se for Admin
    const search = isAdmin.value ? searchQuery.value : ''
    const response = await apiStore.getHistory(page, search)

    matches.value = response.data.data

    pagination.value = {
      current_page: response.data.current_page,
      last_page: response.data.last_page,
      prev_page_url: response.data.prev_page_url,
      next_page_url: response.data.next_page_url,
      total: response.data.total,
      per_page: response.data.per_page
    }
  } catch (error) {
    console.error('Erro ao carregar histórico:', error)
  } finally {
    isLoading.value = false
  }
}

const changePage = (page) => {
  if (page > 0 && page <= pagination.value.last_page) {
    loadHistory(page)
  }
}

onMounted(() => {
  loadHistory()
})
</script>