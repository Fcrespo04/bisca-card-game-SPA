import { defineStore } from 'pinia'
import { toast } from 'vue-sonner'
import axios from 'axios'
import { inject, ref } from 'vue'

export const useAPIStore = defineStore('api', () => {
  const API_BASE_URL = inject('apiBaseURL')

  const token = ref()

  // AUTH
  const postLogin = async (credentials) => {
    const response = await axios.post(`${API_BASE_URL}/login`, credentials)
    token.value = response.data.token
    axios.defaults.headers.common['Authorization'] = `Bearer ${token.value}`
  }
  const postLogout = async () => {
    await axios.post(`${API_BASE_URL}/logout`)
    token.value = undefined
    delete axios.defaults.headers.common['Authorization']
  }

  const postRegister = async (credentials) => {
    const response = await axios.post(`${API_BASE_URL}/register`, credentials)
    token.value = response.data.token
    axios.defaults.headers.common['Authorization'] = `Bearer ${token.value}`
    return response // Return so auth store can use the user data
  }

  // Users
  const getAuthUser = () => {
    return axios.get(`${API_BASE_URL}/users/me`)
  }

  const putUser = (user) => {
    return axios.put(`${API_BASE_URL}/users/${user.id}`, user)
  }

  const patchUserPhoto = (id, filename) => {
    return axios.patch(`${API_BASE_URL}/users/${id}/photo-url`, {
      photo_avatar_filename: filename
    })
  }

  const deleteUser = (user, password) => {
    return axios.delete(`${API_BASE_URL}/users/${user.id}`, {
      data: { password: password }
    })
  }

  // GAMES
  const postGame = (game) => {
    return toast.promise(axios.post(`${API_BASE_URL}/games`, game), {
      loading: 'Sending data to API...',
      success: () => {
        return `[API] Game saved successfully`
      },
      error: (data) => `[API] Error saving game - ${data?.response?.data?.message}`,
    })
  }

  const getGames = () => {
    return axios.get(`${API_BASE_URL}/games`)
  }

  const postTransaction = (data) => {
    return axios.post(`${API_BASE_URL}/transactions`, data)
  }

  const getTransactions = () => {
    return axios.get(`${API_BASE_URL}/transactions`)
  }


  // Files

  const uploadProfilePhoto = async (file) => {
    const formData = new FormData()
    formData.append('photo_avatar_filename', file)

    const uploadPromise = axios.post(`${API_BASE_URL}/files/userphoto`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })

    toast.promise(uploadPromise, {
      loading: 'Uploading profile photo...',
      success: () => `Profile photo uploaded successfully`,
      error: (data) => `Error uploading photo - ${data?.response?.data?.message}`,
    })

    return uploadPromise
  }

  const uploadCardFaces = async (files) => {
    const formData = new FormData()
    for (let file of files) {
      formData.append('cardfaces[]', file)
    }

    const uploadPromise = axios.post(`${API_BASE_URL}/files/cardfaces`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })

    toast.promise(uploadPromise, {
      loading: 'Uploading Card Faces...',
      success: () => `Card Faces uploaded successfully`,
      error: (data) => `Error uploading Card Faces - ${data?.response?.data?.message}`,
    })

    return uploadPromise
  }

  const getCardDecks = () => axios.get(`${API_BASE_URL}/card-decks`)

  const purchaseDeck = (deckId) => axios.post(`${API_BASE_URL}/card-decks/${deckId}/purchase`)

  const equipDeck = (deckId) => axios.post(`${API_BASE_URL}/card-decks/${deckId}/equip`)

  const getHistory = (page = 1, search = '') => {
    return axios.get(`${API_BASE_URL}/history?page=${page}&search=${search}`)
  }

  // G4: Leaderboard Global (Público)
  const getGlobalLeaderboard = () => {
    return axios.get(`${API_BASE_URL}/leaderboard/global`)
  }

  // G4: Estatísticas Pessoais (Privado)
  const getPersonalStats = () => {
    return axios.get(`${API_BASE_URL}/statistics/personal`)
  }

  const getUsersList = (page = 1) => {
    return axios.get(`${API_BASE_URL}/users/list?page=${page}`)
  }

  const getPlayerStats = (userId) => {
    // Rota: GET /api/admin/stats/123
    return axios.get(`${API_BASE_URL}/admin/stats/${userId}`);
  }

  const toggleUserBlock = (userId) => {
    return axios.patch(`${API_BASE_URL}/admin/users/${userId}/block`);
  };

  const removeUserAccount = (userId) => {
    return axios.delete(`${API_BASE_URL}/admin/users/${userId}`);
  };

  const createNewAdmin = (formData) => {
    return axios.post(`${API_BASE_URL}/admin/create-admin`, formData);
  };


  return {
    postLogin,
    postLogout,
    postRegister,
    getAuthUser,
    putUser,
    patchUserPhoto,
    deleteUser,
    postGame,
    getGames,
    uploadProfilePhoto,
    uploadCardFaces,
    postTransaction,
    getTransactions,
    getCardDecks,
    purchaseDeck,
    equipDeck,
    getHistory,
    getGlobalLeaderboard,
    getPersonalStats,
    getUsersList,
    getPlayerStats,
    toggleUserBlock,
    removeUserAccount,
    createNewAdmin,
  }
})
