<script setup lang="ts">
import { provide, ref } from 'vue'

const isOpen = ref(false)

provide('dropdown-open', isOpen)

defineExpose({
  isOpen
})
</script>

<template>
  <div class="relative">
    <slot :open="isOpen" />
  </div>
</template> 