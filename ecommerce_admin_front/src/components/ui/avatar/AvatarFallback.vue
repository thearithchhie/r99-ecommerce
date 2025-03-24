<script setup lang="ts">
import { cn } from '@/lib/utils'
import { ref, onMounted, onUnmounted } from 'vue'

interface Props {
  class?: string
  delayMs?: number
}

const props = withDefaults(defineProps<Props>(), {
  class: '',
  delayMs: 600
})

const show = ref(false)

onMounted(() => {
  const timeout = setTimeout(() => {
    show.value = true
  }, props.delayMs)

  onUnmounted(() => {
    clearTimeout(timeout)
  })
})
</script>

<template>
  <div
    v-if="show"
    :class="cn('flex h-full w-full items-center justify-center rounded-full bg-muted', props.class)"
  >
    <slot />
  </div>
</template>
