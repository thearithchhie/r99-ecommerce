import { defineComponent, h } from 'vue'
import { cn } from '@/lib/utils'

export const Card = defineComponent({
  name: 'Card',
  props: {
    class: {
      type: String,
      default: '',
    },
  },
  setup(props, { slots, attrs }) {
    return () => h('div', {
      class: cn('rounded-lg border bg-card text-card-foreground shadow-sm', props.class),
      ...attrs
    }, slots.default?.())
  },
})

export const CardHeader = defineComponent({
  name: 'CardHeader',
  props: {
    class: {
      type: String,
      default: '',
    },
  },
  setup(props, { slots, attrs }) {
    return () => h('div', {
      class: cn('flex flex-col space-y-1.5 p-6', props.class),
      ...attrs
    }, slots.default?.())
  },
})

export const CardTitle = defineComponent({
  name: 'CardTitle',
  props: {
    class: {
      type: String,
      default: '',
    },
    as: {
      type: String,
      default: 'h3',
    },
  },
  setup(props, { slots, attrs }) {
    return () => h(props.as, {
      class: cn('text-2xl font-semibold leading-none tracking-tight', props.class),
      ...attrs
    }, slots.default?.())
  },
})

export const CardDescription = defineComponent({
  name: 'CardDescription',
  props: {
    class: {
      type: String,
      default: '',
    },
  },
  setup(props, { slots, attrs }) {
    return () => h('p', {
      class: cn('text-sm text-muted-foreground', props.class),
      ...attrs
    }, slots.default?.())
  },
})

export const CardContent = defineComponent({
  name: 'CardContent',
  props: {
    class: {
      type: String,
      default: '',
    },
  },
  setup(props, { slots, attrs }) {
    return () => h('div', {
      class: cn('p-6 pt-0', props.class),
      ...attrs
    }, slots.default?.())
  },
})

export const CardFooter = defineComponent({
  name: 'CardFooter',
  props: {
    class: {
      type: String,
      default: '',
    },
  },
  setup(props, { slots, attrs }) {
    return () => h('div', {
      class: cn('flex items-center p-6 pt-0', props.class),
      ...attrs
    }, slots.default?.())
  },
}) 