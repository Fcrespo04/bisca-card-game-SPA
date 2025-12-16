import { createRouter, createWebHistory } from 'vue-router'

import AboutPage from '@/pages/about/AboutPage.vue'
import HomePage from '@/pages/home/HomePage.vue'
import SingleplayerGamePage from '@/pages/game/SinglePlayerGamePage.vue'
import LoginPage from '@/pages/login/LoginPage.vue'
import ProfilePage from '@/pages/profile/ProfilePage.vue'
import { useAuthStore } from '@/stores/auth'
import MultiplayerLobbyPage from '@/pages/game/MultiplayerLobbyPage.vue'
import MultiplayerGamePage from '@/pages/game/MultiplayerGamePage.vue'
import RegisterPage from '@/pages/register/RegisterPage.vue'
import ShopPage from '@/pages/transactions/ShopPage.vue'
import CardPacksShop from '@/pages/transactions/CardPackShop.vue'
import MyCardCollection from '@/pages/profile/MyCardCollection.vue'
import { toast } from 'vue-sonner'
import Statistics from '@/pages/Statistics/Statistics.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      component: HomePage,
    },
    {
      path: '/games',
      children: [
        {
          path: 'singleplayer',
          name: 'singleplayer',
          component: SingleplayerGamePage,
        },
        {
          path: 'lobby',
          name: 'multiplayer-lobby',
          component: MultiplayerLobbyPage,
          meta: { requiresAuth: true },
        },
        {
          path: 'multiplayer',
          name: 'multiplayer',
          component: MultiplayerGamePage,
          meta: { requiresAuth: true },
        },
      ],
    },
    {
      path: '/about',
      name: 'about',
      component: AboutPage,
    },
    {
      path: '/statistics',
      name: 'statistics',
      component: Statistics
    },
    {
      path: '/login',
      name: 'login',
      component: LoginPage,
    },
    {
      path: '/register',
      name: 'register',
      component: RegisterPage,
    },
    {
      path: '/profile',
      name: 'profile',
      component: ProfilePage,
      meta: { requiresAuth: true },
    },
    {
      path: '/transactions',
      name: 'transactions',
      component: ShopPage,
      meta: { requiresAuth: true },
    },
    {
      path: '/card-packs',
      name: 'card-packs-shop',
      component: CardPacksShop,
      meta: { requiresAuth: true },
    },
    {
      path: '/my-cards',
      name: 'my-card-collection',
      component: MyCardCollection,
      meta: { requiresAuth: true },
    },
  ],
})

router.beforeEach((to, from, next) => {
  const authStore = useAuthStore()
  if (to.meta.requiresAuth && !authStore.isLoggedIn) {
    toast.error('This navigation requires authentication')
    next({ name: 'login' })
  } else {
    next()
  }
})

export default router
