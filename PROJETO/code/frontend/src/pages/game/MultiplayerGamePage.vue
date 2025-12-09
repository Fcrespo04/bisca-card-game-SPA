<template>
  <div class="min-h-screen bg-slate-900 p-2 md:p-4 flex flex-col">

    <div v-if="gameStore.gameStatus === 'ended' || gameStore.gameStatus === 'match_ended'"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
       <div class="bg-white p-6 rounded shadow-lg max-w-sm w-full text-center space-y-4">
           <h1 class="text-2xl font-bold text-black">
             {{ gameStore.gameStatus === 'match_ended' ? 'FIM DA PARTIDA' : 'FIM DA RODADA' }}
           </h1>

           <div class="border p-3 rounded bg-gray-100">
               <h3 class="text-sm font-bold text-gray-600 mb-1">RISCAS (MARKS)</h3>
               <div class="text-3xl font-bold text-black">
                   {{ gameStore.matchScore.player }} - {{ gameStore.matchScore.opponent }}
               </div>
           </div>

           <div class="text-sm">
               Pontos: <b>{{ gameStore.score.player }}</b> (Tu) vs <b>{{ gameStore.score.opponent }}</b> (Op.)
           </div>

           <div v-if="gameStore.gameStatus === 'match_ended'" class="font-bold text-lg">
                <span v-if="gameStore.matchScore.player >= 4" class="text-green-600">GANHASTE!</span>
                <span v-else class="text-red-600">PERDESTE.</span>
           </div>

           <div class="flex flex-col gap-2 pt-2">
               <template v-if="gameStore.gameStatus === 'ended'">
                   <button @click="handleNextRound" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700 font-bold">
                     Próxima Rodada
                   </button>
                   <button @click="handleLeave" class="w-full bg-gray-200 text-black py-2 rounded hover:bg-gray-300">
                     Sair
                   </button>
               </template>

               <template v-if="gameStore.gameStatus === 'match_ended'">
                   <button @click="handleLeave" class="w-full bg-gray-200 text-black py-2 rounded hover:bg-gray-300">
                     Voltar ao Lobby
                   </button>
               </template>
           </div>
       </div>
    </div>

    <BiscaBoard
      v-else
      :deckCount="gameStore.deckCount"
      :trumpCard="gameStore.trumpCard"
      :turn="gameStore.turn"
      :player="{ hand: gameStore.playerHand, score: gameStore.score.player }"
      :opponent="{ handCount: gameStore.opponentHandCount, score: gameStore.score.opponent }"
      :table="gameStore.table"
      :deck-id="userDeckId"
      :matchScore="gameStore.matchScore"
      @play-card="handlePlayCard"
    />
  </div>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useGameStore } from '@/stores/game'
import { useAuthStore } from '@/stores/auth'
import { useSocketStore } from '@/stores/socket'
import BiscaBoard from '@/components/game/BiscaBoard.vue'
import { toast } from 'vue-sonner'

const gameStore = useGameStore()
const authStore = useAuthStore()
const socketStore = useSocketStore()
const router = useRouter()

const userDeckId = computed(() => authStore.currentUser?.current_card_deck_id || 1)

const handlePlayCard = (cardOrIndex) => {
    // BiscaBoard pode emitir o objeto carta ou o index.
    // Como no SP usamos index, vamos ver o que o componente emite.
    // Assumindo que o componente emite o objeto carta para facilitar:

    // Se BiscaBoard emitir indice: const card = gameStore.playerHand[cardOrIndex]

    if (gameStore.turn !== 'player') {
        toast.warning("Ainda não é a tua vez!")
        return
    }

    // A carta é passada. Se for index, pega do array:
    let cardToPlay = cardOrIndex
    if (typeof cardOrIndex === 'number') {
        cardToPlay = gameStore.playerHand[cardOrIndex]
    }

    socketStore.emitPlayCard({
        gameId: gameStore.multiplayerGameId,
        card: cardToPlay
    })
}

const handleNextRound = () => {
    socketStore.emitNextRound(gameStore.multiplayerGameId)
}

const handleLeave = () => {
    socketStore.emitLeaveGame(gameStore.multiplayerGameId)
    router.push({ name: 'multiplayer-lobby' })
}

onMounted(() => {
    if (!gameStore.multiplayerGameId) {
        toast.error("Erro: Nenhum jogo ativo.")
        router.push({ name: 'multiplayer-lobby' })
    }
})
</script>
