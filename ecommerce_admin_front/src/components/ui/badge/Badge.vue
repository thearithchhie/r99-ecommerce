<script setup lang="ts">
import { computed } from 'vue'
import { cn } from '@/lib/utils'

interface Props {
  variant?: 'default' | 'secondary' | 'destructive' | 'outline' | 'success' | 'warning'
}

const props = withDefaults(defineProps<Props>(), {
  variant: 'default'
})

const variantStyles = computed(() => {
  switch (props.variant) {
    case 'default':
      return 'bg-primary text-primary-foreground hover:bg-primary/80'
    case 'secondary':
      return 'bg-secondary text-secondary-foreground hover:bg-secondary/80'
    case 'destructive':
      return 'bg-destructive text-destructive-foreground hover:bg-destructive/80'
    case 'outline':
      return 'text-foreground border border-input hover:bg-accent hover:text-accent-foreground'
    case 'success':
      return 'bg-emerald-100 text-emerald-700'
    case 'warning':
      return 'bg-amber-100 text-amber-700'
    default:
      return ''
  }
})

const classes = computed(() => {
  return cn(
    'inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2',
    variantStyles.value
  )
})
</script>

<template>
  <div :class="classes">
    <slot />
  </div>
</template> 