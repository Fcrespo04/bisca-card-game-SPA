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
                     <Button
                        @click="startGame(3)"
                        variant="outline"
                        class="h-32 flex flex-col gap-2 border-2 hover:bg-green-50 hover:border-green-600 transition-all group"
                    >
                        <span class="text-3xl group-hover:scale-110 transition-transform">🃏</span>
                        <div class="flex flex-col items-center">
                            <span class="font-bold text-lg">Clássico (x3)</span>
                            <span class="text-xs text-muted-foreground">Mão de 3 cartas</span>
                        </div>
                     </Button>

                     <Button
                        @click="startGame(9)"
                        variant="outline"
                        class="h-32 flex flex-col gap-2 border-2 hover:bg-purple-50 hover:border-purple-600 transition-all group"
                    >
                        <span class="text-3xl group-hover:scale-110 transition-transform">🎴</span>
                        <div class="flex flex-col items-center">
                            <span class="font-bold text-lg">Bisca de 9</span>
                            <span class="text-xs text-muted-foreground">Mão de 9 cartas</span>
                        </div>
                     </Button>
                </CardContent>
            </Card>

            <Card class="w-full md:w-1/2 border-slate-200 shadow-lg bg-slate-50/50">
                <CardHeader>
                    <CardTitle class="text-2xl font-bold text-center flex items-center justify-center gap-2 text-slate-400">
                        <span>🌍</span> Multiplayer
                    </CardTitle>
                    <CardDescription class="text-center">
                        Desafia outros jogadores online
                    </CardDescription>
                </CardHeader>
                <CardContent class="flex items-center justify-center h-48">
                    <span class="bg-slate-200 text-slate-500 px-4 py-2 rounded-full font-medium text-sm">
                        Em breve...
                    </span>
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

const gameStore = useGameStore()
const router = useRouter()

const startGame = (numCards) => {
  // 1. Guarda a configuração na Store
  gameStore.cardsPerHand = numCards

  // 2. Redireciona para a página de jogo
  router.push({ name: 'singleplayer'})
}
</script>
