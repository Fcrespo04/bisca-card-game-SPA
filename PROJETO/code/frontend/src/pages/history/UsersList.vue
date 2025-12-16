<template>
    <div v-if="authStore.currentUser?.type === 'A'" class="max-w-6xl mx-auto p-6 space-y-12">

        <div class="text-center space-y-2">
            <h1 class="text-4xl font-bold text-slate-800">👥 Lista de Utilizadores</h1>
            <p class="text-slate-600 text-lg">Consulta de todos os utilizadores registados na plataforma.</p>
        </div>

        <div class="flex items-right gap-2 w-full md:w-auto">
            <Button variant="default" @click="showCreateAdminModal = true">
                ➕ Adicionar Novo User
            </Button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">

            <Card class="h-full border-t-4 border-t-yellow-500 shadow-md md:col-span-3">
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <span>👥</span> Utilizadores ({{ pagination.total || 0 }} total)
                    </CardTitle>
                </CardHeader>

                <CardContent class="p-0">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                            <tr>
                                <th class="px-4 py-3 w-12 text-center">ID</th>
                                <th class="px-4 py-3">Nickname</th>
                                <th class="px-4 py-3">Nome Completo</th>
                                <th class="px-4 py-3 text-right">Data de Entrada</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-if="isLoading">
                                <td colspan="4" class="px-4 py-8 text-center text-indigo-500 italic">
                                    A carregar utilizadores...
                                </td>
                            </tr>
                            <tr v-else-if="users.length === 0">
                                <td colspan="4" class="px-4 py-8 text-center text-gray-400 italic">
                                    Ainda sem registos...
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
                                            <h4 class="font-bold text-slate-700 uppercase text-xs mb-3">Performance e
                                                Conquistas</h4>

                                            <div v-if="user.statsLoading"
                                                class="p-3 text-center text-sm text-indigo-500 rounded border border-indigo-100 bg-white">
                                                A carregar...
                                            </div>

                                            <div v-else-if="user.detailedStats">

                                                <div class="grid grid-cols-5 gap-2">

                                                    <div class="p-2 rounded-lg text-center shadow-sm"
                                                        :class="user.detailedStats.matches_won > 0 ? 'bg-green-50 border border-green-200' : 'bg-gray-50 border border-gray-200'">
                                                        <div class="text-xl font-extrabold text-green-700">
                                                            {{ user.detailedStats.matches_won }}
                                                        </div>
                                                        <div class="text-xs text-gray-600 mt-1">Partidas Ganhas</div>
                                                    </div>
                                                    <div class="p-2 rounded-lg text-center shadow-sm"
                                                        :class="user.detailedStats.games_won > 0 ? 'bg-green-50 border border-green-200' : 'bg-gray-50 border border-gray-200'">
                                                        <div class="text-xl font-extrabold text-green-700">
                                                            {{ user.detailedStats.games_won }}
                                                        </div>
                                                        <div class="text-xs text-gray-600 mt-1">Jogos Ganhos</div>
                                                    </div>
                                                    <div
                                                        class="p-2 rounded-lg text-center bg-blue-50 border border-blue-200 shadow-sm">
                                                        <div class="text-xl font-extrabold text-blue-700">
                                                            {{ user.detailedStats.total_score }}
                                                        </div>
                                                        <div class="text-xs text-gray-600 mt-1">Score Total</div>
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
                                                        <div class="text-xs text-gray-600 mt-1">Bandeiras</div>
                                                    </div>

                                                </div>

                                                <div class="my-4 border-t border-slate-200 w-full md:col-span-5"></div>

                                                <div class="grid grid-cols-2 md:grid-cols-5 gap-3 text-sm">

                                                    <div
                                                        class="p-2 bg-gray-50 border border-gray-200 rounded-lg shadow-sm">
                                                        <div class="text-xs text-gray-600 uppercase">Partidas Jogadas
                                                        </div>
                                                        <div class="font-medium text-slate-800">{{
                                                            user.detailedStats.matches_played }}</div>
                                                    </div>

                                                    <div
                                                        class="p-2 bg-gray-50 border border-gray-200 rounded-lg shadow-sm">
                                                        <div class="text-xs text-gray-600 uppercase">Jogos Jogados</div>
                                                        <div class="font-medium text-slate-800">{{
                                                            user.detailedStats.games_played }}</div>
                                                    </div>

                                                    <div
                                                        class="p-2 bg-gray-50 border border-gray-200 rounded-lg shadow-sm">
                                                        <div class="text-xs text-gray-600 uppercase">Email</div>
                                                        <div class="font-medium text-slate-800 truncate">{{ user.email
                                                        }}
                                                        </div>
                                                    </div>

                                                    <div
                                                        class="p-2 bg-blue-50 border border-blue-200 rounded-lg shadow-sm">
                                                        <div class="text-xs text-gray-600 uppercase">Coins Balance</div>
                                                        <div class="font-bold text-blue-700">{{ user.coins_balance }}
                                                        </div>
                                                    </div>

                                                    <div class="p-2 rounded-lg shadow-sm"
                                                        :class="user.blocked ? 'bg-red-50 border border-red-200' : 'bg-green-50 border border-green-200'">
                                                        <div class="text-xs text-gray-600 uppercase">Estado</div>
                                                        <div class="font-bold"
                                                            :class="user.blocked ? 'text-red-700' : 'text-green-700'">
                                                            {{ user.blocked ? 'BLOQUEADO' : 'ATIVO' }}
                                                        </div>
                                                    </div>

                                                    <div
                                                        class="md:col-span-5 mt-2 p-2 bg-gray-50 border border-slate-200 rounded-lg shadow-sm text-xs">

                                                        <div class="flex justify-between items-center w-full">

                                                            <div>
                                                                <span class="font-semibold text-slate-700">Data de
                                                                    Registo:</span>
                                                                {{ new Date(user.created_at).toLocaleString() }}
                                                            </div>

                                                            <div v-if="authStore.currentUser?.id !== user.id"
                                                                class="flex gap-2 items-center flex-shrink-0">

                                                                <Button size="sm" @click.stop="toggleBlock(user.id)"
                                                                    :variant="user.blocked ? 'success' : 'destructive'">
                                                                    {{ user.blocked ? '✅ Desbloquear' : '🛑 Bloquear' }}
                                                                </Button>

                                                                <Button size="sm"
                                                                    @click.stop="confirmRemoveUser(user.id, user.nickname)"
                                                                    variant="outline">
                                                                    🗑️ Remover Conta
                                                                </Button>
                                                            </div>

                                                            <span v-else
                                                                class="text-xs text-red-600 font-semibold md:text-right w-full md:w-auto">
                                                                ⚠️ Não pode gerir a sua própria conta.
                                                            </span>

                                                        </div>
                                                    </div>

                                                </div>

                                            </div>

                                            <p v-else class="text-xs text-red-500 py-4">Não foi possível carregar as
                                                estatísticas.</p>

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
                        &larr; Anterior
                    </Button>
                    <span class="text-sm font-medium text-slate-600">
                        Página {{ pagination.current_page }} de {{ pagination.last_page }}
                    </span>
                    <Button variant="outline" :disabled="pagination.current_page === pagination.last_page"
                        @click="changePage(pagination.current_page + 1)">
                        Seguinte &rarr;
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
// Não precisamos de 'detailsLoadingId' se usarmos um campo no próprio objeto 'user'.

