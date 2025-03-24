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
  <div :class="cn('-mx-1 my-1 h-px bg-muted', className)"></div>
</template> 