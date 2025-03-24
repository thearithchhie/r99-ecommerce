<script setup lang="ts">
import { X } from 'lucide-vue-next'
import { computed } from 'vue'

const props = defineProps<{
  id: string
  title?: string
  description?: string
  action?: {
    label: string
    onClick: () => void
  }
  variant?: 'default' | 'destructive' | 'success'
}>()

const emit = defineEmits(['dismiss'])

const variantClasses = computed(() => {
  switch (props.variant) {
    case 'destructive':
      return 'bg-red-50 border-red-200 text-red-900'
    case 'success':
      return 'bg-green-50 border-green-200 text-green-900'
    default:
      return 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700'
  }
})

const handleDismiss = () => {
  emit('dismiss')
}
</script>

<template>
  <div
    class="w-full max-w-sm overflow-hidden rounded-lg shadow-lg pointer-events-auto border transform transition-all duration-500 ease-in-out animate-enter"
    :class="variantClasses"
  >
    <div class="p-4">
      <div class="flex items-start">
        <div class="flex-1">
          <h3 class="text-sm font-medium" v-if="title">{{ title }}</h3>
          <p
            class="mt-1 text-sm"
            :class="title ? 'text-gray-500 dark:text-gray-400' : ''"
            v-if="description"
          >
            {{ description }}
          </p>
        </div>
        <div class="ml-4 flex flex-shrink-0">
          <button
            @click="handleDismiss"
            class="inline-flex text-gray-400 focus:outline-none focus:text-gray-500 hover:text-gray-500"
          >
            <span class="sr-only">Close</span>
            <X class="h-4 w-4" />
          </button>
        </div>
      </div>
      <div class="mt-3 flex gap-3 text-sm" v-if="action">
        <button
          @click="action.onClick"
          class="font-medium text-blue-600 hover:text-blue-500 dark:text-blue-400 dark:hover:text-blue-300"
        >
          {{ action.label }}
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.animate-enter {
  animation: slideIn 0.2s ease;
}

@keyframes slideIn {
  from {
    transform: translateY(100%);
    opacity: 0;
  }
  to {
    transform: translateY(0);
    opacity: 1;
  }
}
</style> 