// 1. Lógica de Expansão (toggleDetails)
const toggleDetails = async (id) => {
    // Encontrar o objeto do utilizador no array
    const targetUser = users.value.find(u => u.id === id);

    if (expandedUsers.value.has(id)) {
        expandedUsers.value.delete(id)
    } else {
        expandedUsers.value.add(id)
    }
    expandedUsers.value = new Set(expandedUsers.value)

    // 2. Carregar Estatísticas Pessoais do Jogador (se não existirem)
    if (targetUser && !targetUser.detailedStats) {
        // Adicionar estado de loading temporário ao objeto do utilizador
        targetUser.statsLoading = true;

        try {
            // Chamada à API (Assumindo que apiStore tem getPlayerStats(id))
            const response = await apiStore.getPlayerStats(id);
            // Armazena as estatísticas no campo 'detailedStats'
            targetUser.detailedStats = response.data;
        } catch (e) {
            console.error(`Erro ao carregar estatísticas para o utilizador ${id}:`, e);
            targetUser.detailedStats = {}; // Define um objeto vazio para indicar falha
        } finally {
            targetUser.statsLoading = false;
        }
    }
}

const isExpanded = (id) => {
    return expandedUsers.value.has(id)
}

// 3. Função de Carregamento Principal (loadUsers)
const loadUsers = async (page = 1) => {
    isLoading.value = true
    try {
        // Assume que a função getUsersList aceita 'page'
        const res = await apiStore.getUsersList(page)

        if (res.data) {
            // Atribuir o array de utilizadores
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
    if (!confirm('Tem certeza que deseja alterar o estado de bloqueio deste utilizador?')) {
        return;
    }

    try {
        const response = await apiStore.toggleUserBlock(userId);
        
        // 1. CORREÇÃO: Usar findIndex para obter o índice numérico
        const userIndex = users.value.findIndex(u => u.id === userId); 
        
        // 2. CORREÇÃO: Verificar se o índice é válido (diferente de -1)
        if (userIndex !== -1) {
            // 3. CORREÇÃO: Aceder ao valor 'blocked' da resposta via response.data
            users.value[userIndex].blocked = response.data.blocked; 
        }
        
        alert(`Estado de bloqueio alterado para: ${response.data.blocked ? 'BLOQUEADO' : 'ATIVO'}`);
    } catch (error) {
        console.error('Erro ao alternar bloqueio:', error);
        alert('Falha ao alterar o estado de bloqueio. Verifique o console.');
    }
};

// 2. 🗑️ Função para Remover Conta
const confirmRemoveUser = async (userId, nickname) => {
    // ⚠️ Mudei a mensagem para refletir a ação de desativação/soft-delete
    if (!confirm(`AVISO: Tem certeza que deseja desativar (soft-delete) a conta de ${nickname}?
    
    (A conta será removida da lista, mas os dados serão preservados.)`)) {
        return;
    }

    try {
        // Chamada à API: DELETE /api/admin/users/{userId}
        const response = await apiStore.removeUserAccount(userId);
        
        // 💡 Remoção Local com splice (Rápido e Eficiente)
        
        // Encontra o índice do utilizador no array users.value
        // NOTA: users.value é um array (pois loadUsers atribui res.data.data)
        const userIndex = users.value.findIndex(u => u.id === userId);
        
        if (userIndex !== -1) {
            // Remove 1 elemento a partir da posição userIndex
            users.value.splice(userIndex, 1);
            
            // Opcional: Atualizar o total da paginação
            if (pagination.value.total) {
                pagination.value.total--;
            }
        }
        
        alert(`Sucesso! Conta de ${nickname} desativada. ${response.data.message}`);
        
    } catch (error) {
        console.error('Erro ao desativar utilizador:', error.response?.data || error.message);
        const errorMessage = error.response?.data?.error || 'Falha na comunicação com o servidor. Tente novamente.';
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
    loadUsers(1) // Carregar a primeira página
})
</script>