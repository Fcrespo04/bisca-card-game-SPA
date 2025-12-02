<template>
  <div class="max-w-6xl mx-auto p-6 space-y-6">
    <div class="flex justify-between items-center">
      <h1 class="text-3xl font-bold">Histórico de Jogos</h1>
      <span class="text-sm text-gray-500">Multijogador</span>
    </div>

    <Card>
      <CardContent class="p-0">
        <div v-if="isLoading" class="p-8 text-center text-gray-500">A carregar histórico...</div>
        
        <div v-else-if="matches.length === 0" class="p-8 text-center text-gray-500">
          Ainda não há registo de jogos multijogador.
        </div>

        <div v-else>
          <table class="w-full text-sm text-left">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b">
              <tr>
                <th class="px-6 py-3">Data</th>
                <th class="px-6 py-3">Tipo</th>
                <th class="px-6 py-3">Jogadores</th>
                <th class="px-6 py-3">Vencedor</th>
                <th class="px-6 py-3 text-right">Resultado</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="match in matches" :key="match.id" class="border-b hover:bg-gray-50">
                <td class="px-6 py-4">
                  {{ new Date(match.ended_at).toLocaleDateString() }}
                  <span class="text-xs text-gray-400 block">
                    {{ new Date(match.ended_at).toLocaleTimeString() }}
                  </span>
                </td>
                
                <td class="px-6 py-4">
                  <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded">
                    Bisca de {{ match.type }}
                  </span>
                </td>

                <td class="px-6 py-4">
                  <div class="flex flex-col">
                    <span class="font-medium">{{ match.player1?.nickname || '???' }}</span>
                    <span class="text-gray-500 text-xs">vs</span>
                    <span class="font-medium">{{ match.player2?.nickname || '???' }}</span>
                  </div>
                </td>

                <td class="px-6 py-4 font-bold text-gray-700">
                  {{ match.winner?.nickname || 'Empate/Cancelado' }}
                </td>

                <td class="px-6 py-4 text-right">
                  <span v-if="match.winner_user_id === authStore.currentUser?.id" 
                        class="text-green-600 font-bold bg-green-50 px-2 py-1 rounded">
                    VITÓRIA
                  </span>
                  <span v-else-if="match.status === 'E'" 
                        class="text-red-600 font-bold bg-red-50 px-2 py-1 rounded">
                    DERROTA
                  </span>
                  <span v-else class="text-gray-500">
                    {{ match.status }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </CardContent>
    </Card>

    <div class="flex justify-center gap-4" v-if="pagination.total > pagination.per_page">
      <Button variant="outline" :disabled="!pagination.prev_page_url" @click="changePage(pagination.current_page - 1)">
        Anterior
      </Button>
      <span class="py-2 text-sm text-gray-600">
        Página {{ pagination.current_page }} de {{ pagination.last_page }}
      </span>
      <Button variant="outline" :disabled="!pagination.next_page_url" @click="changePage(pagination.current_page + 1)">
        Seguinte
      </Button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useAPIStore } from '@/stores/api'
import { useAuthStore } from '@/stores/auth'
import { Card, CardContent } from '@/components/ui/card'
import { Button } from '@/components/ui/button'

const apiStore = useAPIStore()
const authStore = useAuthStore()

const matches = ref([])
const pagination = ref({})
const isLoading = ref(true)

const loadHistory = async (page = 1) => {
  isLoading.value = true
  try {
    const response = await apiStore.getHistory(page)
    matches.value = response.data.data
    // Guardar dados de paginação (meta/links do Laravel)
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