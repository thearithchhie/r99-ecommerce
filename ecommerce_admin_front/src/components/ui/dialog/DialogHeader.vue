<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { cn } from '@/lib/utils'

const props = defineProps<{
  class?: string
}>()
</script>

<template>
  <div :class="[
    'flex flex-col space-y-1.5 text-center sm:text-left',
    $props.class
  ]">
    <slot />
  </div>
</template>
