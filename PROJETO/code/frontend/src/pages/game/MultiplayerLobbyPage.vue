<template>
    <div class="container mx-auto p-4 max-w-4xl space-y-6">
        <h1 class="text-3xl font-bold text-slate-800 mb-6">Lobby Multiplayer</h1>

        <Card>
            <CardHeader><CardTitle class="text-lg">Criar Nova Sala</CardTitle></CardHeader>
            <CardContent class="space-y-4">
                <div class="grid grid-cols-2 gap-2">
                    <Button v-for="level in gameStore.difficulties" :key="level.value" size="sm"
                        @click="selectedDiff = level.value"
                        :variant="selectedDiff === level.value ? 'default' : 'outline'"
                        class="flex flex-col py-3 h-16">
                        <span class="font-semibold">{{ level.label }}</span>
                        <span class="text-xs opacity-70">{{ level.description }}</span>
                    </Button>
                </div>
                <Button @click="createGame" class="w-full">Criar Jogo</Button>
            </CardContent>
        </Card>

        <Card v-if="gameStore.myGames.length > 0" class="border-2 border-blue-200 bg-blue-50/50">
            <CardHeader><CardTitle class="text-lg text-blue-800">Aguardando Oponente...</CardTitle></CardHeader>
            <CardContent>
                <div class="bg-white p-4 rounded border flex justify-between items-center" v-for="game of gameStore.myGames" :key="game.id">
                    <div class="flex items-center gap-2">
                        <div class="animate-pulse text-2xl">⏳</div>
                        <span class="font-bold">Sala #{{ game.id }}</span>
                    </div>
                    <Button @click="cancelGame(game)" variant="destructive" size="sm">Cancelar</Button>
                </div>
            </CardContent>
        </Card>

        <Card>
            <CardHeader>
                <div class="flex justify-between items-center">
                    <CardTitle class="text-lg">Jogos Disponíveis</CardTitle>
                    <Button @click="refreshList" variant="ghost" size="sm">↻</Button>
                </div>
            </CardHeader>
            <CardContent>
                <div v-if="gameStore.availableGames.length === 0" class="text-center py-8 text-gray-500">
                    <p>Não há jogos disponíveis.</p>
                </div>
                <div v-else class="space-y-3">
                    <div v-for="game of gameStore.availableGames" :key="game.id"
                        class="flex justify-between items-center p-4 border rounded hover:bg-slate-50">
                        <div>
                            <p class="font-medium">Sala #{{ game.id }}</p>
                            <p class="text-xs text-gray-500">Criado por: {{ game.created_by_name || 'Desconhecido' }}</p>
                        </div>
                        <Button @click="joinGame(game)" size="sm">Entrar</Button>
                    </div>
                </div>
            </CardContent>
        </Card>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useGameStore } from '@/stores/game'
import { useSocketStore } from '@/stores/socket'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'

const gameStore = useGameStore()
const socketStore = useSocketStore()
const selectedDiff = ref('normal')

const createGame = () => socketStore.emitCreateGame({ type: selectedDiff.value })
const joinGame = (game) => socketStore.emitJoinGame(game.id)
const refreshList = () => socketStore.emitGetGames()
const cancelGame = (game) => { /* Implementar cancel */ }

onMounted(() => { socketStore.emitGetGames() })
</script>
