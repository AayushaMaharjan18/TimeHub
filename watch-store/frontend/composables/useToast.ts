import { ref } from 'vue'

export type ToastType = 'success' | 'error' | 'info'

export interface Toast {
  id: number
  type: ToastType
  message: string
  action?: { label: string; to: string }
}

// Module-level so every component shares one queue (rendered by <AppToaster />).
const toasts = ref<Toast[]>([])
let nextId = 1

export const useToast = () => {
  const dismiss = (id: number) => {
    toasts.value = toasts.value.filter(t => t.id !== id)
  }

  const show = (message: string, type: ToastType = 'info', action?: Toast['action'], duration = 3500) => {
    const id = nextId++
    toasts.value.push({ id, type, message, action })
    if (import.meta.client) setTimeout(() => dismiss(id), duration)
    return id
  }

  return {
    toasts,
    dismiss,
    success: (message: string, action?: Toast['action']) => show(message, 'success', action),
    error: (message: string) => show(message, 'error', undefined, 5000),
    info: (message: string) => show(message, 'info'),
  }
}
