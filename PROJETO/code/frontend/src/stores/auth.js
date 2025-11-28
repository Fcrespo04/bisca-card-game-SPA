import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { useAPIStore } from './api'
import { useSocketStore } from './socket'

export const useAuthStore = defineStore('auth', () => {
  const apiStore = useAPIStore()
  const socketStore = useSocketStore()

  const currentUser = ref(undefined)

  const isLoggedIn = computed(() => {
    return currentUser.value !== undefined
  })

  const currentUserID = computed(() => {
    return currentUser.value?.id
  })

  const getUser = async () => {
    const response = await apiStore.getAuthUser()
    // Laravel Resources typically wrap the object in a 'data' property.
    // If your API returns it directly, use response.data.
    currentUser.value = response.data.data || response.data 
  }

  const login = async (credentials) => {
    await apiStore.postLogin(credentials)
    await getUser()
    socketStore.emitJoin(currentUser.value)
    return currentUser.value
  }

  const logout = async () => {
    try {
      await apiStore.postLogout()
    } catch (e) {
      console.warn('API Logout failed (session might already be expired)', e)
    }
    socketStore.emitLeave()
    currentUser.value = undefined
  }

  const register = async (credentials) => {
    const response = await apiStore.postRegister(credentials)
    
    // The API response now includes the user object and token
    currentUser.value = response.data.user
    
    // Connect to WebSocket immediately after registration
    socketStore.emitJoin(currentUser.value)
    
    return currentUser.value
  }

  const deleteAccount = async (password) => {
    if (!currentUser.value) return

    // Send delete request with password
    await apiStore.deleteUser(currentUser.value, password)

    //Perform local cleanup (similar to logout)
    socketStore.emitLeave()
    currentUser.value = undefined
    
    //Clear token from API store
    apiStore.token = undefined
  }

  const updateUser = async (user) => {
    const response = await apiStore.putUser(user)
    currentUser.value = response.data.data
    return currentUser.value
  }

  const updateUserPhoto = async (file) => {
    // 1. Upload (logic is in api.js)
    const response = await apiStore.uploadProfilePhoto(file)
    const photoUrl = response.data.photo_url || response.data.photo_avatar_filename
    
    // 2. Patch User Record (logic is in api.js)
    const patchResponse = await apiStore.patchUserPhoto(currentUser.value.id, photoUrl)
    
    // 3. Update Local State (So the avatar changes in the UI)
    currentUser.value = patchResponse.data.data
    return currentUser.value
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
  }
})
