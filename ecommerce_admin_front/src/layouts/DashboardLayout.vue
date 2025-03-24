<script setup lang="ts">
import { ref, computed } from 'vue'
import { Menu, ChevronDown, User, Settings, LogOut, LayoutDashboard, Package, ShoppingCart, Users, AlertTriangle } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
import {
  DropdownMenu,
  DropdownMenuTrigger,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
} from '@/components/ui/dropdown-menu'
import { useRouter } from 'vue-router'
import { useToast } from '@/components/ui/toast'
import { useAuthStore } from '@/stores/auth'
import LogoutButton from '@/components/ui/logout-button.vue'

const router = useRouter()
const toast = useToast()
const authStore = useAuthStore()
const isSidebarOpen = ref(true)

// Debug logging
console.log('Auth Store State:', {
  isAuthenticated: authStore.isAuthenticated,
  user: authStore.user,
  userName: authStore.userName,
  userEmail: authStore.userEmail,
  userRole: authStore.userRole
})

// Use auth store for user data
const userData = computed(() => {
  console.log('Computing userData:', {
    name: authStore.userName,
    email: authStore.userEmail,
    role: authStore.userRole
  })
  return {
    name: authStore.userName || 'Guest',
    email: authStore.userEmail || 'No email',
    role: authStore.userRole || 'No role'
  }
})

const navigationItems = [
  { name: 'Dashboard', path: '/dashboard', icon: LayoutDashboard },
  { name: 'Products', path: '/products', icon: Package },
  { name: 'Orders', path: '/orders', icon: ShoppingCart },
  { name: 'Customers', path: '/customers', icon: Users },
  { name: 'Users', path: '/users', icon: User },
]

const toggleSidebar = () => {
  isSidebarOpen.value = !isSidebarOpen.value
}
</script>

<template>
  <div class="relative min-h-screen bg-background">
    <!-- Sidebar -->
    <aside
      :class="[
        'fixed inset-y-0 left-0 z-50 flex w-64 flex-col border-r bg-card transition-transform duration-300',
        { '-translate-x-full': !isSidebarOpen },
      ]"
    >
      <div class="flex h-16 items-center border-b px-6">
        <h1 class="text-xl font-semibold">Admin Dashboard</h1>
      </div>
      <nav class="flex-1 space-y-1 p-4">
        <router-link
          v-for="item in navigationItems"
          :key="item.path"
          :to="item.path"
          class="flex items-center rounded-md px-3 py-2 text-sm font-medium transition-colors hover:bg-accent hover:text-accent-foreground"
          :class="{ 'bg-accent text-accent-foreground': $route.path === item.path }"
        >
          <component :is="item.icon" class="mr-2 h-4 w-4" />
          {{ item.name }}
        </router-link>
      </nav>
    </aside>

    <!-- Main Content -->
    <div
      :class="[
        'flex flex-col transition-all duration-300',
        { 'ml-64': isSidebarOpen },
      ]"
    >
      <!-- Header -->
      <header class="sticky top-0 z-40 flex h-16 items-center border-b bg-card/80 px-6 backdrop-blur">
        <Button
          variant="ghost"
          size="icon"
          class="mr-4 md:hidden"
          @click="toggleSidebar"
        >
          <Menu class="h-5 w-5" />
          <span class="sr-only">Toggle menu</span>
        </Button>
        
        <div class="ml-auto flex items-center gap-4">
          <DropdownMenu>
            <DropdownMenuTrigger asChild>
              <div class="h-9 w-9 rounded-full bg-blue-100 dark:bg-blue-900/50 flex items-center justify-center cursor-pointer hover:bg-blue-200 dark:hover:bg-blue-800/50 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 text-blue-600 dark:text-blue-400">
                  <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
                  <circle cx="12" cy="7" r="4"></circle>
                </svg>
              </div>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end">
              <DropdownMenuLabel class="font-normal">
                <div class="flex flex-col space-y-1">
                  <p class="text-sm font-medium leading-none">{{ userData.name }}</p>
                  <p class="text-xs leading-none text-muted-foreground">{{ userData.email }}</p>
                </div>
              </DropdownMenuLabel>
              <DropdownMenuSeparator />
              <DropdownMenuItem>
                <router-link to="/profile" class="flex items-center w-full">
                  <User class="mr-2 h-4 w-4" />
                  <span>Edit Profile</span>
                </router-link>
              </DropdownMenuItem>
              <DropdownMenuItem>
                <Settings class="mr-2 h-4 w-4" />
                <span>Settings</span>
              </DropdownMenuItem>
              <DropdownMenuSeparator />
              <DropdownMenuItem>
                <router-link to="/logout" class="flex items-center w-full">
                  <LogOut class="mr-2 h-4 w-4" />
                  <span>Logout Page</span>
                </router-link>
              </DropdownMenuItem>
            </DropdownMenuContent>
          </DropdownMenu>
        </div>
      </header>

      <!-- Page Content -->
      <main class="flex-1 p-6">
        <router-view />
      </main>
    </div>
  </div>
</template>