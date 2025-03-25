import axios from 'axios'

// Base axios instance for API calls
const instance = axios.create({
  baseURL: import.meta.env.VITE_API_URL || 'http://127.0.0.1:8000/api/v1',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'Accept-Language': 'en'
  },
  withCredentials: true // Important for handling cookies/session
})


// Get cookie helper
const getCookie = (name: string) => {
  const cookieValue = document.cookie
    .split('; ')
    .find(row => row.startsWith(`${name}=`))
  return cookieValue ? cookieValue.split('=')[1] : null
}

// Add request interceptor for tokens and auth
instance.interceptors.request.use(config => {
  // Add CSRF token
  const csrfToken = getCookie('XSRF-TOKEN')
  if (csrfToken) {
    config.headers['X-XSRF-TOKEN'] = decodeURIComponent(csrfToken)
  }
  
  // Add Bearer token from cookies if it exists
  const authToken = getCookie('token')
  if (authToken) {
    config.headers['Authorization'] = `Bearer ${authToken}`
  }
  
  return config
})

export default instance; 