<template>
  <Toaster richColors />
  <nav class="max-w-full p-5 flex flex-row justify-between align-middle">

    <div class="align-middle text-xl flex items-center gap-4">
      <RouterLink to="/"> Bisca Game </RouterLink>

      <div v-if="authStore.currentUser" class="flex items-center gap-3 text-sm bg-slate-100 px-3 py-1 rounded-full border border-slate-200">

        <span class="font-medium text-slate-700">
          {{ authStore.currentUser.nickname || authStore.currentUser.name }}
        </span>

        <span v-if="authStore.currentUser.type === 'A'" class="bg-red-100 text-red-700 text-xs px-2 py-0.5 rounded font-bold">
            ADMIN
        </span>

        <span v-if="authStore.currentUser.type !== 'A'" class="text-slate-300">|</span>

        <RouterLink v-if="authStore.currentUser.type !== 'A'" to="/transactions" class="flex items-center gap-1 hover:text-blue-600 transition-colors" title="Buy Coins">
          <span class="font-bold text-yellow-600">{{ authStore.currentUser.coins_balance }}</span>
          <span>💰</span>
          <span class="text-xs bg-blue-100 text-blue-700 px-1.5 py-0.5 rounded ml-1">Shop</span>
        </RouterLink>

        <RouterLink v-if="authStore.currentUser.type !== 'A'" to="/card-packs" class="flex items-center gap-1 hover:text-purple-600 transition-colors ml-2" title="Card Shop">
          <span>🃏</span>
          <span class="text-xs bg-purple-100 text-purple-700 px-1.5 py-0.5 rounded ml-1">Card Packs</span>
        </RouterLink>
      </div>
    </div>
    <div class="align-middle text-xl flex items-center gap-4">
      <div class="flex items-center gap-3 ...">
        <RouterLink to="/leaderboard" class="flex items-center gap-1 hover:text-blue-600 transition-colors"
          title="Leaderboard">
          <span>🏆</span>
          <span class="text-xs bg-yellow-100 text-yellow-700 px-1.5 py-0.5 rounded ml-1">Leaderboard</span>
        </RouterLink>
      </div>
    </div>

    <NavigationMenu>
      <NavigationMenuList class="justify-around gap-20">

        
        <NavigationMenuItem>
          <NavigationMenuLink as-child>
            <RouterLink to="/statistics" class="group inline-flex h-10 w-max items-center justify-center rounded-md bg-background px-4 py-2 text-sm font-medium transition-colors hover:bg-accent hover:text-accent-foreground focus:bg-accent focus:text-accent-foreground focus:outline-none disabled:pointer-events-none disabled:opacity-50 data-active:bg-accent/50 data-[state=open]:bg-accent/50">
              Estatísticas
            </RouterLink>
          </NavigationMenuLink>
        </NavigationMenuItem>

        <NavigationMenuItem v-if="!authStore.currentUser || authStore.currentUser.type !== 'A'">
          <NavigationMenuTrigger>Games</NavigationMenuTrigger>
          <NavigationMenuContent>
            <li>
              <NavigationMenuLink as-child>
                <RouterLink to="/games/singleplayer">SinglePlayer</RouterLink>
              </NavigationMenuLink>
              <NavigationMenuLink as-child>
                <RouterLink to="/games/lobby">MultiPlayer</RouterLink>
              </NavigationMenuLink>
            </li>
          </NavigationMenuContent>
        </NavigationMenuItem>

        <NavigationMenuItem>
          <NavigationMenuLink>
            <RouterLink to="/about">About</RouterLink>
          </NavigationMenuLink>
        </NavigationMenuItem>

        <NavigationMenuItem v-if="!authStore.isLoggedIn">
          <NavigationMenuLink>
            <RouterLink to="/login">Login</RouterLink>
          </NavigationMenuLink>
        </NavigationMenuItem>

        <NavigationMenuItem v-else>
          <NavigationMenuTrigger>Account</NavigationMenuTrigger>
          <NavigationMenuContent>
            <li>
              <NavigationMenuLink as-child>
                <RouterLink to="/profile">Profile</RouterLink>
              </NavigationMenuLink>
              <NavigationMenuLink as-child>
                <RouterLink to="/history">History</RouterLink>
              </NavigationMenuLink>
              <NavigationMenuLink as-child v-if="authStore.isLoggedIn && authStore.currentUser?.type === 'A'">
                <RouterLink to="/users">Users</RouterLink>
              </NavigationMenuLink>
              <NavigationMenuLink as-child v-if="authStore.isLoggedIn && authStore.currentUser?.type === 'A'">
                <RouterLink to="/admin/transactions">Admin Stats</RouterLink>
              </NavigationMenuLink>

              <NavigationMenuLink as-child v-if="authStore.currentUser.type !== 'A'">
                <RouterLink to="/my-cards">Card Collection</RouterLink>
              </NavigationMenuLink>

              <NavigationMenuLink as-child>
                <a @click.prevent="logout" class="cursor-pointer">Logout</a>
              </NavigationMenuLink>
            </li>
          </NavigationMenuContent>
        </NavigationMenuItem>
      </NavigationMenuList>
    </NavigationMenu>
  </nav>
  <div>
    <main>
      <RouterView />
    </main>
  </div>
</template>

<script setup>
import {
  NavigationMenu,
  NavigationMenuContent,
  NavigationMenuItem,
  NavigationMenuLink,
  NavigationMenuList,
  NavigationMenuTrigger,
} from '@/components/ui/navigation-menu'
import { Toaster } from '@/components/ui/sonner'
import { toast } from 'vue-sonner'
import 'vue-sonner/style.css'
import { RouterLink, RouterView, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useSocketStore } from '@/stores/socket'
import { onMounted } from 'vue'

const authStore = useAuthStore()
const socketStore = useSocketStore()
const router = useRouter()

const logout = () => {
  toast.promise(authStore.logout(), {
    loading: 'Calling API',
    success: () => {
      router.push({ name: 'home' })
      return 'Logout Sucessfull '
    },
    error: (data) => `[API] Error logging out - ${data?.response?.data?.message}`,
  })

}

onMounted(() => {
  socketStore.handleConnection()
  socketStore.handleGameEvents()
})
</script>

<style></style>
