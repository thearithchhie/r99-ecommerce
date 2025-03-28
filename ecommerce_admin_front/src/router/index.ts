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
        component: () => import('@/views/UsersView.vue'),
        meta: { requiresPermission: 'view users' }
      },
      {
        path: 'users/:id',
        name: 'userDetail',
        component: () => import('@/views/UserDetailView.vue'),
        meta: { requiresPermission: 'view users' }
      },
      {
        path: 'users/:id/edit',
        name: 'userEdit',
        component: () => import('@/views/UserEditPage.vue'),
        meta: { requiresPermission: 'update users' }
      },
      {
        path: 'roles',
        name: 'roles',
        component: () => import('@/views/RolesView.vue'),
        meta: { requiresPermission: 'view roles' }
      },
      {
        path: 'permissions',
        name: 'permissions',
        component: () => import('@/views/PermissionsView.vue'),
        meta: { requiresPermission: 'view permissions' }
      },
      {
        path: 'user-roles',
        name: 'userRoles',
        component: () => import('@/views/UserRolesView.vue'),
        meta: { requiresPermission: 'assign roles' }
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
router.beforeEach(async (to, from, next) => {
  // Check for auth token (better than isAuthenticated cookie)
  const hasAuthToken = getCookie('token') !== null
  
  // Check if the route requires authentication
  if (to.matched.some(record => record.meta.requiresAuth)) {
    // If not authenticated, redirect to login
    if (!hasAuthToken) {
      next({ name: 'login' })
    } else {
      // Check for permission requirements
      const requiresPermission = to.matched.find(record => record.meta.requiresPermission)
      
      if (requiresPermission && requiresPermission.meta.requiresPermission) {
        try {
          // Check if user has the required permission
          const permissionName = requiresPermission.meta.requiresPermission as string
          const response = await fetch('/api/check-permission', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'Authorization': `Bearer ${getCookie('token')}`
            },
            body: JSON.stringify({ permission: permissionName })
          })
          
          if (response.ok) {
            const data = await response.json()
            if (data.hasPermission) {
              next() // User has permission, allow access
            } else {
              // Redirect to dashboard with error message
              next({ 
                name: 'dashboard', 
                query: { 
                  permissionError: `You don't have permission to access this page`
                } 
              })
            }
          } else {
            // API error, allow access but log error
            console.error('Error checking permission:', await response.text())
            next()
          }
        } catch (error) {
          // Network error, allow access but log error
          console.error('Error checking permission:', error)
          next()
        }
      } else {
        next() // No permission required, allow access
      }
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