<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { RouterView } from 'vue-router'
import { ToastProvider } from '@/components/ui/toast'
import { useAuthStore } from '@/stores/auth'

const authStore = useAuthStore()
const appLoaded = ref(false)

// Initialize auth state
onMounted(async () => {
  try {
    await authStore.initAuth()
  } catch (error) {
    console.error('Failed to initialize auth:', error)
  } finally {
    appLoaded.value = true
  }
})
</script>

<template>
  <ToastProvider>
    <RouterView v-if="appLoaded" />
    <div v-else class="flex items-center justify-center h-screen">
      <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-primary"></div>
    </div>
  </ToastProvider>
</template> 