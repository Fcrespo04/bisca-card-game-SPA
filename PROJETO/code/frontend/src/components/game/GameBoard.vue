<script setup>

import { ref } from 'vue'
import MemoryCard from './MemoryCard.vue';

//const cards = ref([1, 2, 3, 4, 5, 6, 7, 8])

const props = defineProps({
  cards: {
    type: Array,
    required: true
  }
})

const emits = defineEmits(['flipCard'])

const handleCardClick = (card) => {
    // lógica para tratar o clique numa carta
    console.log('Carta clicada:', card)
    emits('flipCard', card)
}

</script>

<template>
    <div class="m-auto pt-10 grid gap-10 p-4 max-w-lg grid-cols-4">
        <MemoryCard v-for="card in props.cards" 
        :card="card" 
        :key="card.id"
        @clicked="handleCardClick"/> <!-- função para tratar o evento de clique de SingleplayerGamePage.vue-->
    </div>
</template>