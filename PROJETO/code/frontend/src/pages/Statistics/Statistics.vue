<template>
  <div class="max-w-7xl mx-auto p-6 space-y-8">
    
    <div class="text-center">
      <h1 class="text-3xl font-bold text-slate-900">Platform Statistics</h1>
      <p class="text-slate-500 mt-2">Data and metrics about the Bisca Game</p>
    </div>

    <div class="flex justify-center border-b border-slate-200 mb-6">
      <nav class="-mb-px flex space-x-8" aria-label="Tabs">
        <button
          @click="changeTab('public')"
          :class="[
            currentTab === 'public'
              ? 'border-blue-500 text-blue-600'
              : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300',
            'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors'
          ]"
        >
          General
        </button>

        <button
          v-if="authStore.currentUser && authStore.currentUser.type === 'P'"
          @click="changeTab('player')"
          :class="[
            currentTab === 'player'
              ? 'border-green-500 text-green-600'
              : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300',
            'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors'
          ]"
        >
          My Statistics
        </button>

        <button
          v-if="authStore.currentUser && authStore.currentUser.type === 'A'"
          @click="changeTab('admin')"
          :class="[
            currentTab === 'admin'
              ? 'border-purple-500 text-purple-600'
              : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300',
            'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors'
          ]"
        >
          Administration
        </button>
      </nav>
    </div>

   <div v-if="currentTab === 'player' && authStore.currentUser?.type === 'P'">
      
      <div v-if="isPlayerLoading" class="text-center py-10">
        <p class="text-lg text-slate-600 animate-pulse">Loading your data...</p>
      </div>

      <div v-else class="space-y-8">
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
           <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 text-center">
             <p class="text-sm font-medium text-slate-500 uppercase tracking-wider">Games Played</p>
             <p class="text-4xl font-extrabold text-slate-800 mt-2">{{ myStats.totalGames }}</p>
           </div>
           <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 text-center">
             <p class="text-sm font-medium text-slate-500 uppercase tracking-wider">Wins</p>
             <p class="text-4xl font-extrabold text-green-600 mt-2">{{ myStats.totalWins }}</p>
           </div>
           <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 text-center">
             <p class="text-sm font-medium text-slate-500 uppercase tracking-wider">Win Rate</p>
             <p class="text-4xl font-extrabold text-blue-600 mt-2">{{ winRate }}%</p>
           </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="bg-slate-50 px-6 py-4 border-b border-slate-200">
                <h3 class="font-bold text-slate-800">Recent Games History</h3>
             </div>
             <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                     <thead class="text-xs text-slate-500 uppercase bg-slate-50 border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-3">Date</th>
                            <th class="px-6 py-3">Opponent</th>
                            <th class="px-6 py-3 text-center">Score</th>
                            <th class="px-6 py-3 text-right">Result</th>
                        </tr>
                     </thead>
                     <tbody class="divide-y divide-slate-100">
                        <tr v-for="(game, index) in myStats.history" :key="index">
                            <td class="px-6 py-4 text-slate-500 whitespace-nowrap">{{ formatDate(game.date) }}</td>
                             <td class="px-6 py-4 font-medium text-slate-900">{{ game.opponent }}</td>
                             <td class="px-6 py-4 text-center">
                               <span class="font-bold">{{ game.my_points }}</span> - {{ game.opponent_points }}
                             </td>
                             <td class="px-6 py-4 text-right">
                               <span :class="['px-3 py-1 rounded-full text-xs font-bold uppercase', game.result === 'Win' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700']">
                                 {{ game.result }}
                               </span>
                             </td>
                        </tr>
                     </tbody>
                </table>
             </div>
        </div>
        <div v-if="myStats.decks && myStats.decks.length > 0" class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="bg-slate-50 px-6 py-4 border-b border-slate-200 flex justify-between items-center">
                <h3 class="font-bold text-slate-800 flex items-center gap-2">
                    <span>🃏</span> Card Collection Progress
                </h3>
                <RouterLink to="/card-packs" class="text-xs text-blue-600 hover:underline">Go to Shop &rarr;</RouterLink>
            </div>
            
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div v-for="deck in myStats.decks" :key="deck.id" 
                     class="border rounded-lg p-4 relative"
                     :class="deck.is_owned ? 'bg-green-50 border-green-200' : 'bg-white border-slate-200'">
                    
                    <div v-if="deck.is_owned" class="absolute top-2 right-2 bg-green-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full">
                        OWNED
                    </div>

                    <div class="flex items-center gap-4 mb-3">
                        <div class="w-16 h-12 bg-slate-200 rounded overflow-hidden shrink-0">
                             <img :src="`http://localhost:8000/storage/card_decks/${deck.image}`" class="w-full h-full object-cover">
                        </div>
                        <div>
                            <h4 class="font-bold text-sm text-slate-800">{{ deck.name }}</h4>
                            <p class="text-xs text-slate-500">
                                {{ deck.type === 'WINS' ? 'Veteran Reward' : 'Mastery Reward' }}
                            </p>
                        </div>
                    </div>

                    <div class="space-y-1">
                        <div class="flex justify-between text-xs font-medium text-slate-600">
                            <span>Progress</span>
                            <span>{{ deck.user_progress }} / {{ deck.wins_required }}</span>
                        </div>
                        
                        <Progress 
                            :model-value="Math.min(100, (deck.user_progress / deck.wins_required) * 100)" 
                            class="h-2"
                            :class="deck.is_owned ? 'bg-green-200' : 'bg-slate-100'"
                        />
                        
                        <p v-if="!deck.is_owned" class="text-[10px] text-slate-400 text-right mt-1">
                            {{ deck.type === 'WINS' ? 'Win more matches to unlock' : 'Score high in games to unlock' }}
                        </p>
                        <p v-else class="text-[10px] text-green-600 text-right mt-1 font-bold">
                            Unlocked!
                        </p>
                    </div>
                </div>
            </div>
        </div>

      </div>
  </div>

    <div v-if="currentTab === 'public'">
      <div v-if="isLoading" class="text-center py-10">
        <p class="text-lg text-slate-600 animate-pulse">Loading statistics...</p>
      </div>

      <div v-else class="space-y-8 max-w-4xl mx-auto">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-slate-500 uppercase tracking-wider">Registered Players</p>
              <p class="text-4xl font-extrabold text-blue-600 mt-2">{{ publicStats.totalPlayers }}</p>
            </div>
            <div class="h-12 w-12 bg-blue-100 rounded-full flex items-center justify-center text-2xl">
              👥
            </div>
          </div>

          <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-slate-500 uppercase tracking-wider">Total Games</p>
              <p class="text-4xl font-extrabold text-green-600 mt-2">{{ publicStats.totalGames }}</p>
            </div>
            <div class="h-12 w-12 bg-green-100 rounded-full flex items-center justify-center text-2xl">
              🎮
            </div>
          </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
          <div class="bg-slate-50 p-4 border-b border-slate-200">
            <h2 class="font-bold text-slate-800 flex items-center gap-2">
              <span>🏆</span> Top 3 Winners
            </h2>
          </div>
          
          <div class="divide-y divide-slate-100">
            <div 
              v-for="(winner, index) in publicStats.topWinners" 
              :key="index"
              class="p-4 flex items-center justify-between hover:bg-slate-50 transition-colors"
            >
              <div class="flex items-center gap-4">
                <div class="shrink-0 w-8 text-center font-bold text-xl">
                  <span v-if="index === 0">🥇</span>
                  <span v-else-if="index === 1">🥈</span>
                  <span v-else-if="index === 2">🥉</span>
                </div>
                <div class="flex items-center gap-3">
                  <img 
                    :src="getAvatarUrl(winner.photo_avatar_filename)" 
                    @error="handleImageError" 
                    alt="Avatar" 
                    class="w-10 h-10 rounded-full object-cover bg-slate-200 border border-slate-300"
                  >
                  <span class="font-semibold text-slate-900">{{ winner.nickname }}</span>
                </div>
              </div>
              <div class="text-right">
                <span class="block text-lg font-bold text-slate-900">{{ winner.total_wins }}</span>
                <span class="text-xs text-slate-500 uppercase">Wins</span>
              </div>
            </div>
             <div v-if="publicStats.topWinners.length === 0" class="p-8 text-center text-slate-500">
              Not enough data for ranking yet.
            </div>
          </div>
        </div>
      </div>  
    </div>

    <div v-if="currentTab === 'admin' && authStore.currentUser?.type === 'A'">
      <div v-if="isAdminLoading" class="text-center py-10">
        <p class="text-lg text-slate-600 animate-pulse">Loading financial data...</p>
      </div>

      <div v-else class="space-y-8">
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
          
          <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
            <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
              <span>💰</span> Revenue (€) per Month
            </h3>
            <div class="h-64 relative">
               <Bar v-if="chartDataPurchases" :data="chartDataPurchases" :options="chartOptions" />
               <p v-else class="text-center text-slate-400 mt-10">No purchase data.</p>
            </div>
          </div>

          <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
            <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
              <span>💳</span> Payment Methods
            </h3>
            <div class="h-64 relative flex justify-center">
               <Doughnut v-if="chartDataPayments" :data="chartDataPayments" :options="chartOptions" />
               <p v-else class="text-center text-slate-400 mt-10">No payment data.</p>
            </div>
          </div>

          <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
            <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
              <span>🪙</span> Economy (Earned vs Spent)
            </h3>
            <div class="h-64 relative flex justify-center">
               <Doughnut v-if="chartDataCoins" :data="chartDataCoins" :options="chartOptions" />
               <p v-else class="text-center text-slate-400 mt-10">No transaction data.</p>
            </div>
          </div>

          <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
            <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
              <span>📈</span> Activity (Total Games)
            </h3>
            <div class="h-64 relative w-full">
                <Line v-if="chartDataGamesTotal" :data="chartDataGamesTotal" :options="chartOptionsLine" />
                <p v-else class="text-center text-slate-400 mt-10">No activity data.</p>
            </div>
          </div>

        </div> <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
          <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
              <span>📊</span> Activity Breakdown (Bisca 3 vs Bisca 9)
            </h3>
            <div class="flex gap-4 text-xs font-medium">
               <div class="flex items-center gap-1"><div class="w-3 h-3 bg-blue-500 rounded-full"></div> Bisca de 3</div>
               <div class="flex items-center gap-1"><div class="w-3 h-3 bg-orange-500 rounded-full"></div> Bisca de 9</div>
            </div>
          </div>
          
          <div class="h-80 w-full relative">
              <Line v-if="chartDataGamesByType" :data="chartDataGamesByType" :options="chartOptionsLine" />
              <p v-else class="text-center text-slate-400 mt-20">No activity data yet.</p>
          </div>
        </div>

        <div class="bg-slate-900 text-white p-6 rounded-xl flex flex-wrap justify-around gap-4 items-center shadow-lg">
            <div class="text-center">
                <p class="text-slate-400 text-sm uppercase font-semibold">Total Revenue</p>
                <p class="text-2xl font-bold mt-1">{{ totalRevenue }} €</p>
            </div>
            
            <div class="hidden md:block w-px h-12 bg-slate-700"></div>

            <div class="text-center">
                <p class="text-slate-400 text-sm uppercase font-semibold">Coins in Circulation</p>
                <p class="text-2xl font-bold text-yellow-400 mt-1">{{ coinsBalance }} 🪙</p>
            </div>

            <div class="hidden md:block w-px h-12 bg-slate-700"></div>

            <div class="text-center">
                <p class="text-slate-400 text-sm uppercase font-semibold">Avg. Duration / Game</p>
                <p class="text-2xl font-bold text-blue-400 mt-1">
                    {{ formatDuration(adminStats.averageGameTime) }}
                </p>
            </div>
        </div>

      </div>
    </div>

    <div class="flex justify-center mt-6">
       <button 
         @click="refreshData" 
         class="px-6 py-2 bg-slate-900 text-white rounded-lg hover:bg-slate-800 transition-colors text-sm font-medium"
       >
         Refresh Data
       </button>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue' // <--- ADICIONADO O WATCH AQUI
