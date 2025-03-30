<template>
  <div class="h-full flex flex-col bg-primary text-primary-foreground w-64">
    <!-- Sidebar Header -->
    <div class="p-4 flex items-center">
      <div class="rounded-full bg-primary-foreground/10 p-2 mr-2">
        <Box class="h-6 w-6" />
      </div>
      <div class="font-bold text-xl">Admin Panel</div>
    </div>
    
    <nav class="flex-1 overflow-y-auto">
      <div class="px-3 py-2">
        <!-- Dashboard Link -->
        <router-link 
          :to="{ name: 'dashboard' }" 
          class="flex items-center h-10 px-3 rounded-md text-sm transition-colors hover:bg-primary-foreground/10"
          :class="{ 'bg-primary-foreground/20': isExactActive('dashboard') }"
        >
          <LayoutDashboard class="h-4 w-4 mr-2" />
          <span>Dashboard</span>
        </router-link>
      </div>
      
      <!-- User Management Section -->
      <div class="px-3 py-2">
        <h3 class="text-xs font-medium uppercase tracking-wider pl-3 py-2">User Management</h3>
        
        <!-- Users Link -->
        <router-link 
          :to="{ name: 'users' }" 
          class="flex items-center h-10 px-3 rounded-md text-sm transition-colors hover:bg-primary-foreground/10"
          :class="{ 'bg-primary-foreground/20': isActive('/users') }"
        >
          <Users class="h-4 w-4 mr-2" />
          <span>Users</span>
        </router-link>
        
        <!-- Roles & Permissions Dropdown -->
        <div class="relative">
          <button 
            @click="toggleDropdown('rolesOpen')"
            class="flex items-center justify-between w-full h-10 px-3 rounded-md text-sm transition-colors hover:bg-primary-foreground/10"
            :class="{ 'bg-primary-foreground/20': isActive('/roles') }"
          >
            <div class="flex items-center">
              <ShieldCheck class="h-4 w-4 mr-2" />
              <span>Roles & Permissions</span>
            </div>
            <ChevronDown 
              class="h-4 w-4 transition-transform duration-200"
              :class="{ 'transform rotate-180': rolesOpen }"
            />
          </button>
          
          <!-- Dropdown Content -->
          <div 
            v-if="rolesOpen" 
            class="pl-8 pt-1 pb-2 space-y-1"
          >
            <router-link 
              :to="{ name: 'roles' }" 
              class="flex items-center h-8 px-3 rounded-md text-sm hover:bg-primary-foreground/10"
              :class="{ 'bg-primary-foreground/10': isExactActive('roles') }"
            >
              Roles
            </router-link>
            <router-link 
              :to="{ name: 'permissions' }" 
              class="flex items-center h-8 px-3 rounded-md text-sm hover:bg-primary-foreground/10"
              :class="{ 'bg-primary-foreground/10': isExactActive('permissions') }"
            >
              Permissions
            </router-link>
            <router-link 
              :to="{ name: 'user-roles' }" 
              class="flex items-center h-8 px-3 rounded-md text-sm hover:bg-primary-foreground/10"
              :class="{ 'bg-primary-foreground/10': isExactActive('user-roles') }"
            >
              User Roles
            </router-link>
          </div>
        </div>
      </div>
      
      <!-- E-commerce Section -->
      <div class="px-3 py-2">
        <h3 class="text-xs font-medium uppercase tracking-wider pl-3 py-2">E-commerce</h3>
        
        <!-- Products Dropdown -->
        <div class="relative">
          <button 
            @click="toggleDropdown('productsOpen')"
            class="flex items-center justify-between w-full h-10 px-3 rounded-md text-sm transition-colors hover:bg-primary-foreground/10"
            :class="{ 'bg-primary-foreground/20': isActive('/products') }"
          >
            <div class="flex items-center">
              <Box class="h-4 w-4 mr-2" />
              <span>Products</span>
            </div>
            <ChevronDown 
              class="h-4 w-4 transition-transform duration-200"
              :class="{ 'transform rotate-180': productsOpen }"
            />
          </button>
          
          <!-- Dropdown Content -->
          <div 
            v-if="productsOpen" 
            class="pl-8 pt-1 pb-2 space-y-1"
          >
            <router-link 
              :to="{ name: 'products' }" 
              class="flex items-center h-8 px-3 rounded-md text-sm hover:bg-primary-foreground/10"
              :class="{ 'bg-primary-foreground/10': isExactActive('products') }"
            >
              All Products
            </router-link>
            <router-link 
              :to="{ name: 'product-create' }" 
              class="flex items-center h-8 px-3 rounded-md text-sm hover:bg-primary-foreground/10" 
              :class="{ 'bg-primary-foreground/10': isExactActive('product-create') }"
            >
              Add New Product
            </router-link>
          </div>
        </div>
        
        <!-- Categories Link -->
        <router-link 
          :to="{ name: 'categories' }"
          class="flex items-center h-10 px-3 rounded-md text-sm transition-colors hover:bg-primary-foreground/10"
          :class="{ 'bg-primary-foreground/20': isExactActive('categories') }"
        >
          <FolderOpen class="h-4 w-4 mr-2" />
          <span>Categories</span>
        </router-link>
        
        <!-- Brands Link -->
        <router-link
          :to="{ name: 'brands' }"
          class="flex items-center h-10 px-3 rounded-md text-sm transition-colors hover:bg-primary-foreground/10"
          :class="{ 'bg-primary-foreground/20': isExactActive('brands') }"
        >
          <Tag class="h-4 w-4 mr-2" />
          <span>Brands</span>
        </router-link>
        
        <!-- Inventory Link -->
        <a 
          href="#" 
          @click.prevent="notImplemented" 
          class="flex items-center h-10 px-3 rounded-md text-sm transition-colors hover:bg-primary-foreground/10"
        >
          <Warehouse class="h-4 w-4 mr-2" />
          <span>Inventory</span>
        </a>
      </div>
      
      <!-- Settings Section -->
      <div class="px-3 py-2">
        <h3 class="text-xs font-medium uppercase tracking-wider pl-3 py-2">Settings</h3>
        
        <!-- Profile Link -->
        <router-link 
          :to="{ name: 'profile' }" 
          class="flex items-center h-10 px-3 rounded-md text-sm transition-colors hover:bg-primary-foreground/10"
          :class="{ 'bg-primary-foreground/20': isExactActive('profile') }"
        >
          <Key class="h-4 w-4 mr-2" />
          <span>Profile</span>
        </router-link>
        
        <!-- Logout Link -->
        <a 
          href="#" 
          @click.prevent="logout" 
          class="flex items-center h-10 px-3 rounded-md text-sm transition-colors hover:bg-primary-foreground/10"
        >
          <LogOut class="h-4 w-4 mr-2" />
          <span>Logout</span>
        </a>
      </div>
    </nav>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { 
  LayoutDashboard, 
  Users, 
  ShieldCheck, 
  Box, 
  FolderOpen, 
  Tag, 
  Warehouse, 
  Key, 
  LogOut,
  ChevronDown
} from 'lucide-vue-next'
import { useAuthStore } from '@/stores/auth'
import { useToast } from '@/components/ui/toast'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
  DropdownMenuSeparator
} from '@/components/ui/dropdown-menu'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()
const { toast } = useToast()

const isActive = (path: string) => {
  return route.path.includes(path)
}

const isExactActive = (name: string) => {
  return route.name === name
}

const productsOpen = ref(isActive('/products'))
const rolesOpen = ref(isActive('/roles'))

const toggleDropdown = (dropdownRef: string) => {
  if (dropdownRef === 'productsOpen') {
    productsOpen.value = !productsOpen.value
  } else if (dropdownRef === 'rolesOpen') {
    rolesOpen.value = !rolesOpen.value
  }
}

const notImplemented = () => {
  toast({
    title: "Information",
    description: "This feature is not implemented yet.",
    variant: "default",
  })
}

const logout = () => {
  authStore.logout()
  router.push({ name: 'login' })
}
</script>

<style scoped>
/* Add any additional styling as needed */
</style> 