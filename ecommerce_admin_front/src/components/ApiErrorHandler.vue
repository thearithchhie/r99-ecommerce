<template>
  <div v-if="showError" class="fixed bottom-0 inset-x-0 z-50">
    <div class="bg-amber-50 border-t border-amber-200 p-2">
      <div class="container mx-auto flex items-center justify-between">
        <div class="flex items-center space-x-2">
          <AlertCircle class="h-5 w-5 text-amber-500" />
          <p class="text-sm text-amber-800">
            {{ errorMessage || 'There was an issue connecting to the API' }}
          </p>
        </div>
        <button 
          @click="dismiss"
          class="text-amber-800 hover:text-amber-900"
        >
          <X class="h-4 w-4" />
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted, watch } from 'vue'
import { AlertCircle, X } from 'lucide-vue-next'
import { useRouter } from 'vue-router'
import apiClient from '@/utils/axios'

const router = useRouter()
const showError = ref(false)
const errorMessage = ref('')
const checkInterval = ref<number | null>(null)

// Check API health
const checkApiHealth = async () => {
  try {
    await apiClient.get('/health-check')
    if (showError.value) {
      showError.value = false
    }
    return true
  } catch (error: any) {
    showError.value = true
    errorMessage.value = error.message || 'API connection issue'
    return false
  }
}

// Start periodic health checks
const startHealthChecks = () => {
  // Initial check
  checkApiHealth()
  
  // Setup periodic checks
  checkInterval.value = window.setInterval(() => {
    checkApiHealth()
  }, 30000) // Check every 30 seconds
}

// Stop health checks
const stopHealthChecks = () => {
  if (checkInterval.value !== null) {
    clearInterval(checkInterval.value)
    checkInterval.value = null
  }
}

// Manually dismiss the error
const dismiss = () => {
  showError.value = false
}

// Watch for route changes to reset error state
watch(() => router.currentRoute.value.path, () => {
  // We could optionally dismiss errors on route change
  // showError.value = false
})

onMounted(() => {
  startHealthChecks()
})

// Cleanup on component unmount
onUnmounted(() => {
  stopHealthChecks()
})
</script> 