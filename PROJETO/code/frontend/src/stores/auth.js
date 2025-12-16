import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { useAPIStore } from './api'
import { useSocketStore } from './socket'
import axios from 'axios' // <--- Necessário importar axios para definir headers

export const useAuthStore = defineStore('auth', () => {
  const apiStore = useAPIStore()
  const socketStore = useSocketStore()

  // --- 1. INICIALIZAÇÃO COM PERSISTÊNCIA ---
  // Tenta ler do SessionStorage. Se não existir, fica null.
  const token = ref(sessionStorage.getItem('token') || null)
  const currentUser = ref(JSON.parse(sessionStorage.getItem('user')) || null)

  // Se o token existir ao carregar a página (F5), define o header do Axios imediatamente
  if (token.value) {
    axios.defaults.headers.common['Authorization'] = 'Bearer ' + token.value
  }

  const isLoggedIn = computed(() => {
    return !!token.value // Verifica se o token existe
  })

  const currentUserID = computed(() => {
    return currentUser.value?.id
  })

  // --- FUNÇÕES AUXILIARES DE STORAGE ---
  const persistSession = (newToken, newUser) => {
    token.value = newToken
    currentUser.value = newUser
    sessionStorage.setItem('token', newToken)
    sessionStorage.setItem('user', JSON.stringify(newUser))
    axios.defaults.headers.common['Authorization'] = 'Bearer ' + newToken
  }

  const clearSession = () => {
    token.value = null
    currentUser.value = null
    sessionStorage.removeItem('token')
    sessionStorage.removeItem('user')
    delete axios.defaults.headers.common['Authorization']
  }

  // --- AÇÕES ---

  const getUser = async () => {
    const response = await apiStore.getAuthUser()
    const userData = response.data.data || response.data 
    
    currentUser.value = userData
    // Atualiza a storage para garantir que dados como moedas/decks estejam sincronizados
    sessionStorage.setItem('user', JSON.stringify(userData))
  }

  const login = async (credentials) => {
    const response = await apiStore.postLogin(credentials)
    const newToken = response.data.token
    
    // Define o token no header ANTES de pedir o user
    axios.defaults.headers.common['Authorization'] = 'Bearer ' + newToken
    
    await getUser() // Vai buscar os dados completos do user
    
    // Guarda tudo na sessão e liga o socket
    persistSession(newToken, currentUser.value)
    socketStore.emitJoin(currentUser.value)
    
    return currentUser.value
  }

  const logout = async () => {
    try {
      await apiStore.postLogout()
    } catch (e) {
      console.warn('API Logout failed (session might already be expired)', e)
    } finally {
      // Limpa sempre localmente, mesmo que a API falhe
      socketStore.emitLeave()
      clearSession()
    }
  }

  const register = async (credentials) => {
    const response = await apiStore.postRegister(credentials)
    
    const newToken = response.data.token
    const newUser = response.data.user
    
    // Guarda sessão e conecta socket
    persistSession(newToken, newUser)
    socketStore.emitJoin(newUser)
    
    return newUser
  }

  const deleteAccount = async (password) => {
    if (!currentUser.value) return

    await apiStore.deleteUser(currentUser.value, password)

    socketStore.emitLeave()
    clearSession() // Usa o helper para limpar tudo
  }

  const updateUser = async (user) => {
    const response = await apiStore.putUser(user)
    currentUser.value = response.data.data
    // Importante: atualizar a storage após editar perfil
    sessionStorage.setItem('user', JSON.stringify(currentUser.value))
    return currentUser.value
  }

    const updateUserPhoto = async (file) => {

    // upload

    const response = await apiStore.uploadProfilePhoto(file)

    const photoUrl = response.data.photo_url || response.data.photo_avatar_filename

   

    // patch User Record

    const patchResponse = await apiStore.patchUserPhoto(currentUser.value.id, photoUrl)

   

    // avatar changes in the UI

    currentUser.value = patchResponse.data.data

    return currentUser.value

  }

  const buyCoins = async (purchaseData) => {
    const response = await apiStore.postTransaction(purchaseData)
    // O getUser já atualiza a storage automaticamente
    await getUser() 
    return response.data
  }

  const fetchTransactions = async () => {
    const response = await apiStore.getTransactions()
    return response.data.data 
  }

  const buyDeck = async (deckId) => {
    const response = await apiStore.purchaseDeck(deckId)
    await getUser() 
    return response.data
  }

  const equipDeck = async (deckId) => {
    const response = await apiStore.equipDeck(deckId)
    await getUser() 
    return response.data
  }

  return {
    currentUser,
    currentUserID,
    isLoggedIn,
    login,
    logout,
    getUser,
    register,
    deleteAccount,
    updateUser,
    updateUserPhoto,
    buyCoins,
    fetchTransactions,
    buyDeck,
    equipDeck,
  }
})