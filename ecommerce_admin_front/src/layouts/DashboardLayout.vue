<script setup lang="ts">
import { ref, computed, watch, onMounted, reactive } from 'vue'
import { Menu, ChevronDown, User, Settings, LogOut, LayoutDashboard, Package, ShoppingCart, Users, X, Shield, Key, UserCog } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
import {
  DropdownMenu,
  DropdownMenuTrigger,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
} from '@/components/ui/dropdown-menu'
import { Avatar, AvatarImage, AvatarFallback } from '@/components/ui/avatar'
import { Sheet, SheetContent, SheetTrigger, SheetClose } from '@/components/ui/sheet'
import { useRouter } from 'vue-router'
import { useToast } from '@/components/ui/toast'
import { useAuthStore } from '@/stores/auth'
import LogoutDialog from '@/components/ui/logout-dialog.vue'
import { canAccessRoute, routePermissionMap, clearPermissionCache, isAdmin } from '@/lib/permissionChecker'
import Sidebar from '@/components/Sidebar.vue'

const router = useRouter()
const toast = useToast()
const authStore = useAuthStore()
const isSidebarOpen = ref(false)
const isDropdownOpen = ref(false)
const showLogoutDialog = ref(false)
const visibleNavItems = ref<any[]>([])
const isLoadingPermissions = ref(true)

// Close dropdown on route change
watch(() => router.currentRoute.value.path, () => {
  isDropdownOpen.value = false
  showLogoutDialog.value = false
})

// Use auth store for user data
const userData = computed(() => {
  const defaultData = {
    name: 'Guest User',
    email: 'guest@example.com',
    role: 'Guest'
  }
  
  if (!authStore.user) {
    return defaultData
  }
  
  return {
    name: authStore.user.name || defaultData.name,
    email: authStore.user.email || defaultData.email,
    role: authStore.user.role || defaultData.role
  }
})

// Define all potential navigation items
const allNavigationItems = [
  { name: 'Dashboard', path: '/dashboard', icon: LayoutDashboard, requiresPermission: null },
  { name: 'Products', path: '/products', icon: Package, requiresPermission: 'view products' },
  { name: 'Orders', path: '/orders', icon: ShoppingCart, requiresPermission: 'view orders' },
  { name: 'Customers', path: '/customers', icon: Users, requiresPermission: 'view customers' },
  { name: 'Users', path: '/users', icon: User, requiresPermission: 'view users' },
  { name: 'Roles', path: '/roles', icon: Shield, requiresPermission: 'view roles' },
  { name: 'Permissions', path: '/permissions', icon: Key, requiresPermission: 'view permissions' },
  { name: 'User Roles', path: '/user-roles', icon: UserCog, requiresPermission: 'assign roles' },
]

// Function to load navigation items based on permissions
const loadNavigationItems = async () => {
  isLoadingPermissions.value = true
  try {
    // Check if user is admin first
    const userIsAdmin = await isAdmin();
    
    if (userIsAdmin) {
      // Admin users see all navigation items
      visibleNavItems.value = allNavigationItems;
      console.log('Admin user detected - showing all navigation items');
    } else {
      // For non-admin users, filter based on permissions
      const filteredItems = [];
      
      for (const item of allNavigationItems) {
        // If no permission required or user has permission, include it
        if (!item.requiresPermission || await canAccessRoute(item.path)) {
          filteredItems.push(item);
        }
      }
      
      visibleNavItems.value = filteredItems;
    }
  } catch (error) {
    console.error('Error loading navigation items:', error);
    // Fall back to showing just the dashboard if permission checks fail
    visibleNavItems.value = allNavigationItems.filter(item => !item.requiresPermission);
  } finally {
    isLoadingPermissions.value = false;
  }
}

const toggleSidebar = () => {
  isSidebarOpen.value = !isSidebarOpen.value
}

const handleLogout = async () => {
  try {
    showLogoutDialog.value = false
    await authStore.logout()
    // Clear permission cache on logout
    clearPermissionCache()
    toast.toast({
      title: 'Logged out successfully',
      description: 'You have been logged out of your account.',
    })
  } catch (error) {
    toast.toast({
      title: 'Error',
      description: 'Failed to complete logout process. You have been logged out locally.',
      variant: 'destructive',
    })
  }
}

// Get cookie helper
const getCookie = (name: string) => {
  const cookieValue = document.cookie
    .split('; ')
    .find(row => row.startsWith(`${name}=`))
  return cookieValue ? cookieValue.split('=')[1] : null
}

