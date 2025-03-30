import axios from 'axios'
import router from '@/router'

// Get cookies helper function
const getCookie = (name: string): string | null => {
  const value = `; ${document.cookie}`
  const parts = value.split(`; ${name}=`)
  if (parts.length === 2) return parts.pop()?.split(';').shift() || null
  return null
}

// Main API client for /api requests
const apiClient = axios.create({
  baseURL: '/api/v1',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'Accept-Language': 'en'
  },
  withCredentials: true // Important for cookies
})

// CSRF client specifically for /sanctum endpoint without the /api/v1 prefix
export const csrfClient = axios.create({
  baseURL: '',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  },
  withCredentials: true
})

// Request interceptor - add auth token to requests
apiClient.interceptors.request.use(
  (config) => {
    // Add token from localStorage or cookie to headers
    const token = localStorage.getItem('auth_token') || getCookie('token')
    
    if (token) {
      config.headers['Authorization'] = `Bearer ${token}`
    }
    
    // Add XSRF-TOKEN to headers if it exists
    const xsrfToken = getCookie('XSRF-TOKEN')
    if (xsrfToken) {
      config.headers['X-XSRF-TOKEN'] = xsrfToken
    }
    
    return config
  },
  (error) => {
    console.error('API Request Error:', error)
    return Promise.reject(error)
  }
)

// Response interceptor - handle authentication errors
apiClient.interceptors.response.use(
  (response) => response,
  (error) => {
    console.error('API Response Error:', error.response?.status, error.message)
    
    // Handle auth errors (401, 419)
    if (error.response && ([401, 419].includes(error.response.status))) {
      // Clear auth data
      localStorage.removeItem('auth_token')
      
      // Redirect to login if not already there
      if (router.currentRoute.value.name !== 'login') {
        console.log('Authentication required, redirecting to login')
        router.push('/auth/login')
      }
    }
    
    return Promise.reject(error)
  }
)

// Get CSRF token from Laravel Sanctum
export const refreshCsrfToken = async (): Promise<boolean> => {
  try {
    const response = await csrfClient.get('/sanctum/csrf-cookie', {
      withCredentials: true
    })
    
    console.log('CSRF token refreshed', response.status)
    return true
  } catch (error) {
    console.error('Failed to refresh CSRF token:', error)
    return false
  }
}

export default apiClient 