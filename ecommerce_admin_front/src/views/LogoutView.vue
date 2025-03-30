<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuth } from '@/composables/useAuth'
import { useToast } from '@/components/ui/toast'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog'
import { Button } from '@/components/ui/button'

const router = useRouter()
const { logout, isLoading } = useAuth()
const { toast } = useToast()
const showDialog = ref(true)

const handleConfirm = async () => {
  try {
    const result = await logout()
    if (result) {
      toast({
        title: 'Success',
        description: 'You have been successfully logged out.',
      })
    } else {
      toast({
        title: 'Warning',
        description: 'Logout process encountered an issue, but you have been logged out.',
        variant: 'destructive',
      })
    }
  } catch (error: any) {
    console.error('Logout error:', error)
    toast({
      title: 'Error',
      description: error.message || 'Failed to logout. Please try again.',
      variant: 'destructive',
    })
  }
}

const handleCancel = () => {
  router.back()
}
</script>

<template>
  <Dialog :open="showDialog" @update:open="handleCancel">
    <DialogContent>
      <DialogHeader>
        <DialogTitle>Confirm Logout</DialogTitle>
        <DialogDescription>
          Are you sure you want to logout? You'll need to login again to access your account.
        </DialogDescription>
      </DialogHeader>
      <DialogFooter>
        <Button variant="outline" @click="handleCancel" :disabled="isLoading">Cancel</Button>
        <Button 
          variant="destructive" 
          @click="handleConfirm"
          :disabled="isLoading"
        >
          <svg v-if="isLoading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          {{ isLoading ? 'Logging out...' : 'Yes, Logout' }}
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template> 