import axios from 'axios'
import { useAuthStore } from '@/stores/auth'
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  BarElement,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend,
  ArcElement,
  Filler
} from 'chart.js'
import { Bar, Doughnut, Line } from 'vue-chartjs'
import { Progress } from '@/components/ui/progress'

// --- 1. CHART.JS CONFIGURATION ---
ChartJS.register(
  CategoryScale, 
  LinearScale, 
  BarElement, 
  PointElement, 
  LineElement, 
  Title, 
  Tooltip, 
  Legend, 
  ArcElement,
  Filler
)

// --- 2. STATE AND STORES ---
const authStore = useAuthStore()
const currentTab = ref('public')
const isLoading = ref(false)
const isAdminLoading = ref(false)
const isPlayerLoading = ref(false) 

// Public Data
const publicStats = ref({
  totalPlayers: 0,
  totalGames: 0,
  topWinners: []
})

// Admin Data
const adminStats = ref({
  purchases: [],
  coins: { earned: 0, spent: 0 },
  gamesPerDay: [],
  averageGameTime: 0,
  paymentTypes: []
})

// Player Data
const myStats = ref({
  totalGames: 0,
  totalWins: 0,
  totalLosses: 0,
  history: [],
  decks: [] // <--- ADICIONA ISTO
})

// --- 3. COMPUTED DATA ---

