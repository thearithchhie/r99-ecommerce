<script setup lang="ts">
import { computed } from 'vue'

interface Props {
  open?: boolean
  modal?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  modal: true
})

const emit = defineEmits<{
  (e: 'update:open', value: boolean): void
}>()

const isOpen = computed({
  get: () => props.open,
  set: (value) => emit('update:open', value)
})
</script>

<template>
  <Teleport to="body">
    <div v-if="isOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <!-- Backdrop -->
      <div class="fixed inset-0 bg-black bg-opacity-50" @click="isOpen = false" />
      <!-- Dialog content -->
      <div class="relative z-50 bg-white rounded-lg shadow-xl max-w-md w-full">
        <slot />
      </div>
    </div>
  </Teleport>
</template>
