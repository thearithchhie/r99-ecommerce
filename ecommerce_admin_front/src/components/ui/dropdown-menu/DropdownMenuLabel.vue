<script setup lang="ts">
import { cn } from '@/lib/utils'

defineProps({
  className: {
    type: String,
    default: ''
  }
})
</script>

<template>
  <div :class="cn('px-2 py-1.5 text-sm font-semibold', className)">
    <slot />
  </div>
</template> 