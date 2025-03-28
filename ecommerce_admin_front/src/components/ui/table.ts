import { defineComponent, h } from 'vue'
import { cn } from '@/lib/utils'

export const Table = defineComponent({
  name: 'Table',
  props: {
    class: {
      type: String,
      default: '',
    },
  },
  setup(props, { slots, attrs }) {
    return () => h('div', {
      class: cn('w-full overflow-auto', props.class)
    }, h('table', {
      class: 'w-full caption-bottom text-sm',
      ...attrs
    }, slots.default?.()))
  },
})

export const TableHeader = defineComponent({
  name: 'TableHeader',
  props: {
    class: {
      type: String,
      default: '',
    },
  },
  setup(props, { slots, attrs }) {
    return () => h('thead', {
      class: cn('[&_tr]:border-b', props.class),
      ...attrs
    }, slots.default?.())
  },
})

export const TableBody = defineComponent({
  name: 'TableBody',
  props: {
    class: {
      type: String,
      default: '',
    },
  },
  setup(props, { slots, attrs }) {
    return () => h('tbody', {
      class: cn('[&_tr:last-child]:border-0', props.class),
      ...attrs
    }, slots.default?.())
  },
})

export const TableFooter = defineComponent({
  name: 'TableFooter',
  props: {
    class: {
      type: String,
      default: '',
    },
  },
  setup(props, { slots, attrs }) {
    return () => h('tfoot', {
      class: cn('bg-primary text-primary-foreground font-medium', props.class),
      ...attrs
    }, slots.default?.())
  },
})

export const TableRow = defineComponent({
  name: 'TableRow',
  props: {
    class: {
      type: String,
      default: '',
    },
  },
  setup(props, { slots, attrs }) {
    return () => h('tr', {
      class: cn('border-b transition-colors hover:bg-muted/50 data-[state=selected]:bg-muted', props.class),
      ...attrs
    }, slots.default?.())
  },
})

export const TableHead = defineComponent({
  name: 'TableHead',
  props: {
    class: {
      type: String,
      default: '',
    },
  },
  setup(props, { slots, attrs }) {
    return () => h('th', {
      class: cn('h-12 px-4 text-left align-middle font-medium text-muted-foreground [&:has([role=checkbox])]:pr-0', props.class),
      ...attrs
    }, slots.default?.())
  },
})

export const TableCell = defineComponent({
  name: 'TableCell',
  props: {
    class: {
      type: String,
      default: '',
    },
  },
  setup(props, { slots, attrs }) {
    return () => h('td', {
      class: cn('p-4 align-middle [&:has([role=checkbox])]:pr-0', props.class),
      ...attrs
    }, slots.default?.())
  },
})

export const TableCaption = defineComponent({
  name: 'TableCaption',
  props: {
    class: {
      type: String,
      default: '',
    },
  },
  setup(props, { slots, attrs }) {
    return () => h('caption', {
      class: cn('mt-4 text-sm text-muted-foreground', props.class),
      ...attrs
    }, slots.default?.())
  },
}) 