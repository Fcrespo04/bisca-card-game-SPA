import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import router from './router'
import {io} from 'socket.io-client'

//import AboutPage from '@/pages/about/AboutPage.vue'
import GameBoard from './components/game/GameBoard.vue'
//const app = createApp(GameBoard)
const app = createApp(App)
app.use(createPinia())
app.use(router)

const API_BASE_URL = 'http://localhost:8000/api' // alterar o endereço quando se for publicar o projeto
app.provide('apiBaseURL', API_BASE_URL)

const SERVER_BASE_URL = 'http://localhost:8000' // Base URL without /api
app.provide('serverBaseURL', SERVER_BASE_URL)

const socket = io('http://localhost:3000') // alterar o endereço quando se for publicar o projeto
app.provide('socket', socket) // ponteiro para socket, depois faz-se o inject nas componentes que precisarem

app.mount('#app')

