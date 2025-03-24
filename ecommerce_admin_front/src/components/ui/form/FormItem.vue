<script setup lang="ts">
import { cn } from '@/lib/utils'
import { computed } from 'vue'

interface Props {
  class?: string
}

const props = withDefaults(defineProps<Props>(), {
  class: ''
})

const id = computed(() => `form-item-${Math.random().toString(36).slice(2, 11)}`)
</script>

<template>
  <div :class="cn('space-y-2', props.class)" :id="id">
    <slot />
  </div>
</template>