// Check authentication when component mounts
onMounted(async () => {
  // Explicitly ensure logout dialog is closed on mount
  showLogoutDialog.value = false
  
  try {
    // Try to initialize auth state
    await authStore.initAuth()
    
    // If still not authenticated after init, redirect
    if (!authStore.isAuthenticated && !getCookie('token')) {
      console.log('Not authenticated, redirecting to login')
      router.push('/auth/login')
      return
    }
    
    // Load navigation items after authentication
    await loadNavigationItems()
  } catch (error) {
    console.error('Auth check failed:', error)
    router.push('/auth/login')
  }
})
</script>

<template>
  <!-- Use the new LogoutDialog component -->
  <LogoutDialog v-model="showLogoutDialog" />

  <div class="relative min-h-screen bg-background">
    <!-- Mobile Sidebar -->
    <Sheet v-model:open="isSidebarOpen">
      <SheetTrigger>
        <Button variant="ghost" size="icon" class="md:hidden">
          <Menu class="h-5 w-5" />
          <span class="sr-only">Toggle menu</span>
        </Button>
      </SheetTrigger>
      <SheetContent side="left" class="w-[300px] sm:w-[400px]">
        <nav class="flex flex-col space-y-4">
          <div v-if="isLoadingPermissions" class="flex justify-center py-4">
            <div class="animate-spin h-5 w-5 border-2 border-primary rounded-full border-t-transparent"></div>
          </div>
          <router-link
            v-else
            v-for="item in visibleNavItems"
            :key="item.path"
            :to="item.path"
            class="flex items-center space-x-4 rounded-md px-3 py-2 text-sm font-medium transition-colors hover:bg-accent hover:text-accent-foreground"
            :class="{ 'bg-accent text-accent-foreground': $route.path === item.path }"
            @click="isSidebarOpen = false"
          >
            <component :is="item.icon" class="h-5 w-5" />
            <span>{{ item.name }}</span>
          </router-link>
        </nav>
        <SheetClose class="absolute right-4 top-4">
          <Button variant="ghost" size="icon">
            <span class="sr-only">Close</span>
            <X class="h-4 w-4" />
          </Button>
        </SheetClose>
      </SheetContent>
    </Sheet>

    <!-- Desktop Sidebar -->
    <aside
      class="fixed inset-y-0 left-0 z-30 hidden w-64 border-r bg-background md:block"
    >
      <Sidebar />
    </aside>

    <!-- Main Content -->
    <div class="md:pl-64">
      <!-- Header -->
      <header class="sticky top-0 z-40 flex h-16 items-center border-b bg-background/95 px-6 backdrop-blur supports-[backdrop-filter]:bg-background/60">
        <div class="ml-auto flex items-center gap-4">
          <DropdownMenu>
            <DropdownMenuTrigger>
              <Button variant="ghost" class="relative h-8 w-8 rounded-full">
                <Avatar>
                  <AvatarImage :src="`https://api.dicebear.com/7.x/avataaars/svg?seed=${userData?.email || 'default'}`" />
                  <AvatarFallback>{{ userData?.name?.charAt(0) || 'U' }}</AvatarFallback>
                </Avatar>
              </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent class="w-56">
              <DropdownMenuLabel class="font-normal">
                <div class="flex flex-col space-y-1">
                  <p class="text-sm font-medium leading-none">{{ userData?.name || 'User' }}</p>
                  <p class="text-xs leading-none text-muted-foreground">{{ userData?.email || 'No email' }}</p>
                </div>
              </DropdownMenuLabel>
              <DropdownMenuSeparator />
              <DropdownMenuItem>
                <router-link to="/profile" class="flex w-full items-center">
                  <User class="mr-2 h-4 w-4" />
                  <span>Profile</span>
                </router-link>
              </DropdownMenuItem>
              <DropdownMenuItem>
                <router-link to="/settings" class="flex w-full items-center">
                  <Settings class="mr-2 h-4 w-4" />
                  <span>Settings</span>
                </router-link>
              </DropdownMenuItem>
              <DropdownMenuSeparator />
              <DropdownMenuItem @click="showLogoutDialog = true">
                <div class="flex w-full items-center text-red-600">
                  <LogOut class="mr-2 h-4 w-4" />
                  <span>Log out</span>
                </div>
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