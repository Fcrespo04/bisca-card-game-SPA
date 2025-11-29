<script setup>
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { ref, onMounted, inject } from 'vue'

const socket = inject('socket')
const api = ref({})
const received = ref('')
const message = ref('Hello from Bisca Client')

socket.on('echo', (msg) => {
  received.value = msg
})

onMounted(async () => {
  try {
    const response = await fetch('http://localhost:8000/api/metadata', {
      method: 'GET',
      headers: {
        'Accept': 'application/json'
      }
    })
    api.value = await response.json()
  } catch (error) {
    console.error("API Offline")
    api.value = { version: "Offline", name: "API Unreachable" }
  }
})
</script>

<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto space-y-8">

      <div class="text-center space-y-2">
        <h1 class="text-4xl font-bold text-slate-900 tracking-tight">
          Bisca Game Platform
        </h1>
        <p class="text-lg text-slate-600 font-medium">
          DAD Project 25/26 - Group 6
        </p>
      </div>

      <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6">
        <h2 class="text-xl font-semibold text-slate-800 mb-4">About the Project</h2>
        <p class="text-slate-700 leading-relaxed mb-4">
          This platform allows users to play the traditional Portuguese card game <strong>Bisca</strong>. 
          It features a complete Single Page Application (SPA) with real-time multiplayer capabilities.
        </p>
        <ul class="list-disc list-inside text-slate-600 space-y-1 ml-4">
          <li><strong>Frontend:</strong> Vue.js (Composition API + Pinia)</li>
          <li><strong>Backend:</strong> Laravel API (Sanctum Auth + SQLite)</li>
          <li><strong>Real-time:</strong> Node.js + Socket.io</li>
        </ul>
      </div>

      <div class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden">
        <div class="bg-slate-50 px-6 py-4 border-b border-slate-200">
          <h3 class="text-lg font-medium text-slate-900">System Status</h3>
        </div>
        
        <div class="p-6 space-y-6">
          
          <div class="flex items-center justify-between p-4 bg-slate-50 rounded-lg border border-slate-100">
            <div>
              <p class="text-sm font-medium text-slate-600">API Connection</p>
              <p class="text-xs text-slate-500 mt-1">{{ api.name || 'Connecting...' }}</p>
            </div>
            <div class="flex items-center gap-2">
              <span class="text-xs font-mono bg-white px-2 py-1 rounded border border-slate-200">
                v{{ api.version || '---' }}
              </span>
              <span :class="['w-2 h-2 rounded-full', api.version ? 'bg-green-500' : 'bg-red-500']"></span>
            </div>
          </div>

          <div class="flex items-center justify-between p-4 bg-slate-50 rounded-lg border border-slate-100">
            <div>
              <p class="text-sm font-medium text-slate-600">WebSocket Server</p>
              <p class="text-xs text-slate-500 mt-1">Real-time communication status</p>
            </div>
            <span :class="[
              'inline-flex items-center gap-2 px-3 py-1 rounded-full text-sm font-medium',
              socket.connected ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
            ]">
              <span :class="['w-2 h-2 rounded-full', socket.connected ? 'bg-green-500 animate-pulse' : 'bg-red-500']"></span>
              {{ socket.connected ? 'Connected' : 'Disconnected' }}
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>