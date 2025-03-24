<script setup lang="ts">
import { computed } from 'vue'
import { buttonVariants } from '.'
import type { ButtonHTMLAttributes } from 'vue'

interface Props {
  variant?: 'default' | 'destructive' | 'outline' | 'secondary' | 'ghost' | 'link'
  size?: 'default' | 'sm' | 'lg' | 'icon'
  asChild?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  variant: 'default',
  size: 'default',
  asChild: false,
})

const buttonClasses = computed(() => {
  return buttonVariants({ variant: props.variant, size: props.size })
})
</script>

<template>
  <button :class="buttonClasses">
    <slot />
  </button>
</template> 