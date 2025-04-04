<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { Button } from '@/components/ui/button'
import { Eye, EyeOff, LogIn, AlertCircle } from 'lucide-vue-next'
import { useAuthStore } from '@/stores/auth'
import { useToast } from '@/components/ui/toast'
import apiClient, { csrfClient, refreshCsrfToken } from '@/utils/axios'

const router = useRouter()
const authStore = useAuthStore()
const { toast } = useToast()

const email = ref('')
const password = ref('')
const showPassword = ref(false)
const rememberMe = ref(false)
const apiStatus = ref('Checking API connection...')

// Check API health on mount
onMounted(async () => {
  checkApiHealth()
})

// Check if API is reachable
const checkApiHealth = async () => {
  try {
    apiStatus.value = 'Checking API connection...'
    
    const response = await apiClient.get('/health-check')
    console.log('API health check successful:', response.data)
    
    apiStatus.value = 'API connected ✓'
    
    return true
  } catch (error: any) {
    console.error('API health check error:', error.message)
    apiStatus.value = `API connection error: ${error.message}`
    
    return false
  }
}

// Test CSRF token
const testCsrf = async () => {
  try {
    const result = await refreshCsrfToken()
    
    if (result) {
      toast({
        title: 'CSRF Test Successful',
        description: 'CSRF token has been refreshed'
      })
    } else {
      toast({
        title: 'CSRF Test Failed',
        description: 'Could not refresh CSRF token',
        variant: 'destructive'
      })
    }
  } catch (error: any) {
    console.error('CSRF test error:', error)
    
    toast({
      title: 'CSRF Test Error',
      description: error.message,
      variant: 'destructive'
    })
  }
}

const toggleShowPassword = () => {
  showPassword.value = !showPassword.value
}

const handleLogin = async () => {
  // Basic form validation
  if (!email.value || !password.value) {
    toast({
      title: 'Error',
      description: 'Please fill in all fields',
      variant: 'destructive'
    })
    return
  }
  
  // Simple email validation
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  if (!emailRegex.test(email.value)) {
    toast({
      title: 'Error',
      description: 'Please enter a valid email address',
      variant: 'destructive'
    })
    return
  }
  
  try {
    // First ensure we have a CSRF token
    await refreshCsrfToken()
    
    // Attempt to login
    await authStore.login(email.value, password.value)
    
    toast({
      title: 'Success',
      description: 'You have been logged in successfully',
    })
  } catch (err: any) {
    console.error('Login error:', err)
    toast({
      title: 'Error',
      description: authStore.error || 'Failed to login. Please try again.',
      variant: 'destructive'
    })
  }
}
</script>

<template>
  <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
    <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
      <div class="mb-6 text-center">
        <h2 class="text-3xl font-extrabold text-gray-900">Admin Login</h2>
        <p class="mt-2 text-sm text-gray-600">
          Sign in to your account to access the admin dashboard
        </p>
        <p class="mt-1 text-xs" :class="apiStatus.includes('error') ? 'text-red-600' : 'text-green-600'">
          {{ apiStatus }}
        </p>
      </div>
      
      <form @submit.prevent="handleLogin" class="space-y-6">
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
              placeholder="password"
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
            :disabled="authStore.isLoading"
          >
            <LogIn v-if="!authStore.isLoading" class="mr-2 h-4 w-4" />
            <svg v-if="authStore.isLoading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            {{ authStore.isLoading ? 'Signing in...' : 'Sign in' }}
          </Button>
        </div>
      </form>
      
      <!-- Debug tools -->
      <div class="mt-6 pt-4 border-t border-gray-200">
        <div class="flex justify-center space-x-4">
          <button 
            type="button" 
            @click="checkApiHealth"
            class="px-2 py-1 text-xs border border-gray-300 rounded hover:bg-gray-50 transition-colors"
          >
            Test API Connection
          </button>
          <button 
            type="button" 
            @click="testCsrf"
            class="px-2 py-1 text-xs border border-gray-300 rounded hover:bg-gray-50 transition-colors"
          >
            Test CSRF Token
          </button>
        </div>
      </div>
    </div>
  </div>
</template>