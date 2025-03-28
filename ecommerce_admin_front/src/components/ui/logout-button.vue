<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { LogOut } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
import { useAuthStore } from '@/stores/auth'
import { useToast } from '@/components/ui/toast'

// Define props for customizing the button
const props = defineProps({
  variant: {
    type: String as () => 'default' | 'destructive' | 'outline' | 'secondary' | 'ghost' | 'link',
    default: 'default',
  },
  size: {
    type: String as () => 'default' | 'sm' | 'lg' | 'icon',
    default: 'default',
  },
  showIcon: {
    type: Boolean,
    default: true
  },
  label: {
    type: String,
    default: 'Log out'
  }
})

const authStore = useAuthStore()
const toast = useToast()
const localLoading = ref(false)

// Watch for changes in auth store loading state
watch(() => authStore.isLoading, (newVal, oldVal) => {
  // If loading ends in the store, ensure our local loading state is eventually reset
  if (oldVal && !newVal) {
    setTimeout(() => {
      localLoading.value = false
    }, 500)
  }
})

const handleLogout = async () => {
  // Use local loading state to prevent button spam even if store loading state resets early
  if (localLoading.value) return
  
  localLoading.value = true
  
  try {
    // Attempt to logout using the auth store
    // Note: The auth store will handle redirection
    await authStore.logout()
    
    // We shouldn't reach this point normally, but just in case...
    setTimeout(() => {
      // If we're still here after 2 seconds, force a redirect
      if (document.cookie.indexOf('isAuthenticated=true') === -1) {
        window.location.href = '/auth/login'
      }
    }, 2000)
    
  } catch (error) {
    // Show error toast
    toast.toast({
      title: 'Logout Warning',
      description: authStore.error || 'Something went wrong during logout',
      variant: 'destructive',
    })
    
    // Reset local loading state in case of error
    setTimeout(() => {
      localLoading.value = false
      
      // Force redirect even on error if auth cookie is cleared
      if (document.cookie.indexOf('isAuthenticated=true') === -1) {
        window.location.href = '/auth/login'
      }
    }, 1000)
  }
}
</script>

<template>
  <Button 
    :variant="props.variant" 
    :size="props.size"
    @click="handleLogout" 
    :disabled="localLoading"
    class="logout-button"
  >
    <div v-if="localLoading" class="h-4 w-4 animate-spin rounded-full border-2 border-current border-t-transparent mr-2"></div>
    <LogOut v-else-if="showIcon" class="mr-2 h-4 w-4" />
    <span>{{ localLoading ? 'Logging out...' : label }}</span>
  </Button>
</template> 