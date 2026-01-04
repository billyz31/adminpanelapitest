import { createRouter, createWebHistory } from 'vue-router'
import Login from '../views/Login.vue'
import Dashboard from '../views/Dashboard.vue'
import HealthCheck from '../views/HealthCheck.vue'
import Users from '../views/Users.vue'
import GameRounds from '../views/GameRounds.vue'
import Transactions from '../views/Transactions.vue'
import Settings from '../views/Settings.vue'
import Layout from '../components/Layout.vue'

const routes = [
  {
    path: '/login',
    name: 'Login',
    component: Login,
    meta: { requiresGuest: true }
  },
  {
    path: '/',
    component: Layout,
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        name: 'Dashboard',
        component: Dashboard
      },
      {
        path: 'health',
        name: 'HealthCheck',
        component: HealthCheck
      },
      {
        path: 'users',
        name: 'Users',
        component: Users
      },
      {
        path: 'game-rounds',
        name: 'GameRounds',
        component: GameRounds
      },
      {
        path: 'transactions',
        name: 'Transactions',
        component: Transactions
      },
      {
        path: 'settings',
        name: 'Settings',
        component: Settings
      }
    ]
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

// Navigation Guard (Placeholder for now)
router.beforeEach((to, _from, next) => {
  const isAuthenticated = localStorage.getItem('admin_token')
  
  if (to.meta.requiresAuth && !isAuthenticated) {
    next('/login')
  } else if (to.meta.requiresGuest && isAuthenticated) {
    next('/')
  } else {
    next()
  }
})

export default router
