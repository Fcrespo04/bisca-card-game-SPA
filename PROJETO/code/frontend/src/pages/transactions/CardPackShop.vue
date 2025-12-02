<template>
  <div class="max-w-6xl mx-auto p-6 space-y-8">
    <div class="flex justify-between items-center">
      <h1 class="text-3xl font-bold flex items-center gap-3">
        <span>🃏</span> Card Pack Shop
      </h1>
    <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg flex items-center gap-3">
        <span class="text-2xl">💰</span>
        <div>
          <p class="text-sm text-yellow-800 uppercase font-semibold">Current Balance</p>
          <p class="text-2xl font-bold text-yellow-900">{{ authStore.currentUser?.coins_balance ?? 0 }} Coins</p>
        </div>
      </div>
    </div>
    <div v-if="isLoading" class="text-center py-12">Loading shop...</div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <Card v-for="deck in decks" :key="deck.id" 
            class="relative overflow-hidden transition-all hover:shadow-lg"
            :class="{'border-green-500 ring-2 ring-green-100': deck.is_owned}">
        
        <div v-if="deck.is_owned" class="absolute top-3 right-3 bg-green-500 text-white text-xs font-bold px-2 py-1 rounded-full">
          OWNED
        </div>

        <CardHeader class="pb-4">
          <CardTitle class="flex justify-between items-center">
            {{ deck.name }}
          </CardTitle>
          <CardDescription>{{ getDeckTypeLabel(deck) }}</CardDescription>
        </CardHeader>      
        <CardContent class="space-y-4">
          <div class="aspect-video bg-slate-100 rounded-md flex items-center justify-center overflow-hidden">
            <img :src="`${serverBaseURL}/storage/card_decks/${deck.image}`" 
                 class="object-contain h-full w-full p-2 hover:scale-105 transition-transform duration-500"
                 alt="Pack Preview">
          </div>
          <div v-if="(deck.type === 'WINS' || deck.type === 'POINTS') && !deck.is_owned" class="space-y-2">
            <div class="flex justify-between text-sm">
              <span>Progress</span>         
              <span v-if="deck.type === 'WINS'" class="font-medium">
                 {{ deck.user_progress }} / {{ deck.wins_required }} Wins
              </span>
              <span v-else class="font-medium">
                 Best: {{ deck.user_progress }} / {{ deck.min_points_required }} Pts
              </span>
            </div>        
            <Progress 
              :model-value="Math.min(100, (deck.user_progress / (deck.type === 'WINS' ? deck.wins_required : deck.min_points_required)) * 100)" 
              class="h-2" 
            />
          </div>
          <Button class="w-full" 
            :disabled="deck.is_owned || (deck.type === 'COINS' && authStore.currentUser.coins_balance < deck.price) || ((deck.type === 'WINS' || deck.type === 'POINTS') && !deck.can_claim)"
            :variant="deck.is_owned ? 'secondary' : 'default'"
            @click="handlePurchase(deck)">       
            <span v-if="deck.is_owned">In Collection</span>
            <span v-else-if="deck.type === 'FREE'">Claim Free</span>
            <span v-else-if="deck.type === 'COINS'">Buy for {{ deck.price }} 💰</span>       
            <span v-else-if="deck.type === 'WINS'">
              {{ deck.can_claim ? 'Claim Reward!' : `Win ${deck.wins_required - deck.user_progress} more` }}
            </span>
            <span v-else-if="deck.type === 'POINTS'">
              {{ deck.can_claim ? 'Claim Master Deck!' : `Win a match with 100+ points` }}
            </span>
          </Button>
        </CardContent>
      </Card>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, inject } from 'vue'
import { useAPIStore } from '@/stores/api'
import { useAuthStore } from '@/stores/auth'
import { toast } from 'vue-sonner'
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Progress } from '@/components/ui/progress' // Ensure you have this shadcn component installed

const apiStore = useAPIStore()
const authStore = useAuthStore()
const serverBaseURL = inject('serverBaseURL')

const decks = ref([])
const isLoading = ref(true)

const loadShop = async () => {
  isLoading.value = true
  try {
    const response = await apiStore.getCardDecks()
    decks.value = response.data.filter(deck => deck.slug !== 'default')
  } catch (error) {
    toast.error('Failed to load shop items')
  } finally {
    isLoading.value = false
  }
}

const getDeckTypeLabel = (deck) => {
  if (deck.type === 'FREE') return 'Starter Pack'
  if (deck.type === 'COINS') return 'Premium Pack'
  if (deck.type === 'WINS') return 'Veteran Reward'
  if (deck.type === 'POINTS') return 'Mastery Reward' 
}

const handlePurchase = async (deck) => {
  try {
    await authStore.buyDeck(deck.id)
    toast.success(`Unlocked: ${deck.name}!`)
    await loadShop() // Refresh state
  } catch (error) {
    toast.error(error.response?.data?.message || 'Purchase failed')
  }
}

onMounted(loadShop)
</script>