<template>
  <div class="max-w-4xl mx-auto p-6 space-y-8">
    <h1 class="text-3xl font-bold">My Card Collection</h1>

    <div v-if="isLoading" class="text-center">Loading...</div>

    <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
      <Card v-for="deck in myDecks" :key="deck.id"
            :class="{'border-blue-500 ring-2 ring-blue-100': deck.is_active}">

        <CardHeader>
            <div class="flex justify-between items-start">
                <CardTitle>{{ deck.name }}</CardTitle>
                <span v-if="deck.is_active" class="bg-blue-100 text-blue-700 text-xs px-2 py-1 rounded-full font-bold">ACTIVE</span>
            </div>
        </CardHeader>

        <CardContent class="space-y-4">
            <div class="aspect-video bg-slate-100 rounded-md overflow-hidden flex items-center justify-center">
                <img :src="`${serverBaseURL}/storage/card_decks/${deck.semFace}`"
                     class="object-contain w-full h-full p-2">
            </div>

            <Button class="w-full"
                :variant="deck.is_active ? 'outline' : 'default'"
                :disabled="deck.is_active"
                @click="handleEquip(deck)">
                {{ deck.is_active ? 'Equipped' : 'Equip Deck' }}
            </Button>
        </CardContent>
      </Card>

      <RouterLink to="/card-packs" class="block">
        <Card class="h-full border-dashed border-2 border-gray-300 flex items-center justify-center hover:bg-gray-50 cursor-pointer">
            <CardContent class="text-center py-12">
                <span class="text-4xl mb-2 block">➕</span>
                <span class="text-gray-600 font-medium">Get More Packs</span>
            </CardContent>
        </Card>
      </RouterLink>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, inject } from 'vue'
import { useAPIStore } from '@/stores/api'
import { useAuthStore } from '@/stores/auth'
import { toast } from 'vue-sonner'
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card'
import { Button } from '@/components/ui/button'

const apiStore = useAPIStore()
const authStore = useAuthStore()
const serverBaseURL = inject('serverBaseURL')

const decks = ref([])
const isLoading = ref(true)

const myDecks = computed(() => decks.value.filter(d => d.is_owned))

const loadCollection = async () => {
  isLoading.value = true
  try {
    const response = await apiStore.getCardDecks()
    decks.value = response.data
  } finally {
    isLoading.value = false
  }
}

const handleEquip = async (deck) => {

    try {
        await authStore.equipDeck(deck.id)
    } catch (e) {
        console.warn("Erro ao salvar na DB, mas vamos salvar localmente.")
    }

    toast.success(`${deck.name} Equipado!`)

    decks.value.forEach(d => d.is_active = d.id === deck.id)

}

onMounted(loadCollection)
</script>
