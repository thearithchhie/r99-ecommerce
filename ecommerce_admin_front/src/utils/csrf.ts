import { refreshCsrfToken } from './axios'

/**
 * Get CSRF token from Laravel Sanctum
 * This function calls Laravel's CSRF token endpoint to set the XSRF-TOKEN cookie
 */
export const getCsrfToken = (): string | null => {
  const value = `; ${document.cookie}`
  const parts = value.split(`; XSRF-TOKEN=`)
  if (parts.length === 2) {
    const token = parts.pop()?.split(';').shift()
    return token ? decodeURIComponent(token) : null
  }
  return null
}

/**
 * Gets the current CSRF token from cookies
 */
export const getCurrentCsrfToken = (): string | null => {
  const csrfCookie = document.cookie
    .split('; ')
    .find(row => row.startsWith('XSRF-TOKEN='))
  
  if (csrfCookie) {
    return decodeURIComponent(csrfCookie.split('=')[1])
  }
  
  return null
}

/**
 * Ensures a CSRF token exists before making a request
 * This is useful for operations that require CSRF protection
 */
export const ensureCsrfToken = async (): Promise<boolean> => {
  const currentToken = getCsrfToken()
  
  if (!currentToken) {
    console.log('No CSRF token found, fetching a new one')
    return await refreshCsrfToken()
  } else {
    console.log('Using existing CSRF token')
    return true
  }
} 