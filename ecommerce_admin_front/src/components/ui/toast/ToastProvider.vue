<script setup lang="ts">
import { ref, provide, onMounted, onUnmounted } from 'vue'
import Toast from './Toast.vue'

export interface ToastProps {
  id?: string
  title?: string
  description?: string
  action?: {
    label: string
    onClick: () => void
  }
  variant?: 'default' | 'destructive' | 'success'
  duration?: number
}

const toasts = ref<ToastProps[]>([])

const createToast = (props: ToastProps) => {
  const id = Math.random().toString(36).substring(2, 9)
  const toast = {
    id,
    title: props.title,
    description: props.description,
    action: props.action,
    variant: props.variant || 'default',
    duration: props.duration || 5000,
  }
  
  toasts.value = [toast, ...toasts.value]
  
  // Remove the toast after the specified duration
  setTimeout(() => {
    dismissToast(id)
  }, toast.duration)
  
  return id
}

const dismissToast = (id: string) => {
  toasts.value = toasts.value.filter(toast => toast.id !== id)
}

provide('toast', {
  toast: createToast,
  dismiss: dismissToast
})
</script>

<template>
  <div class="fixed inset-0 flex flex-col items-end px-4 py-6 pointer-events-none sm:p-6 z-50 gap-2">
    <Toast
      v-for="toast in toasts"
      :key="toast.id"
      :id="toast.id"
      :title="toast.title"
      :description="toast.description"
      :action="toast.action"
      :variant="toast.variant"
      @dismiss="dismissToast(toast.id)"
    />
  </div>
  <slot></slot>
</template> 