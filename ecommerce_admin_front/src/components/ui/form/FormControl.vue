<script setup lang="ts">
import { cn } from '@/lib/utils'

interface Props {
  class?: string
}

const props = withDefaults(defineProps<Props>(), {
  class: ''
})
</script>

<template>
  <div :class="cn('', props.class)">
    <slot />
  </div>
</template>
