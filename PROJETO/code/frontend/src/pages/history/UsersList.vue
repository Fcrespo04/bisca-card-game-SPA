<template>
    <div v-if="authStore.currentUser?.type === 'A'" class="max-w-6xl mx-auto p-6 space-y-12">

        <div class="text-center space-y-2">
            <h1 class="text-4xl font-bold text-slate-800">👥 User List</h1>
            <p class="text-slate-600 text-lg">Consult all users registered on the platform.</p>
        </div>

        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">

            <div class="flex gap-2 p-1 bg-slate-100 rounded-lg shadow-inner text-sm flex-wrap">
                <Button size="sm" :variant="currentFilter === 'all' ? 'default' : 'ghost'" @click="setFilter('all')">
                    🌐 All ({{ pagination.total || 0 }})
                </Button>

                <Button size="sm" :variant="currentFilter === 'active' ? 'default' : 'ghost'"
                    @click="setFilter('active')">
                    ✅ Active
                </Button>

                <Button size="sm" :variant="currentFilter === 'blocked' ? 'default' : 'ghost'"
                    @click="setFilter('blocked')">
                    🛑 Blocked
                </Button>

                <Button size="sm" :variant="currentFilter === 'deleted' ? 'default' : 'ghost'"
                    @click="setFilter('deleted')">
                    🗑️ Deactivated
                </Button>
            </div>

            <div class="relative w-full md:w-96">
                <input 
                    v-model="searchQuery" 
                    @keyup.enter="loadUsers(1)"
                    type="text" 
                    placeholder="Search by name, email or nickname..." 
                    class="w-full pl-10 pr-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none"
                />
                <button @click="loadUsers(1)"
                    class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 hover:text-blue-600">
                    🔍
                </button>
            </div>

            <Button variant="default" @click="showCreateAdminModal = true">
                ➕ Add New User
            </Button>

            <div v-if="showCreateAdminModal"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
                <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-6 space-y-4">

                    <div>
                        <h2 class="text-xl font-bold text-slate-800">New User</h2>
                        <p class="text-sm text-slate-500">Fill in the details to register a new admin or player.</p>
                    </div>

                    <div class="grid gap-4 py-2">
                        <div class="space-y-1">
                            <label class="text-sm font-medium">Full Name</label>
                            <input v-model="newUser.name" type="text" placeholder="e.g.: John Doe"
                                class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-blue-500 outline-none" />
                        </div>

                        <div class="space-y-1">
                            <label class="text-sm font-medium">Nickname</label>
                            <input v-model="newUser.nickname" type="text" placeholder="e.g.: johndoe"
                                class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-blue-500 outline-none" />
                        </div>

                        <div class="space-y-1">
                            <label class="text-sm font-medium">Email</label>
                            <input v-model="newUser.email" type="email" placeholder="email@example.com"
                                class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-blue-500 outline-none" />
                        </div>

                        <div class="space-y-1">
                            <label class="text-sm font-medium">Password</label>
                            <input v-model="newUser.password" type="password"
                                class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-blue-500 outline-none" />
                        </div>

                        <div class="space-y-1">
                            <label class="text-sm font-medium">User Type</label>
                            <select v-model="newUser.type"
                                class="w-full px-3 py-2 border rounded-md bg-white focus:ring-2 focus:ring-blue-500 outline-none">
                                <option value="P">Player</option>
                                <option value="A">Administrator</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t">
                        <button @click="showCreateAdminModal = false"
                            class="px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100 rounded-md transition-colors">
                            Cancel
                        </button>
                        <button @click="handleCreateUser"
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-md shadow-sm transition-colors">
                            💾 Save User
                        </button>
                    </div>
                </div>
            </div>

        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">

            <Card class="h-full border-t-4 border-t-yellow-500 shadow-md md:col-span-3">
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <span>👥</span> Users ({{ pagination.total || 0 }} total)
                    </CardTitle>
                </CardHeader>

                <CardContent class="p-0">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                            <tr>
                                <th class="px-4 py-3 w-12 text-center">ID</th>
                                <th class="px-4 py-3">Nickname</th>
                                <th class="px-4 py-3">Full Name</th>
                                <th class="px-4 py-3 text-right">Join Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-if="isLoading">
                                <td colspan="4" class="px-4 py-8 text-center text-indigo-500 italic">
                                    Loading users...
                                </td>
                            </tr>
                            <tr v-else-if="users.length === 0">
                                <td colspan="4" class="px-4 py-8 text-center text-gray-400 italic">
                                    No records found or no users match the filter "{{ currentFilter }}".
                                </td>
                            </tr>

                            <template v-else v-for="user in users" :key="user.id">

                                <tr @click="toggleDetails(user.id)"
                                    class="hover:bg-slate-50 transition-colors cursor-pointer group"
                                    :class="{ 'bg-slate-100/70': isExpanded(user.id) }">

                                    <td class="px-4 py-3 text-center font-bold text-slate-600">{{ user.id }}</td>

                                    <td class="px-4 py-3 flex items-center gap-3">
                                        <Avatar class="h-8 w-8 border border-slate-200">
                                            <AvatarImage v-if="user.photo_avatar_filename"
                                                :src="`${serverBaseURL}/storage/photos_avatars/${user.photo_avatar_filename}`" />
                                            <AvatarImage v-else
                                                :src="`${serverBaseURL}/storage/photos_avatars/anonymous.png`" />
                                        </Avatar>
                                        <span class="font-medium text-slate-800">{{ user.nickname }}</span>
                                    </td>

                                    <td class="px-4 py-3 text-slate-700">{{ user.name }}</td>

                                    <td class="px-4 py-3 text-right text-sm text-slate-500">
                                        {{ new Date(user.created_at).toLocaleDateString() }}
                                    </td>
                                </tr>

                                <tr v-if="isExpanded(user.id)" class="bg-slate-50/80 shadow-inner">
                                    <td colspan="4" class="px-6 py-4">

                                        <div class="space-y-2 border-r border-slate-200 pr-4">
                                            <h4 class="font-bold text-slate-700 uppercase text-xs mb-3">Performance and Achievements</h4>

                                            <div v-if="user.statsLoading"
                                                class="p-3 text-center text-sm text-indigo-500 rounded border border-indigo-100 bg-white">
                                                Loading...
                                            </div>

                                            <div v-else-if="user.detailedStats">

                                                <div class="grid grid-cols-5 gap-2">

                                                    <div class="p-2 rounded-lg text-center shadow-sm"
                                                        :class="user.detailedStats.matches_won > 0 ? 'bg-green-50 border border-green-200' : 'bg-gray-50 border border-gray-200'">
                                                        <div class="text-xl font-extrabold text-green-700">
                                                            {{ user.detailedStats.matches_won }}
                                                        </div>
                                                        <div class="text-xs text-gray-600 mt-1">Matches Won</div>
                                                    </div>
                                                    <div class="p-2 rounded-lg text-center shadow-sm"
                                                        :class="user.detailedStats.games_won > 0 ? 'bg-green-50 border border-green-200' : 'bg-gray-50 border border-gray-200'">
                                                        <div class="text-xl font-extrabold text-green-700">
                                                            {{ user.detailedStats.games_won }}
                                                        </div>
                                                        <div class="text-xs text-gray-600 mt-1">Games Won</div>
                                                    </div>
                                                    <div
                                                        class="p-2 rounded-lg text-center bg-blue-50 border border-blue-200 shadow-sm">
                                                        <div class="text-xl font-extrabold text-blue-700">
                                                            {{ user.detailedStats.total_score }}
                                                        </div>
                                                        <div class="text-xs text-gray-600 mt-1">Total Score</div>
                                                    </div>
                                                    <div
                                                        class="p-2 rounded-lg text-center bg-purple-50 border border-purple-200 shadow-sm">
                                                        <div
                                                            class="text-xl font-extrabold text-purple-600 flex items-center justify-center">
                                                            {{ user.detailedStats.capotes || 0 }} ♦️
                                                        </div>
                                                        <div class="text-xs text-gray-600 mt-1">Capotes</div>
                                                    </div>
                                                    <div
                                                        class="p-2 rounded-lg text-center bg-yellow-50 border border-yellow-200 shadow-sm">
                                                        <div
                                                            class="text-xl font-extrabold text-yellow-700 flex items-center justify-center">
                                                            {{ user.detailedStats.bandeiras || 0 }} 🚩
                                                        </div>
                                                        <div class="text-xs text-gray-600 mt-1">Flags</div>
                                                    </div>

                                                </div>

                                                <div class="my-4 border-t border-slate-200 w-full md:col-span-5"></div>

                                                <div class="grid grid-cols-2 md:grid-cols-5 gap-3 text-sm">

                                                    <div
                                                        class="p-2 bg-gray-50 border border-gray-200 rounded-lg shadow-sm">
                                                        <div class="text-xs text-gray-600 uppercase">Matches Played</div>
                                                        <div class="font-medium text-slate-800">{{ user.detailedStats.matches_played }}</div>
                                                    </div>

                                                    <div
                                                        class="p-2 bg-gray-50 border border-gray-200 rounded-lg shadow-sm">
                                                        <div class="text-xs text-gray-600 uppercase">Games Played</div>
                                                        <div class="font-medium text-slate-800">{{ user.detailedStats.games_played }}</div>
                                                    </div>

                                                    <div
                                                        class="p-2 bg-gray-50 border border-gray-200 rounded-lg shadow-sm">
                                                        <div class="text-xs text-gray-600 uppercase">Email</div>
                                                        <div class="font-medium text-slate-800 truncate">{{ user.email }}</div>
                                                    </div>

                                                    <div
                                                        class="p-2 bg-blue-50 border border-blue-200 rounded-lg shadow-sm">
                                                        <div class="text-xs text-gray-600 uppercase">Coins Balance</div>
                                                        <div class="font-bold text-blue-700">{{ user.coins_balance }}</div>
                                                    </div>

                                                    <div class="p-2 rounded-lg shadow-sm"
                                                        :class="user.deleted_at
                                                            ? 'bg-orange-50 border border-orange-200'
                                                            : user.blocked ? 'bg-red-50 border border-red-200' : 'bg-green-50 border border-green-200'">

                                                        <div class="text-xs text-gray-600 uppercase">Status</div>

                                                        <div class="font-bold" :class="user.deleted_at
                                                            ? 'text-orange-700'
                                                            : user.blocked ? 'text-red-700' : 'text-green-700'">

                                                            {{ user.deleted_at ? 'DEACTIVATED' : user.blocked ?
                                                                'BLOCKED' : 'ACTIVE' }}

                                                        </div>
                                                    </div>

                                                    <div
                                                        class="md:col-span-5 mt-2 p-2 bg-gray-50 border border-slate-200 rounded-lg shadow-sm text-xs">

                                                        <div class="flex justify-between items-center w-full">

                                                            <div>
                                                                <span class="font-semibold text-slate-700">Registration Date:</span>
                                                                {{ new Date(user.created_at).toLocaleString() }}
                                                            </div>

                                                            <div v-if="authStore.currentUser?.id !== user.id"
                                                                class="flex gap-2 items-center flex-shrink-0">

                                                                <Button size="sm" @click.stop="toggleBlock(user.id)"
                                                                    :variant="user.blocked ? 'success' : 'destructive'">
                                                                    {{ user.blocked ? '✅ Unblock' : '🛑 Block' }}
                                                                </Button>

                                                                <Button size="sm"
                                                                    @click.stop="confirmRemoveUser(user.id, user.nickname)"
                                                                    variant="outline">
                                                                    🗑️ Delete Account
                                                                </Button>
                                                            </div>

                                                            <span v-else
                                                                class="text-xs text-red-600 font-semibold md:text-right w-full md:w-auto">
                                                                ⚠️ You cannot manage your own account.
                                                            </span>

                                                        </div>
                                                    </div>

                                                </div>

                                            </div>

                                            <p v-else class="text-xs text-red-500 py-4">Could not load statistics.</p>

                                        </div>

                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </CardContent>

                <div class="flex justify-center items-center gap-4 pt-4 pb-4 border-t" v-if="pagination.last_page > 1">
                    <Button variant="outline" :disabled="pagination.current_page === 1"
                        @click="changePage(pagination.current_page - 1)">
                        &larr; Previous
                    </Button>
                    <span class="text-sm font-medium text-slate-600">
                        Page {{ pagination.current_page }} of {{ pagination.last_page }}
                    </span>
                    <Button variant="outline" :disabled="pagination.current_page === pagination.last_page"
                        @click="changePage(pagination.current_page + 1)">
                        Next &rarr;
                    </Button>
                </div>
            </Card>

        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, inject } from 'vue'
