<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { Button } from '@/components/ui/button'
import { Eye, EyeOff, LogIn, AlertCircle } from 'lucide-vue-next'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const authStore = useAuthStore()
const email = ref('')
const password = ref('')
const showPassword = ref(false)
const loading = ref(false)
const error = ref('')
const rememberMe = ref(false)

const toggleShowPassword = () => {
  showPassword.value = !showPassword.value
}

const handleLogin = async () => {
  // Reset error state
  error.value = ''
  
  // Basic form validation
  if (!email.value || !password.value) {
    error.value = 'Please fill in all fields'
    return
  }
  
  // Simple email validation
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  if (!emailRegex.test(email.value)) {
    error.value = 'Please enter a valid email address'
    return
  }
  
  try {
    loading.value = true
    console.log('Attempting login with:', { email: email.value })
    
    // Use auth store login function
    await authStore.login(email.value, password.value)
    
    // Redirect to dashboard
    router.push('/dashboard')
  } catch (err: any) {
    if (err.response && err.response.data) {
      error.value = err.response.data.message || 'Authentication failed'
    } else {
      error.value = 'An error occurred during sign in'
    }
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
    <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
      <!-- Error message -->
      <div v-if="error" class="mb-4 rounded-md bg-red-50 p-4">
        <div class="flex items-center">
          <AlertCircle class="h-5 w-5 text-red-400" />
          <div class="ml-3">
            <h3 class="text-sm font-medium text-red-800">{{ error }}</h3>
          </div>
        </div>
      </div>
      
      <form @submit.prevent="handleLogin()" class="space-y-6">
        <div>
          <label for="email" class="block text-sm font-medium text-gray-700">Email address</label>
          <div class="mt-1">
            <input
              id="email"
              v-model="email"
              name="email"
              type="email"
              autocomplete="email"
              required
              class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm"
              placeholder="admin@example.com"
            />
          </div>
        </div>

        <div>
          <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
          <div class="mt-1 relative">
            <input
              id="password"
              v-model="password"
              name="password"
              :type="showPassword ? 'text' : 'password'"
              autocomplete="current-password"
              required
              class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm pr-10"
              placeholder="password123"
            />
            <button 
              type="button" 
              @click="toggleShowPassword" 
              class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-500"
            >
              <Eye v-if="!showPassword" class="h-5 w-5" />
              <EyeOff v-else class="h-5 w-5" />
              <span class="sr-only">{{ showPassword ? 'Hide password' : 'Show password' }}</span>
            </button>
          </div>
        </div>

        <div class="flex items-center justify-between">
          <div class="flex items-center">
            <input
              id="remember-me"
              v-model="rememberMe"
              name="remember-me"
              type="checkbox"
              class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded"
            />
            <label for="remember-me" class="ml-2 block text-sm text-gray-900">
              Remember me
            </label>
          </div>

          <div class="text-sm">
            <a href="#" class="font-medium text-primary hover:text-primary-foreground">
              Forgot your password?
            </a>
          </div>
        </div>

        <div>
          <Button 
            type="submit" 
            class="w-full flex justify-center items-center"
            :disabled="loading"
          >
            <LogIn v-if="!loading" class="mr-2 h-4 w-4" />
            <svg v-if="loading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>{{ loading ? 'Signing in...' : 'Sign in' }}</span>
          </Button>
        </div>
      </form>
      
    </div>
  </div>
</template>