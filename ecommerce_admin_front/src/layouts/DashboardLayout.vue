<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { Menu, ChevronDown, User, Settings, LogOut, LayoutDashboard, Package, ShoppingCart, Users, X } from 'lucide-vue-next'
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
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from '@/components/ui/dialog'
import { useRouter } from 'vue-router'
import { useToast } from '@/components/ui/toast'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const toast = useToast()
const authStore = useAuthStore()
const isSidebarOpen = ref(false)
const isDropdownOpen = ref(false)
const showLogoutDialog = ref(false)

// Close dropdown on route change
watch(() => router.currentRoute.value.path, () => {
  isDropdownOpen.value = false
})

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

const handleLogout = async () => {
  try {
    await authStore.logout()
    showLogoutDialog.value = false
    router.push('/login')
    toast.toast({
      title: 'Logged out successfully',
      description: 'You have been logged out of your account.',
    })
  } catch (error) {
    toast.toast({
      title: 'Error',
      description: 'Failed to logout. Please try again.',
      variant: 'destructive',
    })
  }
}
</script>

<template>
  <Dialog v-model:open="showLogoutDialog">
    <DialogContent class="sm:max-w-[425px]">
      <DialogHeader>
        <DialogTitle>Are you sure you want to logout?</DialogTitle>
        <DialogDescription class="pt-2">
          This will end your current session. You will need to login again to access your account.
        </DialogDescription>
      </DialogHeader>
      <DialogFooter class="mt-4">
        <Button variant="outline" @click="showLogoutDialog = false">Cancel</Button>
        <Button variant="destructive" @click="handleLogout">Logout</Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>

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
          <router-link
            v-for="item in navigationItems"
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
    <div class="md:pl-64">
      <!-- Header -->
      <header class="sticky top-0 z-40 flex h-16 items-center border-b bg-background/95 px-6 backdrop-blur supports-[backdrop-filter]:bg-background/60">
        <div class="ml-auto flex items-center gap-4">
          <DropdownMenu>
            <DropdownMenuTrigger>
              <Button variant="ghost" class="relative h-8 w-8 rounded-full">
                <Avatar>
                  <AvatarImage :src="`https://api.dicebear.com/7.x/avataaars/svg?seed=${userData.email}`" />
                  <AvatarFallback>{{ userData.name.charAt(0) }}</AvatarFallback>
                </Avatar>
              </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent class="w-56">
              <DropdownMenuLabel class="font-normal">
                <div class="flex flex-col space-y-1">
                  <p class="text-sm font-medium leading-none">{{ userData.name }}</p>
                  <p class="text-xs leading-none text-muted-foreground">{{ userData.email }}</p>
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