import { useAPIStore } from '@/stores/api'
import { useAuthStore } from '@/stores/auth'
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card'
import { Avatar, AvatarImage } from '@/components/ui/avatar'
import { Button } from '@/components/ui/button'

const apiStore = useAPIStore()
const authStore = useAuthStore()
const serverBaseURL = inject('serverBaseURL')

// Variáveis de Estado
const users = ref([]) // Este será o array de dados (users.data)
const pagination = ref({})
const isLoading = ref(true)
const expandedUsers = ref(new Set())
const currentFilter = ref('all') // ⬅️ NOVO: Estado inicial do filtro
const searchQuery = ref('');

const showCreateAdminModal = ref(false)
const newUser = ref({
    name: '',
    nickname: '',
    email: '',
    password: '',
    type: 'P' // Começa como Player por defeito
})

const handleCreateUser = async () => {
    try {
        // 1. Chamada à API através da store
        await apiStore.createUserAdmin(newUser.value)

        // 2. Feedback e fechar modal
        alert('Utilizador criado com sucesso!')
        showCreateAdminModal.value = false

        // 3. Limpar os campos para a próxima vez
        newUser.value = { name: '', nickname: '', email: '', password: '', type: 'P' }

        // 4. Recarregar a lista para aparecer o novo user
        await loadUsers(1)

    } catch (error) {
        console.error(error)
        alert(error.response?.data?.message || 'Erro ao criar utilizador')
    }
}