// A. Win Rate (Player)
const winRate = computed(() => {
  if (!myStats.value.totalGames) return 0
  return Math.round((myStats.value.totalWins / myStats.value.totalGames) * 100)
})

// B. Admin Charts
const chartDataPurchases = computed(() => {
  if (!adminStats.value.purchases?.length) return null
  return {
    labels: adminStats.value.purchases.map(p => p.month),
    datasets: [{
      label: 'Sales Volume (€)',
      backgroundColor: '#3b82f6',
      data: adminStats.value.purchases.map(p => p.total),
      borderRadius: 4
    }]
  }
})

const chartDataPayments = computed(() => {
  if (!adminStats.value.paymentTypes?.length) return null
  const colors = ['#f59e0b', '#3b82f6', '#ef4444', '#10b981', '#8b5cf6', '#6366f1']
  return {
    labels: adminStats.value.paymentTypes.map(p => p.type),
    datasets: [{
      backgroundColor: colors,
      data: adminStats.value.paymentTypes.map(p => p.count),
      borderWidth: 1
    }]
  }
})

const chartDataCoins = computed(() => {
  const { earned, spent } = adminStats.value.coins || { earned: 0, spent: 0 }
  if (!earned && !spent) return null
  return {
    labels: ['Earned by Players', 'Spent/Burned'],
    datasets: [{
      backgroundColor: ['#22c55e', '#ef4444'],
      data: [earned, spent]
    }]
  }
})

