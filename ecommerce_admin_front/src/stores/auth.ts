import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import apiClient, { refreshCsrfToken } from '@/utils/axios'
import router from '@/router'

export interface User {
  id: number
  name: string
  email: string
  role: string
}

export const useAuthStore = defineStore('auth', () => {
  const user = ref<User | null>(null)
  const isAuthenticated = computed(() => !!user.value)
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
      isLoading.value = true
      error.value = null
      
      // First get a CSRF token if we don't have one
      await refreshCsrfToken()
      
      // Check if user is already logged in
      const response = await apiClient.get('/user')
      
      if (response.data && response.status === 200) {
        user.value = response.data
        return true
      }
      
      return false
    } catch (err: any) {
      console.error('Init auth error:', err)
      // If we get a 401, it means we're not authenticated
      if (err.response && (err.response.status === 401 || err.response.status === 419)) {
        return false
      }
      
      error.value = err.message || 'Failed to initialize authentication'
      return false
    } finally {
      isLoading.value = false
    }
  }

  // Login function
  const login = async (email: string, password: string) => {
    try {
      isLoading.value = true
      error.value = null
      
      // First ensure we have a CSRF token
      await refreshCsrfToken()
      
      // Attempt login
      const response = await apiClient.post('/login', {
        email,
        password
      })
      
      // If we get here, login was successful
      user.value = response.data.user
      
      // Redirect to dashboard after successful login
      router.push({ name: 'dashboard' })
      
      return true
    } catch (err: any) {
      console.error('Login error:', err)
      
      if (err.response && err.response.data && err.response.data.message) {
        error.value = err.response.data.message
      } else {
        error.value = err.message || 'Failed to login'
      }
      
      throw err
    } finally {
      isLoading.value = false
    }
  }

  // Fetch user profile
  const fetchUserProfile = async () => {
    try {
      const token = getCookie('token')
      if (!token) {
        throw new Error('No authentication token found')
      }
      
      const response = await apiClient.get('/user-profile', {
        headers: {
          'Authorization': `Bearer ${token}`
        }
      })
      
      user.value = response.data
      return response.data
    } catch (error) {
      console.error('Error fetching user profile:', error)
      throw error
    }
  }

  // Logout function
  const logout = async () => {
    try {
      isLoading.value = true
      error.value = null
      
      // Call logout endpoint
      await apiClient.post('/logout')
      
      // Clear user data
      user.value = null
      
      // Redirect to login
      router.push({ name: 'login' })
      
      return true
    } catch (err: any) {
      console.error('Logout error:', err)
      
      // Even if there's an error, we should clear the user data
      user.value = null
      
      error.value = err.message || 'Failed to logout'
      
      // Redirect to login anyway
      router.push({ name: 'login' })
      
      return false
    } finally {
      isLoading.value = false
    }
  }

  // Clear auth data
  const clearAuthData = () => {
    user.value = null
    
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