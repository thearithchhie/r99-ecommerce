import { createRouter, createWebHistory } from 'vue-router'
import type { RouteRecordRaw } from 'vue-router'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import LoginLayout from '@/layouts/LoginLayout.vue'
import LogoutView from '@/views/LogoutView.vue'

// Function to get a cookie value by name
const getCookie = (name: string): string | null => {
  const nameEQ = name + "="
  const ca = document.cookie.split(';')
  for (let i = 0; i < ca.length; i++) {
    let c = ca[i]
    while (c.charAt(0) === ' ') c = c.substring(1, c.length)
    if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length)
  }
  return null
}

const routes: RouteRecordRaw[] = [
  {
    path: '/',
    component: DashboardLayout,
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        redirect: '/dashboard'
      },
      {
        path: 'dashboard',
        name: 'dashboard',
        component: () => import('@/views/DashboardView.vue')
      },
      {
        path: 'profile',
        name: 'profile',
        component: () => import('@/views/ProfileView.vue')
      },
      {
        path: 'products',
        name: 'products',
        component: () => import('@/views/ProductsView.vue')
      },
      {
        path: 'orders',
        name: 'orders',
        component: () => import('@/views/OrdersView.vue')
      },
      {
        path: 'customers',
        name: 'customers',
        component: () => import('@/views/CustomersView.vue')
      },
      {
        path: 'users',
        name: 'users',
        component: () => import('@/views/UsersView.vue')
      },
      {
        path: 'users/:id',
        name: 'userDetail',
        component: () => import('@/views/UserDetailView.vue')
      },
      {
        path: 'users/:id/edit',
        name: 'userEdit',
        component: () => import('@/views/UserEditPage.vue')
      }
    ]
  },
  {
    path: '/auth',
    component: LoginLayout,
    children: [
      {
        path: 'login',
        name: 'login',
        component: () => import('@/views/LoginView.vue')
      }
    ]
  },
  {
    path: '/login',
    redirect: '/auth/login'
  },
  {
    path: '/logout',
    name: 'logout',
    component: LogoutView,
    meta: {
      requiresAuth: true
    }
  }
]

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes
})

// Navigation Guards
router.beforeEach((to, from, next) => {
  // Check for auth token (better than isAuthenticated cookie)
  const hasAuthToken = getCookie('token') !== null
  
  // Check if the route requires authentication
  if (to.matched.some(record => record.meta.requiresAuth)) {
    // If not authenticated, redirect to login
    if (!hasAuthToken) {
      next({ name: 'login' })
    } else {
      next() // Allow access
    }
  } else {
    // For login route, redirect to dashboard if already authenticated
    if (to.name === 'login' && hasAuthToken) {
      next({ name: 'dashboard' })
    } else {
      next() // Allow access to non-auth routes
    }
  }
})

export default router;