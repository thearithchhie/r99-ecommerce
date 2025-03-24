<script setup lang="ts">
import { cn } from '@/lib/utils'
import { X } from 'lucide-vue-next'
import type { HTMLAttributes } from 'vue'

interface Props {
  class?: HTMLAttributes['class']
}

const props = defineProps<Props>()

const emit = defineEmits<{
  (e: 'close'): void
}>()
</script>

<template>
  <div :class="cn('p-6', props.class)">
    <slot />
    <button
      class="absolute right-4 top-4 rounded-sm opacity-70 ring-offset-background transition-opacity hover:opacity-100 focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:pointer-events-none data-[state=open]:bg-accent data-[state=open]:text-muted-foreground"
      @click="emit('close')"
    >
      <X class="h-4 w-4" />
      <span class="sr-only">Close</span>
    </button>
  </div>
</template>
