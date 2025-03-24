import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import router from '@/router'

export interface User {
  name: string;
  email: string;
  role: string;
}

export const useAuthStore = defineStore('auth', () => {
  // Default user data
  const defaultUser: User = {
    name: 'Admin User',
    email: 'admin@example.com',
    role: 'Administrator'
  }

  const user = ref<User | null>(null)
  const isAuthenticated = ref(false)
  const isLoading = ref(false)
  const error = ref<string | null>(null)

  // Computed properties for user data with default values
  const userName = computed(() => user.value?.name || defaultUser.name)
  const userEmail = computed(() => user.value?.email || defaultUser.email)
  const userRole = computed(() => user.value?.role || defaultUser.role)

  // Initialize auth state
  const initAuth = () => {
    try {
      const savedUser = localStorage.getItem('user')
      const savedAuth = localStorage.getItem('isAuthenticated')
      
      if (savedUser && savedAuth === 'true') {
        const parsedUser = JSON.parse(savedUser)
        // Ensure all required fields are present
        user.value = {
          name: parsedUser.name || defaultUser.name,
          email: parsedUser.email || defaultUser.email,
          role: parsedUser.role || defaultUser.role
        }
        isAuthenticated.value = true
      } else {
        // Use default user data
        user.value = { ...defaultUser }
        isAuthenticated.value = true
        // Save initial state
        localStorage.setItem('user', JSON.stringify(user.value))
        localStorage.setItem('isAuthenticated', 'true')
      }
      console.log('Auth initialized with user:', user.value)
    } catch (error) {
      console.error('Error initializing auth:', error)
      // Fallback to default user
      user.value = { ...defaultUser }
      isAuthenticated.value = true
      localStorage.setItem('user', JSON.stringify(user.value))
      localStorage.setItem('isAuthenticated', 'true')
    }
  }

  // Login function
  const login = async (email: string, password: string) => {
    isLoading.value = true
    error.value = null
    
    try {
      console.log('Logging in with email:', email)
      await new Promise(resolve => setTimeout(resolve, 1000))
      
      const newUser = {
        ...defaultUser,
        email: email
      }
      
      user.value = newUser
      isAuthenticated.value = true
      
      // Save to localStorage
      localStorage.setItem('user', JSON.stringify(newUser))
      localStorage.setItem('isAuthenticated', 'true')
      
      console.log('Login successful, user data:', user.value)
      router.push('/dashboard')
    } catch (err: any) {
      error.value = 'Failed to log in'
      console.error('Login failed:', err)
      throw err
    } finally {
      isLoading.value = false
    }
  }

  // Logout function
  const logout = async () => {
    isLoading.value = true
    try {
      console.log('Logging out...')
      await new Promise(resolve => setTimeout(resolve, 500))
      clearAuthData()
      localStorage.removeItem('user')
      localStorage.removeItem('isAuthenticated')
      router.push('/auth/login')
    } finally {
      isLoading.value = false
    }
  }

  // Clear auth data
  const clearAuthData = () => {
    console.log('Clearing auth data')
    user.value = null
    isAuthenticated.value = false
  }

  // Update user info with validation
  const updateUserInfo = (userData: Partial<User>) => {
    if (!user.value) return
    
    console.log('Updating user info:', userData)
    
    user.value = {
      ...user.value,
      ...userData
    }

    // Save updated user to localStorage
    localStorage.setItem('user', JSON.stringify(user.value))
    console.log('User info updated:', user.value)
  }

  return {
    user,
    isAuthenticated,
    isLoading,
    error,
    userName,
    userEmail,
    userRole,
    initAuth,
    login,
    logout,
    clearAuthData,
    updateUserInfo
  }
}) 