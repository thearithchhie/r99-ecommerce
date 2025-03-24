<script setup lang="ts">
import { onMounted, computed } from 'vue'
import { RouterView } from 'vue-router'
import { ToastProvider } from '@/components/ui/toast'
import { useAuthStore } from '@/stores/auth'

const authStore = useAuthStore()

// Initialize auth state
onMounted(() => {
  authStore.initAuth()
})

const userName = computed(() => authStore.userName)
const userEmail = computed(() => authStore.userEmail)
const userRole = computed(() => authStore.userRole)
</script>

<template>
  <ToastProvider>
    <RouterView />
  </ToastProvider>
</template> 