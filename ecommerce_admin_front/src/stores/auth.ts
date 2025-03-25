import { defineStore } from 'pinia'
import { ref } from 'vue'
import router from '@/router'
import axios from '@/lib/axios'

export interface User {
  name: string;
  email: string;
  role: string;
}

export const useAuthStore = defineStore('auth', () => {
  const user = ref<User | null>(null)
  const isAuthenticated = ref(false)
  const isLoading = ref(false)
  const error = ref<string | null>(null)

  // Cookie helpers
  const setCookie = (name: string, value: string, days = 7) => {
    const date = new Date()
    date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000))
    const expires = `expires=${date.toUTCString()}`
    document.cookie = `${name}=${value};${expires};path=/;SameSite=Strict`
  }

  const getCookie = (name: string) => {
    const cookieValue = document.cookie
      .split('; ')
      .find(row => row.startsWith(`${name}=`))
    return cookieValue ? cookieValue.split('=')[1] : null
  }

  const deleteCookie = (name: string) => {
    document.cookie = `${name}=;expires=Thu, 01 Jan 1970 00:00:00 UTC;path=/;`
  }

  // Initialize auth state
  const initAuth = async () => {
    try {
      const response = await axios.get('/user')
      user.value = response.data
      isAuthenticated.value = true
    } catch (error) {
      console.log('Not authenticated or error fetching user data')
      user.value = null
      isAuthenticated.value = false
      
      // Check if we're on a protected route and redirect to login if needed
      const currentRoute = router.currentRoute.value
      if (currentRoute.meta.requiresAuth || 
          currentRoute.path.startsWith('/dashboard') || 
          currentRoute.path === '/') {
        router.push('/auth/login')
      }
    }
  }

  // Login function
  const login = async (email: string, password: string) => {
    isLoading.value = true
    error.value = null
    
    try {
      // Call the API login endpoint
      const response = await axios.post('/auth/login', {
        email,
        password
      })
      
      // Handle API response
      const { user: userData, token } = response.data
      
      // Save token to cookie
      if (token) {
        setCookie('token', token)
      }
      
      user.value = userData
      isAuthenticated.value = true
      
      console.log('Login successful')
      router.push('/dashboard')
    } catch (err: any) {
      console.error('Login error:', err)
      error.value = err.response?.data?.message || 'Failed to log in'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  // Logout function
  const logout = async () => {
    isLoading.value = true
    try {
      // Get token from cookie
      const token = getCookie('token')
      
      // Try to call the backend logout endpoint with token
      try {
        await axios.post('/auth/logout', {}, {
          headers: {
            'Authorization': `Bearer ${token}`
          }
        })
      } catch (e) {
        console.error('Backend logout failed, but proceeding with client logout', e)
      }
      
      // Always clear local state regardless of API success
      clearAuthData()
      router.push('/auth/login')
    } catch (error) {
      console.error('Logout processing error:', error)
      // Still clear auth data and redirect
      clearAuthData()
      router.push('/auth/login')
    } finally {
      isLoading.value = false
    }
  }

  // Clear auth data
  const clearAuthData = () => {
    user.value = null
    isAuthenticated.value = false
    
    // Clear auth cookies
    deleteCookie('token')
    deleteCookie('XSRF-TOKEN')
    deleteCookie('laravel_session')
  }

  return {
    user,
    isAuthenticated,
    isLoading,
    error,
    initAuth,
    login,
    logout,
    clearAuthData
  }
}) 