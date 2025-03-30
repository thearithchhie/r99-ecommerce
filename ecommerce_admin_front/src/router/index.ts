import { createRouter, createWebHistory } from 'vue-router'
import type { RouteRecordRaw } from 'vue-router'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import LoginLayout from '@/layouts/LoginLayout.vue'
import LogoutView from '@/views/LogoutView.vue'

// Route configuration
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
        component: () => import('@/views/products/ProductListView.vue')
      },
      {
        path: 'products/create',
        name: 'product-create',
        component: () => import('@/views/products/ProductFormView.vue')
      },
      {
        path: 'products/:id/edit',
        name: 'product-edit',
        component: () => import('@/views/products/ProductFormView.vue')
      },
      {
        path: 'products/:id/variants',
        name: 'product-variants',
        component: () => import('@/views/products/ProductVariantsView.vue')
      },
      {
        path: 'categories',
        name: 'categories',
        component: () => import('@/views/categories/CategoryListView.vue'),
        meta: { requiresPermission: 'view products' }
      },
      {
        path: 'brands',
        name: 'brands',
        component: () => import('@/views/brands/BrandListView.vue'),
        meta: { requiresPermission: 'view products' }
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
        name: 'user-roles',
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

export default router;