import axios from 'axios'

// API base URL - dynamically use the current hostname for network compatibility
const getApiBaseUrl = () => {
  const hostname = window.location.hostname
  // If accessing from a remote device, use the network API URL
  if (hostname !== 'localhost' && hostname !== '127.0.0.1') {
    const networkUrl = import.meta.env.VITE_API_URL_NETWORK || 'http://192.168.1.124:8000/api/v1'
    return networkUrl
  }
  const localUrl = import.meta.env.VITE_API_URL || 'http://127.0.0.1:8000/api/v1'
  return localUrl
}

const API_URL = getApiBaseUrl()

// Create an axios instance
const api = axios.create({
  baseURL: API_URL,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'X-Requested-With': 'XMLHttpRequest' // This helps Laravel identify AJAX requests
  },
  // Important: Do NOT use withCredentials for APIs with different domains unless your backend is configured for it
  withCredentials: false
})

// Function to get cookie value
const getCookie = (name: string): string | null => {
  const nameEQ = name + "="
  const ca = document.cookie.split(';')
  for (let i = 0; i < ca.length; i++) {
    let c = ca[i]
    while (c.charAt(0) === ' ') c = c.substring(1, c.length)
    if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length)
  }
  return null
}

// Add request interceptor to include token in all requests
api.interceptors.request.use(config => {
  const token = getCookie('token')
  console.log('Request to:', config.url)
  console.log('Token found:', token ? 'Yes' : 'No')
  if (token) {
    config.headers['Authorization'] = `Bearer ${token}`
  }
  return config
}, error => {
  console.error('Request interceptor error:', error)
  return Promise.reject(error)
})

// Add response interceptor to handle common errors
api.interceptors.response.use(
  response => {
    console.log('API Response:', response.status, response.config.url)
    return response
  },
  error => {
    console.error('API Error:', error.message)
    
    if (error.response) {
      console.error('Response status:', error.response.status)
      console.error('Response data:', error.response.data)
    }
    
    // Handle 401 Unauthorized errors
    if (error.response && error.response.status === 401) {
      console.log('Unauthorized - clearing auth cookies and redirecting to login')
      // Clear auth cookies
      document.cookie = 'user=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;'
      document.cookie = 'token=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;'
      document.cookie = 'isAuthenticated=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;'
      
      // Redirect to login page
      window.location.href = '/auth/login'
    }
    return Promise.reject(error)
  }
)

// Auth API functions
export const authAPI = {
  login: async (email: string, password: string) => {
    try {
      const response = await api.post('/auth/login', { email, password })
      return response
    } catch (error: any) {
      throw error
    }
  },
  logout: async () => {
    try {
      const response = await api.post('/logout')
      return response
    } catch (error) {
      throw error
    }
  }
}

// User API functions
export const userAPI = {
  getCurrentUser: async () => {
    try {
      return await api.get('/user')
    } catch (error) {
      console.warn('Error getting current user from API, using fallback data')
      
      // Use fallback data
      const mockData = { 
        data: {
          name: 'Admin User',
          email: 'admin@example.com',
          role: 'Administrator'
        } 
      }
      return mockData
    }
  },
  updateProfile: async (data: any) => {
    return api.post('/user/profile', data)
  }
}

export default api 