// 1. Lógica de Expansão (toggleDetails)
const toggleDetails = async (id) => {
    // ... (Mantém o código existente para expansão)
    const targetUser = users.value.find(u => u.id === id);

    if (expandedUsers.value.has(id)) {
        expandedUsers.value.delete(id)
    } else {
        expandedUsers.value.add(id)
    }
    expandedUsers.value = new Set(expandedUsers.value)

    if (targetUser && !targetUser.detailedStats) {
        targetUser.statsLoading = true;
        try {
            const response = await apiStore.getPlayerStats(id);
            targetUser.detailedStats = response.data;
        } catch (e) {
            console.error(`Erro ao carregar estatísticas para o utilizador ${id}:`, e);
            targetUser.detailedStats = {};
        } finally {
            targetUser.statsLoading = false;
        }
    }
}

const isExpanded = (id) => {
    return expandedUsers.value.has(id)
}

// 💡 NOVO: Função para alterar o filtro e recarregar
const setFilter = (filter) => {
    currentFilter.value = filter;
    loadUsers(1); // Sempre volta para a primeira página com o novo filtro
}


// 3. Função de Carregamento Principal (loadUsers)
const loadUsers = async (page = 1) => {
    isLoading.value = true
    expandedUsers.value = new Set() // Limpar detalhes expandidos ao recarregar

    try {
        // 💡 ATUALIZADO: Passa o filtro atual para a API
        const res = await apiStore.getUsersListAdmin(page, currentFilter.value, searchQuery.value)

        if (res.data) {
            // Se a API retornar um objeto de paginação (Laravel), usa data dentro
            users.value = res.data.data

            // Atribuir os metadados de paginação
            pagination.value = {
                current_page: res.data.current_page,
                last_page: res.data.last_page,
                total: res.data.total,
                per_page: res.data.per_page,
            }
        }
    } catch (e) {
        console.error("Erro ao carregar lista de utilizadores:", e)
    } finally {
        isLoading.value = false
    }
}

