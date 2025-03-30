<script setup lang="ts">
import { onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { refreshCsrfToken } from '@/utils/axios'
import { useAuth } from '@/composables/useAuth'
import { Toaster } from '@/components/ui/toast'
import ApiErrorHandler from '@/components/ApiErrorHandler.vue'

const router = useRouter()
const route = useRoute()
const { initAuth, isAuthenticated } = useAuth()

onMounted(async () => {
  console.log('App mounted - initializing auth and CSRF token')
  
  try {
    // First ensure we have a CSRF token
    await refreshCsrfToken()
    
    // Initialize authentication
    const authResult = await initAuth()
    
    // Handle route redirection based on auth state
    const currentRouteName = route.name as string
    
    // If user is not authenticated and trying to access a protected route
    if (!isAuthenticated.value && 
        route.meta.requiresAuth && 
        currentRouteName !== 'login') {
      console.log('Redirecting to login from protected route:', currentRouteName)
      router.push({ name: 'login' })
    }
    
    // If user is authenticated and trying to access login page
    if (isAuthenticated.value && currentRouteName === 'login') {
      console.log('Redirecting to dashboard from login page')
      router.push({ name: 'dashboard' })
    }
  } catch (error) {
    console.error('Error during app initialization:', error)
  }
})
</script>

<template>
  <div>
    <RouterView />
    <Toaster />
    <ApiErrorHandler />
  </div>
</template> 