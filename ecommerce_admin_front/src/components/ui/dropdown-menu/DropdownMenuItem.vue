<script setup lang="ts">
import { inject } from 'vue'
import { cn } from '@/lib/utils'

defineProps({
  disabled: {
    type: Boolean,
    default: false
  },
  className: {
    type: String,
    default: ''
  }
})

const open = inject('dropdown-open', null)

const closeDropdown = () => {
  if (open) {
    open.value = false
  }
}
</script>

<template>
  <button
    :class="cn(
      'flex w-full items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900 cursor-pointer',
      disabled && 'pointer-events-none opacity-50',
      className
    )"
    :disabled="disabled"
    @click="closeDropdown"
  >
    <slot />
  </button>
</template> 