<script setup lang="ts">
import { cn } from '@/lib/utils'
import type { HTMLAttributes } from 'vue'

interface Props {
  class?: HTMLAttributes['class']
}

const props = defineProps<Props>()
</script>

<template>
  <h2
    :class="cn(
      'text-lg font-semibold leading-none tracking-tight',
      props.class
    )"
  >
    <slot />
  </h2>
</template>
