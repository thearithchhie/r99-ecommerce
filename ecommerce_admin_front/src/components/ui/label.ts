import { defineComponent, h } from 'vue'
import { cn } from '@/lib/utils'

export const Label = defineComponent({
  name: 'Label',
  props: {
    for: {
      type: String,
    },
    class: {
      type: String,
      default: '',
    },
  },
  setup(props, { slots, attrs }) {
    return () => h('label', {
      for: props.for,
      class: cn(
        'text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70',
        props.class
      ),
      ...attrs
    }, slots.default?.())
  },
}) 