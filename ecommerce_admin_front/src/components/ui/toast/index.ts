import { inject } from 'vue'

export interface ToastProps {
  title?: string
  description?: string
  action?: {
    label: string
    onClick: () => void
  }
  variant?: 'default' | 'destructive' | 'success'
  duration?: number
}

export interface ToastAPI {
  toast: (props: ToastProps) => string
  dismiss: (id: string) => void
}

export function useToast(): ToastAPI {
  const toast = inject('toast') as ToastAPI
  
  if (!toast) {
    throw new Error('useToast() must be used within a ToastProvider component')
  }
  
  return toast
}

// Direct imports without type checking to avoid module resolution errors
export { default as ToastProvider } from './ToastProvider.vue'
export { default as Toast } from './Toast.vue' 