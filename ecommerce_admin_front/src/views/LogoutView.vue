<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useToast } from '@/components/ui/toast'

const authStore = useAuthStore()
const toast = useToast()
const isLoading = ref(true)
const message = ref('Please wait while we log you out of your account')

// Automatically logout when the page loads
onMounted(async () => {
  isLoading.value = true
  
  try {
    // Update message before attempting logout
    message.value = 'Logging out of your account...'
    
    // Manual timeout to ensure message updates before logout process
    await new Promise(resolve => setTimeout(resolve, 300))
    
    // Perform the logout (note: this will redirect in the auth store)
    await authStore.logout()
    
    // We shouldn't reach this point as redirect should happen in auth store
    message.value = 'Logout successful! Redirecting to login...'
    
    // Backup redirect if store redirect fails
    setTimeout(() => {
      window.location.href = '/auth/login'
    }, 1000)
    
  } catch (error) {
    message.value = 'Error during logout. Redirecting anyway...'
    
    toast.toast({
      title: 'Logout Warning',
      description: authStore.error || 'Something went wrong during logout',
      variant: 'destructive',
    })
    
    // Redirect even on error after a short delay
    setTimeout(() => {
      window.location.href = '/auth/login'
    }, 1000)
  }
})
</script>

<template>
  <div class="flex flex-col items-center justify-center min-h-screen p-4 bg-background">
    <div class="w-full max-w-md space-y-8 text-center">
      <div class="flex flex-col items-center justify-center space-y-2">
        <div class="h-12 w-12 rounded-full bg-primary/10 flex items-center justify-center">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            width="24"
            height="24"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
            class="text-primary"
          >
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
            <polyline points="16 17 21 12 16 7"></polyline>
            <line x1="21" y1="12" x2="9" y2="12"></line>
          </svg>
        </div>
        <h1 class="text-2xl font-bold tracking-tight">Logging Out</h1>
        <p class="text-sm text-muted-foreground">
          {{ message }}
        </p>
      </div>
      <div class="flex justify-center">
        <div class="h-8 w-8 animate-spin rounded-full border-4 border-primary border-t-transparent"></div>
      </div>
    </div>
  </div>
</template> 