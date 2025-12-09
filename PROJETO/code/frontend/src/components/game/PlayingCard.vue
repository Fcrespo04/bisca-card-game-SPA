<template>
  <div
    class="relative select-none transition-transform duration-200"
    :class="{
      'cursor-pointer hover:-translate-y-6 hover:scale-105 z-10 hover:z-20': interactive,
      'cursor-default': !interactive
    }"
    @click="handleClick"
  >
    <img
      v-if="!hidden && card"
      :src="imageSrc"
      :alt="`${card.rank} de ${card.suit}`"
      class="w-full h-full object-contain rounded-lg bg-white shadow-md border border-gray-200"
      :class="{ 'shadow-xl shadow-black/30': interactive }"
      @error="handleImageError"
    />

    <img
      v-else
      :src="backImageSrc"
      alt="Verso da carta"
      class="w-full h-full object-cover rounded-lg shadow-md border border-gray-200"
    />

    <div v-if="!hidden && card?.points > 0"
         class="absolute -top-2 -right-2 bg-yellow-500 text-black text-[10px] font-bold w-5 h-5 flex items-center justify-center rounded-full shadow-sm border border-white">
      {{ card.points }}
    </div>
  </div>
</template>

<script setup>
import { computed, inject } from 'vue'

const props = defineProps({
  card: Object,
  hidden: Boolean,
  interactive: Boolean,
  // Recebe o ID vindo da user.current_card_deck_id
  deckId: { type: [Number, String], default: 1 }
})

const emit = defineEmits(['click'])
const serverBaseURL = inject('serverBaseURL') || 'http://localhost:8000'

// --- MAPA DE DECKS ---
// Baseado na tua base de dados: ID => Nome da Pasta
const deckMap = {
  1: 'Standard',
  2: 'Rainbow',
  3: 'Carnival',
  4: 'Ancient',
  5: 'Gold',
  6: 'Platinum',
  7: 'Master'
}

const currentDeckFolder = computed(() => {
  // Se o ID não existir no mapa, usa 'Standard' como fallback
  return deckMap[props.deckId] || 'Standard'
})

const handleClick = () => {
  if (props.interactive) emit('click')
}

// Lógica para transformar RANK/SUIT em nome de ficheiro
const mapRankToAssetCode = (suit, rank) => {
    let num;
    switch(rank) {
        case 'ACE': num = 1; break;
        case '2': num = 2; break;
        case '3': num = 3; break;
        case '4': num = 4; break;
        case '5': num = 5; break;
        case '6': num = 6; break;
        case '7': num = 7; break;
        case 'JACK': num = 11; break;
        case 'QUEEN': num = 12; break;
        case 'KING': num = 13; break;
        default: num = rank;
    }

    // Mapeamento dos naipes para o prefixo do ficheiro
    // Verifica se os teus ficheiros (c1.jpg, etc) são iguais em todas as pastas.
    // Se no deck 'Rainbow' o Ás de Copas for 'h1.jpg' em vez de 'c1.jpg',
    // terás de ajustar isto. Assumi que a nomenclatura é padrão.
    const suitPrefix = {
        'HEARTS': 'c',   // Copas
        'SPADES': 'e',   // Espadas
        'DIAMONDS': 'o', // Ouros
        'CLUBS': 'p'     // Paus
    }[suit] || 'c';

    return `${suitPrefix}${num}`;
}

const imageSrc = computed(() => {
  if (!props.card) return ''
  const code = mapRankToAssetCode(props.card.suit, props.card.rank)

  // Path dinamico --> usa currentDeckFolder para escolher
  return `${serverBaseURL}/storage/card_decks/${currentDeckFolder.value}/${code}.jpg`
})

// Computed para a Imagem do Verso (semFace.jpg na DB)
const backImageSrc = computed(() => {
  return `${serverBaseURL}/storage/card_decks/${currentDeckFolder.value}/semFace.jpg`
})

// Fallback visual caso a imagem falhe a carregar
const handleImageError = (e) => {
  e.target.src = `${serverBaseURL}/storage/card_decks/not_found.png`
}
</script>
