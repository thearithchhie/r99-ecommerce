import { defineComponent, h, Teleport, ref, watch, onMounted, onUnmounted } from 'vue'
import { cn } from '@/lib/utils'
import { X } from 'lucide-vue-next'

export const Dialog = defineComponent({
  name: 'Dialog',
  props: {
    open: {
      type: Boolean,
      default: false
    }
  },
  emits: ['update:open'],
  setup(props, { slots, emit }) {
    const isOpen = ref(props.open)

    watch(() => props.open, (value) => {
      isOpen.value = value
    })

    watch(isOpen, (value) => {
      emit('update:open', value)
    })

    return () => slots.default?.()
  }
})

export const DialogTrigger = defineComponent({
  name: 'DialogTrigger',
  setup(props, { slots, attrs }) {
    return () => h('button', {
      ...attrs,
      type: 'button',
    }, slots.default?.())
  }
})

export const DialogContent = defineComponent({
  name: 'DialogContent',
  props: {
    class: {
      type: String,
      default: ''
    }
  },
  emits: ['close'],
  setup(props, { slots, attrs, emit }) {
    const handleClose = () => {
      emit('close')
    }

    const handleBackdropClick = (e: MouseEvent) => {
      if (e.target === e.currentTarget) {
        handleClose()
      }
    }

    const handleEsc = (e: KeyboardEvent) => {
      if (e.key === 'Escape') {
        handleClose()
      }
    }

    onMounted(() => {
      document.addEventListener('keydown', handleEsc)
    })

    onUnmounted(() => {
      document.removeEventListener('keydown', handleEsc)
    })

    // Check if we're within a Dialog context
    // and only render when that Dialog is open
    return () => h(Teleport, { to: 'body' }, [
      h('div', {
        class: 'fixed inset-0 z-50 flex items-center justify-center bg-black/50',
        onClick: handleBackdropClick
      }, [
        h('div', {
          class: cn(
            'relative max-h-[85vh] w-full max-w-md rounded-lg bg-white p-6 shadow-lg',
            props.class
          ),
          ...attrs
        }, [
          h('button', {
            type: 'button',
            class: 'absolute right-4 top-4 rounded-sm opacity-70 ring-offset-background transition-opacity hover:opacity-100 focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:pointer-events-none',
            onClick: handleClose
          }, [
            h(X, { class: 'h-4 w-4' }),
            h('span', { class: 'sr-only' }, 'Close')
          ]),
          slots.default?.()
        ])
      ])
    ])
  }
})

export const DialogHeader = defineComponent({
  name: 'DialogHeader',
  props: {
    class: {
      type: String,
      default: ''
    }
  },
  setup(props, { slots, attrs }) {
    return () => h('div', {
      class: cn('flex flex-col space-y-1.5 text-center sm:text-left', props.class),
      ...attrs
    }, slots.default?.())
  }
})

export const DialogTitle = defineComponent({
  name: 'DialogTitle',
  props: {
    class: {
      type: String,
      default: ''
    }
  },
  setup(props, { slots, attrs }) {
    return () => h('h2', {
      class: cn('text-lg font-semibold leading-none tracking-tight', props.class),
      ...attrs
    }, slots.default?.())
  }
})

export const DialogDescription = defineComponent({
  name: 'DialogDescription',
  props: {
    class: {
      type: String,
      default: ''
    }
  },
  setup(props, { slots, attrs }) {
    return () => h('p', {
      class: cn('text-sm text-muted-foreground', props.class),
      ...attrs
    }, slots.default?.())
  }
})

export const DialogFooter = defineComponent({
  name: 'DialogFooter',
  props: {
    class: {
      type: String,
      default: ''
    }
  },
  setup(props, { slots, attrs }) {
    return () => h('div', {
      class: cn('flex flex-col-reverse sm:flex-row sm:justify-end sm:space-x-2', props.class),
      ...attrs
    }, slots.default?.())
  }
}) 