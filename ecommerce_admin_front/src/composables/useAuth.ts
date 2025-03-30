import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useToast } from '@/components/ui/toast'
import { getCsrfToken } from '@/utils/csrf'

export function useAuth() {
  const authStore = useAuthStore()
  const router = useRouter()
  const { toast } = useToast()
  const isInitialized = ref(false)
  
  // Computed properties
  const isAuthenticated = computed(() => authStore.isAuthenticated)
  const user = computed(() => authStore.user)
  const isLoading = computed(() => authStore.isLoading)
  const error = computed(() => authStore.error)
  
  // Methods
  const login = async (email: string, password: string) => {
    try {
      // Ensure we have a CSRF token first
      try {
        await getCsrfToken()
      } catch (error) {
        console.error('Failed to get CSRF token:', error)
        toast({
          title: 'Warning',
          description: 'CSRF protection issue. Your request may fail.',
          variant: 'warning'
        })
        // Continue anyway to attempt login
      }
      
      // Attempt login
      await authStore.login(email, password)
      
      toast({
        title: 'Success',
        description: 'Login successful',
      })
      
      return true
    } catch (error: any) {
      console.error('Login error:', error)
      
      toast({
        title: 'Login Failed',
        description: error.response?.data?.message || error.message || 'Authentication failed',
        variant: 'destructive'
      })
      
      return false
    }
  }
  
  const logout = async () => {
    try {
      await authStore.logout()
      toast({
        title: 'Success',
        description: 'You have been logged out successfully',
      })
      router.push('/auth/login')
      return true
    } catch (error) {
      console.error('Logout error:', error)
      toast({
        title: 'Error',
        description: 'Failed to logout. Please try again.',
        variant: 'destructive'
      })
      return false
    }
  }
  
  const redirectIfNotAuthenticated = () => {
    if (!isAuthenticated.value) {
      console.log('Not authenticated, redirecting to login')
      router.push({ name: 'login' })
      return true
    }
    return false
  }
  
  // Method to initialize authentication
  const initAuth = async () => {
    if (isInitialized.value) return isAuthenticated.value
    
    try {
      // Try to authenticate with stored credentials
      const result = await authStore.initAuth()
      isInitialized.value = true
      return result
    } catch (err) {
      console.error('Error initializing auth:', err)
      return false
    }
  }
  
  // Auto-initialize on mount if in a component
  onMounted(() => {
    initAuth()
  })
  
  return {
    isAuthenticated,
    user,
    isLoading,
    error,
    isInitialized,
    redirectIfNotAuthenticated,
    initAuth,
    login,
    logout
  }
} 