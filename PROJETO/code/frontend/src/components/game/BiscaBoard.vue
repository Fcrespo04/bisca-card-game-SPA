<template>
  <div class="flex-grow flex flex-col justify-between w-full h-full relative p-2 select-none bg-green-900/90 rounded-xl">

    <!-- OPPONENT -->
    <div class="flex justify-center pt-2 relative z-10">
      <div class="flex flex-col items-center gap-2">
        <div class="relative">
             <div class="w-14 h-14 rounded-full bg-slate-800 border-2 border-white flex items-center justify-center text-white font-bold">OP</div>
        </div>

        <!-- OPPONENT SCORE AND MARKS -->
        <div class="flex flex-col items-center">
            <span class="text-white font-bold bg-black/50 px-3 py-0.5 rounded-full text-xs mb-1">
              Opponent ({{ opponent.score }} pts)
            </span>
            <!-- MARKS VISUALIZER -->
            <div class="flex gap-1.5" title="Match Marks">
                <div v-for="n in 4" :key="n"
                     class="w-3 h-3 rounded-full border border-white/40 shadow-sm transition-colors duration-300"
                     :class="n <= matchScore.opponent ? 'bg-yellow-400 shadow-yellow-400/50' : 'bg-black/30'">
                </div>
            </div>
        </div>

        <div class="flex -space-x-4 h-24 mt-1">
          <PlayingCard
            v-for="n in opponent.handCount"
            :key="n"
            :hidden="true"
            :deck-id="deckId"
            class="w-16 h-24 shadow-lg"
          />
        </div>
      </div>
    </div>

    <!-- CENTER TABLE -->
    <div class="flex-1 w-full flex items-center justify-center relative my-4">
      <div class="absolute left-4 md:left-10 flex items-center">
        <div class="relative w-20 h-28 md:w-24 md:h-32">

          <!-- TRUMP CARD: Only show if there are cards in the deck -->
          <div v-if="trumpCard && deckCount > 0" class="absolute w-full h-full transform rotate-90 translate-x-6">
             <PlayingCard :card="trumpCard" :deck-id="deckId" class="w-full h-full shadow-md" />
          </div>

          <!-- DECK PILE: Only show if > 1 card remains (so it doesn't cover the trump when it's the last one) -->
          <div v-if="deckCount > 1" class="absolute inset-0 z-10">
             <PlayingCard :hidden="true" :deck-id="deckId" class="w-full h-full shadow-2xl" />
             <span class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 bg-black/60 text-white rounded-full px-2">
               {{ deckCount }}
             </span>
          </div>

        </div>
      </div>

      <div class="flex gap-8 items-center justify-center">
        <!-- Opponent Card on Table -->
        <div class="relative w-24 h-36 md:w-28 md:h-40 flex items-center justify-center">
          <PlayingCard v-if="table.opponent" :card="table.opponent" :deck-id="deckId" class="w-full h-full shadow-xl" />
          <div v-else class="w-full h-full border-2 border-dashed border-white/20 rounded-lg"></div>
        </div>

        <!-- Player Card on Table -->
        <div class="relative w-24 h-36 md:w-28 md:h-40 flex items-center justify-center">
          <PlayingCard v-if="table.player" :card="table.player" :deck-id="deckId" class="w-full h-full shadow-xl" />
          <div v-else class="w-full h-full border-2 border-dashed border-white/20 rounded-lg"></div>
        </div>
      </div>

      <div class="bg-black/60 px-5 py-2 rounded-full mt-2 text-white absolute right-4 md:right-10">
        Your Score: <span class="text-green-400 font-bold text-xl">{{ player.score }}</span>
      </div>
    </div>

    <!-- PLAYER -->
    <div class="w-full flex flex-col items-center mb-4">
      <div class="h-8 mb-2">
        <span v-if="turn === 'player'" class="text-green-400 font-bold animate-pulse bg-black/60 px-4 py-1 rounded-full uppercase">
          YOUR TURN
        </span>
      </div>
      <!-- MARKS VISUALIZER -->
          <div class="flex gap-1.5 bg-black/40 px-3 py-1 rounded-full" title="Your Marks">
                <div v-for="n in 4" :key="n"
                     class="w-3 h-3 rounded-full border border-white/40 shadow-sm transition-colors duration-300"
                     :class="n <= matchScore.player ? 'bg-yellow-400 shadow-yellow-400/50' : 'bg-black/30'">
                </div>
      </div>

      <div class="flex -space-x-6 hover:space-x-1 transition-all duration-300 px-4 py-2">
        <PlayingCard
          v-for="(card, index) in player.hand"
          :key="index"
          :card="card"
          :deck-id="deckId"
          :interactive="turn === 'player'"
          @click="playCard(index)"
          class="w-24 h-36 md:w-32 md:h-48"
        />
      </div>


      <!-- PLAYER SCORE AND MARKS -->
      <div class="flex flex-col items-center mt-2 gap-2">


      </div>
    </div>

  </div>
</template>

<script setup>
import PlayingCard from './PlayingCard.vue'

const props = defineProps({
  deckCount: Number,
  trumpCard: Object,
  turn: String,
  player: Object,
  opponent: Object,
  table: Object,
  deckId: { type: [Number, String], default: 1 },
  matchScore: { type: Object, default: () => ({ player: 0, opponent: 0 }) }
})

const emit = defineEmits(['play-card'])

const playCard = (index) => {
  if (props.turn === 'player') {
    emit('play-card', index)
  }
}
</script>
