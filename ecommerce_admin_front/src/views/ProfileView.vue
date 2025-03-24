<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useToast } from '@/components/ui/toast'
import { User, Mail } from 'lucide-vue-next'

const authStore = useAuthStore()
const toast = useToast()

// Form data initialized from auth store
const formData = ref({
  name: authStore.userName,
  email: authStore.userEmail,
  role: authStore.userRole
})

// Loading and error states
const isLoading = ref(false)
const error = ref<string | null>(null)

// Check if any form data has changed
const hasChanges = computed(() => {
  return (
    formData.value.name !== authStore.userName ||
    formData.value.email !== authStore.userEmail
  )
})

// Reset form to match auth store
const resetForm = () => {
  console.log('Resetting form to match auth store values')
  formData.value = {
    name: authStore.userName,
    email: authStore.userEmail,
    role: authStore.userRole
  }
  error.value = null
}

// Update profile
const updateProfile = async () => {
  console.log('Updating profile with:', formData.value)
  isLoading.value = true
  error.value = null

  try {
    // Update user info in auth store
    authStore.updateUserInfo({
      name: formData.value.name,
      email: formData.value.email
    })

    toast.toast({
      title: 'Success',
      description: 'Profile updated successfully',
      variant: 'default',
    })

    // Reset form to match new auth store values
    resetForm()
  } catch (err: any) {
    console.error('Profile update failed:', err)
    error.value = err.message || 'Failed to update profile'
    toast.toast({
      title: 'Error',
      description: error.value,
      variant: 'destructive',
    })
  } finally {
    isLoading.value = false
  }
}

// Initialize form data
onMounted(() => {
  console.log('ProfileView mounted, initializing form data')
  resetForm()
})
</script>

<template>
  <div>
    <div class="mb-8">
      <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">My Profile</h1>
      <p class="text-gray-600 dark:text-gray-400">Manage your personal information</p>
    </div>
    
    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700">
      <div class="p-6 border-b border-gray-200 dark:border-gray-700">
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">Personal Information</h2>
        <p class="text-sm text-gray-600 dark:text-gray-400">Update your profile details</p>
      </div>
      
      <div v-if="isLoading" class="p-6 flex justify-center">
        <div class="flex flex-col items-center space-y-2">
          <div class="h-8 w-8 animate-spin rounded-full border-4 border-blue-600 border-t-transparent"></div>
          <p class="text-sm text-gray-600 dark:text-gray-400">Loading profile data...</p>
        </div>
      </div>
      
      <form v-else @submit.prevent="updateProfile" class="p-6 space-y-6">
        <div v-if="error" class="rounded-md bg-red-50 dark:bg-red-900/30 p-4 text-red-600 dark:text-red-400 text-sm">
          {{ error }}
        </div>
        
        <!-- Profile Picture -->
        <div class="flex flex-col sm:flex-row sm:items-center gap-6">
          <div class="flex-shrink-0">
            <div class="h-24 w-24 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-user-icon h-12 w-12 text-blue-600 dark:text-blue-400">
                <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
                <circle cx="12" cy="7" r="4"></circle>
              </svg>
            </div>
          </div>
          
          <div class="space-y-1">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ authStore.userName }}</h3>
            <p class="text-sm text-gray-600 dark:text-gray-400">{{ authStore.userRole }}</p>
          </div>
        </div>
        
        <!-- Profile Form -->
        <div class="grid gap-6 sm:grid-cols-2">
          <div class="space-y-2">
            <label for="name" class="text-sm font-medium text-gray-900 dark:text-gray-100">Name</label>
            <div class="relative">
              <User class="absolute left-3 top-2.5 h-4 w-4 text-gray-500 dark:text-gray-400" />
              <input
                id="name"
                v-model="formData.name"
                type="text"
                class="w-full pl-10 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-transparent"
                placeholder="Your name"
              />
            </div>
          </div>
          
          <div class="space-y-2">
            <label for="email" class="text-sm font-medium text-gray-900 dark:text-gray-100">Email</label>
            <div class="relative">
              <Mail class="absolute left-3 top-2.5 h-4 w-4 text-gray-500 dark:text-gray-400" />
              <input
                id="email"
                v-model="formData.email"
                type="email"
                class="w-full pl-10 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-transparent"
                placeholder="your.email@example.com"
              />
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
</template> 