<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
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
const authStore = useAuthStore()
const toast = useToast()
const showDialog = ref(true)

const handleConfirm = async () => {
  try {
    await authStore.logout()
    toast.toast({
      title: 'Success',
      description: 'You have been successfully logged out.',
    })
  } catch (error) {
    toast.toast({
      title: 'Error',
      description: 'Failed to logout. Please try again.',
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
        <Button variant="outline" @click="handleCancel">Cancel</Button>
        <Button variant="destructive" @click="handleConfirm">Yes, Logout</Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template> 