<script setup lang="ts">
import { inject, onMounted, ref } from 'vue'
import { onClickOutside } from '@vueuse/core'
import { cn } from '@/lib/utils'

const props = defineProps({
  align: {
    type: String,
    default: 'center',
    validator: (value: string) => ['start', 'center', 'end'].includes(value)
  },
  forceMount: {
    type: Boolean,
    default: false
  },
  className: {
    type: String,
    default: ''
  },
  width: {
    type: String,
    default: 'w-56'
  }
})

const isOpen = inject('dropdown-open', ref(false))
const contentRef = ref(null)

onMounted(() => {
  if (contentRef.value) {
    onClickOutside(contentRef, () => {
      isOpen.value = false
    })
  }
})

const alignClasses = {
  start: 'left-0',
  center: 'left-1/2 -translate-x-1/2',
  end: 'right-0'
}

const classes = cn(
  'absolute top-full z-50 mt-2 overflow-hidden rounded-md border border-border bg-background shadow-lg',
  props.width,
  alignClasses[props.align as keyof typeof alignClasses],
  props.className
)
</script>

<template>
  <div v-if="isOpen || forceMount" :class="classes" ref="contentRef">
    <div class="py-1">
      <slot />
    </div>
  </div>
</template> 