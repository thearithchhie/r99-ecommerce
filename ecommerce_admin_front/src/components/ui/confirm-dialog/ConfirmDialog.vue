<script setup lang="ts">
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog'
import { Button } from '@/components/ui/button'

interface Props {
  isOpen: boolean
  title: string
  description: string
  confirmText?: string
  confirmVariant?: 'default' | 'destructive'
}

const props = withDefaults(defineProps<Props>(), {
  confirmText: 'Confirm',
  confirmVariant: 'destructive'
})

const emit = defineEmits<{
  (e: 'confirm'): void
  (e: 'cancel'): void
}>()
</script>

<template>
  <Dialog :open="isOpen" @update:open="emit('cancel')">
    <DialogContent>
      <DialogHeader>
        <DialogTitle>{{ title }}</DialogTitle>
        <DialogDescription>
          {{ description }}
        </DialogDescription>
      </DialogHeader>
      <DialogFooter class="mt-4">
        <Button variant="outline" @click="emit('cancel')">Cancel</Button>
        <Button :variant="confirmVariant" @click="emit('confirm')">{{ confirmText }}</Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template> 