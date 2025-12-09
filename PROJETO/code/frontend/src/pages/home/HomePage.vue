<template>
    <div class="flex flex-col items-center justify-center min-h-[80vh] gap-8 p-4">

        <div class="text-center space-y-2">
            <h1 class="text-4xl md:text-6xl font-bold bg-gradient-to-r from-green-600 to-green-800 bg-clip-text text-transparent">
                Bisca Game
            </h1>
            <p class="text-slate-500 font-medium">Escolhe o teu desafio</p>
        </div>

        <div class="flex flex-col md:flex-row gap-6 w-full max-w-4xl justify-center items-stretch">

            <Card class="w-full md:w-1/2 border-slate-200 shadow-lg hover:shadow-xl transition-shadow">
                <CardHeader>
                    <CardTitle class="text-2xl font-bold text-center flex items-center justify-center gap-2">
                        <span>👤</span> Single Player
                    </CardTitle>
                    <CardDescription class="text-center">
                        Joga contra o Bot e treina as tuas habilidades
                    </CardDescription>
                </CardHeader>
                <CardContent class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                     <Button @click="startSP(3)" variant="outline" class="h-32 flex flex-col gap-2 border-2 hover:bg-green-50 hover:border-green-600 transition-all group">
                            <img :src="three_cards" class="w-13 h-13 object-contain" />
                            <div class="flex flex-col items-center">
                                <span class="font-bold text-lg">Clássico (x3)</span>
                                <span class="text-xs text-muted-foreground">Mão de 3 cartas</span>
                            </div>
                     </Button>

                     <Button @click="startSP(9)" variant="outline" class="h-32 flex flex-col gap-2 border-2 hover:bg-purple-50 hover:border-purple-600 transition-all group">
                        <img :src="nine_cards" class="w-13 h-13 object-contain" />
                        <div class="flex flex-col items-center">
                            <span class="font-bold text-lg">Bisca de 9</span>
                            <span class="text-xs text-muted-foreground">Mão de 9 cartas</span>
                        </div>
                     </Button>
                </CardContent>
            </Card>

            <Card class="w-full md:w-1/2 border-slate-200 shadow-lg bg-blue-50/20">
                <CardHeader>
                    <CardTitle class="text-2xl font-bold text-center flex items-center justify-center gap-2 text-blue-600">
                        <span>🌍</span> Multiplayer
                    </CardTitle>
                    <CardDescription class="text-center">
                        Desafia outros jogadores online
                    </CardDescription>
                </CardHeader>
                <CardContent class="flex items-center justify-center h-48">
                    <Button @click="goToLobby" class="w-full h-24 text-lg bg-blue-600 hover:bg-blue-700 text-white shadow-md">
                        Entrar no Lobby
                    </Button>
                </CardContent>
            </Card>
        </div>
    </div>
</template>

<script setup>
import { useRouter } from 'vue-router'
import { useGameStore } from '@/stores/game'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { inject } from 'vue'

const gameStore = useGameStore()
const router = useRouter()
const serverBaseURL = inject('serverBaseURL') || 'http://localhost:8000'
const three_cards = `${serverBaseURL}/storage/icon_card/3cartas.png`
const nine_cards = `${serverBaseURL}/storage/icon_card/9cartas.png`

const startSP = (numCards) => {
  gameStore.cardsPerHand = numCards
  router.push({ name: 'singleplayer'})
}

const goToLobby = () => {
  router.push({ name: 'multiplayer-lobby' })
}
</script>