const toggleBlock = async (userId) => {
    // ... (Mantém o seu código toggleBlock corrigido)
    if (!confirm('Tem certeza que deseja alterar o estado de bloqueio deste utilizador?')) {
        return;
    }

    try {
        const response = await apiStore.toggleUserBlock(userId);

        const userIndex = users.value.findIndex(u => u.id === userId);

        if (userIndex !== -1) {
            users.value[userIndex].blocked = response.data.blocked;
        }

        alert(`Estado de bloqueio alterado para: ${response.data.blocked ? 'BLOQUEADO' : 'ATIVO'}`);
    } catch (error) {
        console.error('Erro ao alternar bloqueio:', error);
        alert('Falha ao alterar o estado de bloqueio. Verifique o console.');
    }
};

// 2. 🗑️ Função para Desativar (Soft Delete)
const confirmRemoveUser = async (userId, nickname) => {
    if (!confirm(`AVISO: Tem certeza que deseja desativar (soft-delete) a conta de ${nickname}?
    (A conta será removida da lista, mas os dados serão preservados.)`)) {
        return;
    }

    try {
        const response = await apiStore.removeUserAccount(userId);

        // Remoção Local com splice para desaparecer da lista
        const userIndex = users.value.findIndex(u => u.id === userId);

        if (userIndex !== -1) {
            users.value.splice(userIndex, 1);
            if (pagination.value.total) {
                pagination.value.total--;
            }
        }

        alert(`Sucesso! Conta de ${nickname} desativada. ${response.data.message}`);

    } catch (error) {
        const errorMessage = error.response?.data?.error || 'Falha na comunicação com o servidor. Tente novamente.';
        console.error('Erro ao desativar utilizador:', error);
        alert(`Falha ao desativar: ${errorMessage}`);
    }
};

// 4. Função de Alteração de Página
const changePage = (page) => {
    if (page > 0 && page <= pagination.value.last_page) {
        loadUsers(page)
    }
}

onMounted(() => {
    loadUsers(1) // Carregar a primeira página com o filtro 'all'
})
</script>