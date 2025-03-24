<script setup lang="ts">
import type { HTMLAttributes } from 'vue'

interface Props {
  class?: HTMLAttributes['class']
}

const props = defineProps<Props>()

const emit = defineEmits<{
  (e: 'click'): void
}>()
</script>

<template>
  <button
    type="button"
    :class="props.class"
    @click="emit('click')"
  >
    <slot />
  </button>
</template>
