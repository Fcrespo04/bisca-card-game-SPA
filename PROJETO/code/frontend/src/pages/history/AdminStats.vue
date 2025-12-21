<template>
  <div class="p-6 bg-gray-50 min-h-screen">
    <div class="flex justify-between items-center mb-8">
      <h1 class="text-3xl font-bold text-gray-800 tracking-tight">Platform Statistics</h1>
    </div>

    <div v-if="!loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
      <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
        <div class="text-blue-500 text-sm font-bold uppercase mb-2">Total Players</div>
        <div class="text-3xl font-black text-gray-800">{{ stats.summary?.total_users }}</div>
      </div>

      <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
        <div class="text-green-500 text-sm font-bold uppercase mb-2">Coins in Circulation</div>
        <div class="text-3xl font-black text-gray-800">🪙 {{ stats.summary?.coins_in_circulation }}</div>
      </div>

      <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
        <div class="text-yellow-600 text-sm font-bold uppercase mb-2">Total Revenue</div>
        <div class="text-3xl font-black text-gray-800">€ {{ stats.summary?.total_revenue_euros }}</div>
      </div>

      <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
        <div class="text-purple-500 text-sm font-bold uppercase mb-2">Multiplayer Games</div>
        <div class="text-3xl font-black text-gray-800">{{ stats.summary?.total_multiplayer_games }}</div>
      </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
      <div class="p-6 border-b border-gray-50 flex justify-between items-center">
        <h2 class="text-xl font-bold text-gray-700">Transaction History</h2>
        <span class="text-xs font-mono bg-gray-100 px-2 py-1 rounded text-gray-500">Page {{ stats.transactions?.current_page }}</span>
      </div>

      <div v-if="loading" class="p-20 text-center">
        <div class="animate-spin inline-block w-8 h-8 border-4 border-blue-500 border-t-transparent rounded-full mb-4"></div>
        <p class="text-gray-400">Processing data...</p>
      </div>

      <div v-else class="overflow-x-auto">
        <table class="w-full text-left">
          <thead>
            <tr class="bg-gray-50 text-gray-400 text-xs uppercase tracking-widest">
              <th class="px-6 py-4 font-semibold">Date and Time</th>
              <th class="px-6 py-4 font-semibold">Player (Nickname)</th>
              <th class="px-6 py-4 font-semibold">Movement Type</th>
              <th class="px-6 py-4 font-semibold text-right">Value (Coins)</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50">
            <tr v-for="t in stats.transactions?.data" :key="t.id" class="hover:bg-blue-50/30 transition">
              <td class="px-6 py-4 text-sm text-gray-600">{{ formatDate(t.transaction_datetime) }}</td>
              <td class="px-6 py-4">
                <div class="font-bold text-gray-700">{{ t.user?.nickname || 'N/A' }}</div>
                <div class="text-xs text-gray-400">{{ t.user?.name }}</div>
              </td>
              <td class="px-6 py-4">
                <span :class="getTypeClass(t.transaction_type?.name)" class="px-3 py-1 rounded-full text-xs font-bold shadow-sm">
                  {{ t.transaction_type?.name }}
                </span>
              </td>
              <td class="px-6 py-4 text-right font-mono font-bold text-lg" :class="t.coins > 0 ? 'text-green-500' : 'text-red-400'">
                {{ t.coins > 0 ? '+' : '' }}{{ t.coins }}
              </td>
            </tr>
          </tbody>
        </table>

        <div class="p-6 bg-gray-50 flex justify-between items-center">
          <button 
            @click="fetchStats(stats.transactions.current_page - 1)"
            :disabled="stats.transactions?.current_page === 1"
            class="px-6 py-2 bg-white border border-gray-200 rounded-xl text-sm font-bold text-gray-600 disabled:opacity-30 hover:shadow-md transition"
          >
            ← Previous
          </button>
          
          <div class="hidden md:block text-sm text-gray-400">
            Showing 10 of {{ stats.transactions?.total }} records
          </div>

          <button 
            @click="fetchStats(stats.transactions.current_page + 1)"
            :disabled="stats.transactions?.current_page === stats.transactions?.last_page"
            class="px-6 py-2 bg-white border border-gray-200 rounded-xl text-sm font-bold text-gray-600 disabled:opacity-30 hover:shadow-md transition"
          >
            Next →
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useAPIStore } from '@/stores/api'

const apiStore = useAPIStore()

const stats = ref({})
const loading = ref(true)

const formatDate = (dateStr) => {
  return new Date(dateStr).toLocaleString('en-GB', {
    day: '2-digit', month: '2-digit', year: 'numeric',
    hour: '2-digit', minute: '2-digit'
  })
}

const getTypeClass = (typeName) => {
  const types = {
    'Coin purchase': 'bg-green-100 text-green-700',
    'Game bonus': 'bg-blue-100 text-blue-700',
    'Game entry': 'bg-orange-100 text-orange-700',
    'Refund': 'bg-gray-100 text-gray-700'
  }
  return types[typeName] || 'bg-gray-100 text-gray-600'
}

const fetchStats = async (page = 1) => {
  loading.value = true
  try {
    const response = await apiStore.getAdminStats(page)
    stats.value = response.data
  } catch (error) {
    console.error("Error loading dashboard:", error)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchStats(1)
})
</script>