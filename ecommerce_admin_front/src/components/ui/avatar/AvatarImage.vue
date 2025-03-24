<script setup lang="ts">
import { cn } from '@/lib/utils'

interface Props {
  class?: string
  src?: string
  alt?: string
}

const props = withDefaults(defineProps<Props>(), {
  class: '',
  alt: ''
})

const emit = defineEmits<{
  error: [event: Event]
}>()
</script>

<template>
  <img
    :src="props.src"
    :alt="props.alt"
    :class="cn('aspect-square h-full w-full', props.class)"
    @error="emit('error', $event)"
  />
</template>