// --- 1. GRÁFICO TOTAL (SOMA TUDO - DESDE SEMPRE) ---
const chartDataGamesTotal = computed(() => {
  if (!adminStats.value.gamesPerDay?.length) return null
  
  const rawData = adminStats.value.gamesPerDay
  
  // 1. Obter TODAS as datas únicas e ordenar
  // Removi o .slice(-30) aqui
  const allDates = [...new Set(rawData.map(item => item.date))].sort()

  // 2. Somar Tipo 3 + Tipo 9 para cada dia (usando todas as datas)
  const dataTotal = allDates.map(date => {
    const entries = rawData.filter(d => d.date === date)
    return entries.reduce((acc, curr) => acc + curr.count, 0)
  })

  return {
    labels: allDates, // Usa o histórico completo
    datasets: [{
        label: 'Total Games',
        backgroundColor: 'rgba(139, 92, 246, 0.2)',
        borderColor: '#8b5cf6',
        pointBackgroundColor: '#8b5cf6',
        borderWidth: 2,
        data: dataTotal,
        fill: true,
        tension: 0.3
    }]
  }
})

// --- 2. GRÁFICO COMPARATIVO (3 vs 9 - DESDE SEMPRE) ---
const chartDataGamesByType = computed(() => {
  if (!adminStats.value.gamesPerDay?.length) return null

  const rawData = adminStats.value.gamesPerDay
  
  // 1. Obter TODAS as datas únicas
  // Removi o .slice(-30) aqui também
  const allDates = [...new Set(rawData.map(item => item.date))].sort()

  // 2. Filtrar dados usando todas as datas
  const data3 = allDates.map(date => {
    const entry = rawData.find(d => d.date === date && d.type === '3')
    return entry ? entry.count : 0
  })

  const data9 = allDates.map(date => {
    const entry = rawData.find(d => d.date === date && d.type === '9')
    return entry ? entry.count : 0
  })

  return {
    labels: allDates, // Usa o histórico completo
    datasets: [
      {
        label: 'Bisca de 3',
        backgroundColor: 'rgba(59, 130, 246, 0.2)',
        borderColor: '#3b82f6',
        pointBackgroundColor: '#3b82f6',
        borderWidth: 2,
        data: data3,
        tension: 0.3,
        fill: true
      },
      {
        label: 'Bisca de 9',
        backgroundColor: 'rgba(249, 115, 22, 0.2)',
        borderColor: '#f97316',
        pointBackgroundColor: '#f97316',
        borderWidth: 2,
        data: data9,
        tension: 0.3,
        fill: true
      }
    ]
  }
})


const totalRevenue = computed(() => {
    return adminStats.value.purchases?.reduce((acc, curr) => acc + Number(curr.total), 0).toFixed(2) || '0.00'
})

const coinsBalance = computed(() => {
    const earned = adminStats.value.coins?.earned || 0
    const spent = adminStats.value.coins?.spent || 0
    return earned - spent
})

// --- 4. CHART OPTIONS ---
const chartOptions = { 
  responsive: true, 
  maintainAspectRatio: false 
}

