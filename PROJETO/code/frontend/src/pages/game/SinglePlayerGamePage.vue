<template>
  <div class="min-h-screen bg-slate-900 p-2 md:p-4 flex flex-col">

    <!-- MODAL SIMPLES E DIRETO -->
    <div v-if="gameStore.gameStatus === 'ended' || gameStore.gameStatus === 'match_ended'"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">

       <div class="bg-white p-6 rounded shadow-lg max-w-sm w-full text-center space-y-4">

           <!-- TÍTULO -->
           <h1 class="text-2xl font-bold text-black">
             {{ gameStore.gameStatus === 'match_ended' ? 'MATCH OVER' : 'ROUND OVER' }}
           </h1>

           <!-- PLACAR SIMPLES -->
           <div class="border p-3 rounded bg-gray-100">
               <h3 class="text-sm font-bold text-gray-600 mb-1">MARKS (RISCAS)</h3>
               <div class="text-3xl font-bold text-black">
                   {{ gameStore.matchScore.player }} - {{ gameStore.matchScore.opponent }}
               </div>
               <p class="text-xs text-gray-500 mt-1">First to 4 wins.</p>
           </div>

           <!-- PONTOS DA RODADA -->
           <div class="text-sm">
               Round Points: <b>{{ gameStore.score.player }}</b> (You) vs <b>{{ gameStore.score.opponent }}</b> (Bot)
           </div>

           <!-- MENSAGEM FINAL -->
           <div v-if="gameStore.gameStatus === 'match_ended'" class="font-bold text-lg">
                <span v-if="gameStore.matchScore.player >= 4" class="text-green-600">YOU WON!</span>
                <span v-else class="text-red-600">YOU LOST.</span>
           </div>

           <!-- BOTÕES NORMAIS -->
           <div class="flex flex-col gap-2 pt-2">

               <template v-if="gameStore.gameStatus === 'ended'">
                   <button
                     @click="gameStore.nextRound"
                     class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700 font-bold"
                   >
                     Next Round
                   </button>

                   <button
                     @click="router.push('/')"
                     class="w-full bg-gray-200 text-black py-2 rounded hover:bg-gray-300"
                   >
                     Give Up
                   </button>
               </template>

               <template v-if="gameStore.gameStatus === 'match_ended'">
                   <button
                     @click="gameStore.initGame"
                     class="w-full bg-green-600 text-white py-2 rounded hover:bg-green-700 font-bold"
                   >
                     Play Again
                   </button>

                   <button
                     @click="router.push('/')"
                     class="w-full bg-gray-200 text-black py-2 rounded hover:bg-gray-300"
                   >
                     Exit
                   </button>
               </template>

           </div>
       </div>
    </div>

    <!-- TABULEIRO -->
    <BiscaBoard
      v-else
      :deckCount="gameStore.deck.length"
      :trumpCard="gameStore.trumpCard"
      :turn="gameStore.turn"
      :player="{ hand: gameStore.playerHand, score: gameStore.score.player }"
      :opponent="{ handCount: gameStore.botHand.length, score: gameStore.score.opponent }"
      :table="gameStore.table"
      :deck-id="userDeckId"
      :matchScore="gameStore.matchScore"
      @play-card="handlePlayCard"
    />
  </div>
</template>

<script setup>
import { onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useGameStore } from '@/stores/game'
import { useAuthStore } from '@/stores/auth'
import BiscaBoard from '@/components/game/BiscaBoard.vue'

const gameStore = useGameStore()
const authStore = useAuthStore()
const router = useRouter()

const userDeckId = computed(() => {
    return authStore.currentUser?.current_card_deck_id || 1
})

const handlePlayCard = (index) => {
  gameStore.playCard(index)
}

onMounted(async () => {
    gameStore.initGame()

    if (!authStore.currentUser) {
        try {
            await authStore.getUser()
        } catch (e) {
            console.error('Erro ao carregar utilizador:', e)
        }
    }
})
</script>