const chartOptionsLine = {
  responsive: true,
  maintainAspectRatio: false,
  interaction: {
    mode: 'index',
    intersect: false,
  },
  plugins: {
    legend: { display: false } // Opcional: esconde a legenda se quiseres mais espaço
  },
  elements: {
    point: {
      radius: 0, // <--- TRUQUE: Remove as "bolinhas" (só aparecem ao passar o rato)
      hitRadius: 10,
      hoverRadius: 4
    },
    line: {
      tension: 0.1 // Deixa a linha menos curva para ver melhor os picos
    }
  },
  scales: {
    y: { 
      beginAtZero: true, 
      ticks: { precision: 0 } 
    },
    x: {
      ticks: {
        maxTicksLimit: 10, // <--- TRUQUE: Mostra no máximo 10 datas no eixo X
        maxRotation: 0,
        minRotation: 0
      },
      grid: {
        display: false // Remove as linhas verticais para limpar o visual
      }
    }
  }
}

// --- 5. HELPER FUNCTIONS ---

const formatDuration = (seconds) => {
  if (!seconds || isNaN(seconds)) return '0s'
  const minutes = Math.floor(seconds / 60)
  const remainingSeconds = Math.round(seconds % 60)
  return `${minutes}m ${remainingSeconds}s`
}

const formatDate = (dateString) => {
  if (!dateString) return '-'
  return new Date(dateString).toLocaleDateString()
}

const getAvatarUrl = (filename) => {
  if (!filename) {
    return 'http://localhost:8000/storage/photos_avatars/anonymous.png' 
  }
  
return `http://localhost:8000/storage/photos_avatars/${filename}`
}

const changeTab = (tab) => {
  currentTab.value = tab
  // Removemos as chamadas manuais aqui porque o WATCH em baixo já vai tratar disto automaticamente
}

const refreshData = () => {
  if (currentTab.value === 'public') fetchPublicStats()
  else if (currentTab.value === 'admin') fetchAdminStats()
  else if (currentTab.value === 'player') fetchPlayerStats()
}
const handleImageError = (event) => {
  // Se a imagem falhar, vai buscar a "anonymous.png" à pasta storage do Laravel
  event.target.src = 'http://localhost:8000/storage/anonymous.png'
}

// --- 6. API CALLS ---

const fetchPublicStats = async () => {
  isLoading.value = true
  try {
    const response = await axios.get('http://localhost:8000/api/public-statistics')
    publicStats.value = {
      totalPlayers: response.data.total_players,
      totalGames: response.data.total_games_played,
      topWinners: response.data.top_winners
    }
  } catch (error) {
    console.error("Error loading public stats:", error)
  } finally {
    isLoading.value = false
  }
}

const fetchAdminStats = async () => {
  if (authStore.currentUser?.type !== 'A') return
  isAdminLoading.value = true
  try {
    const response = await axios.get('http://localhost:8000/api/admin-statistics')
    adminStats.value = {
        purchases: response.data.purchases_by_month,
        coins: response.data.coins,
        gamesPerDay: response.data.games_per_day,
        averageGameTime: response.data.average_game_time,
        paymentTypes: response.data.payment_types
    }
  } catch (error) {
    console.error("Error loading admin stats:", error)
  } finally {
    isAdminLoading.value = false
  }
}

const fetchPlayerStats = async () => {
  if (authStore.currentUser?.type !== 'P') return
  isPlayerLoading.value = true
  try {
    const response = await axios.get('http://localhost:8000/api/player-statistics')
    
    myStats.value = {
        totalGames: response.data.totalGames,
        totalWins: response.data.totalWins,
        totalLosses: response.data.totalLosses,
        history: response.data.history,
        decks: response.data.decks // <--- O BACKEND AGORA ENVIA ISTO
    }
  } catch (error) {
    console.error("Error loading player stats:", error)
  } finally {
    isPlayerLoading.value = false
  }
}

// --- 7. WATCHER (CORRIGIDO) ---
// Isto faz com que os dados carreguem logo ao abrir e ao trocar de aba
watch(currentTab, (newTab) => {
  if (newTab === 'public') {        // Era 'general', mudei para 'public'
    fetchPublicStats()              // Era fetchGeneralStats
  } else if (newTab === 'player') { // Era 'personal', mudei para 'player'
    fetchPlayerStats()              // Era fetchPersonalStats
  } else if (newTab === 'admin') {
    fetchAdminStats()
  }
}, { immediate: true })

</script>