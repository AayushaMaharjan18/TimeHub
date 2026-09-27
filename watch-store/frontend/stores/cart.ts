import { defineStore } from 'pinia'
import type { CartItem, Product } from '~/types'

const STORAGE_KEY = 'cart_items'

export const useCartStore = defineStore('cart', {
  state: () => ({
    items: [] as CartItem[],
  }),

  getters: {
    totalItems: (state) => state.items.reduce((sum, item) => sum + item.quantity, 0),
    subtotal: (state) => state.items.reduce((sum, item) => sum + (item.product.final_price * item.quantity), 0),
    isEmpty: (state) => state.items.length === 0,
    quantityOf: (state) => (productId: number) => state.items.find(i => i.product_id === productId)?.quantity ?? 0,
  },

  actions: {
    hydrate() {
      if (!import.meta.client) return
      try {
        const raw = localStorage.getItem(STORAGE_KEY)
        const parsed = raw ? JSON.parse(raw) : []
        this.items = Array.isArray(parsed) ? parsed.filter((i: CartItem) => i?.product?.id) : []
      } catch {
        this.items = []
      }
    },

    persist() {
      if (!import.meta.client) return
      try {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(this.items))
      } catch {
        // Storage full or blocked — the in-memory cart still works for this visit.
      }
    },

    /** Adds to the cart, capped at available stock. Returns the quantity actually added. */
    addItem(product: Product, quantity: number = 1): number {
      const max = Math.max(0, product.stock_quantity ?? Infinity)
      const existing = this.items.find(i => i.product_id === product.id)
      const current = existing?.quantity ?? 0
      const added = Math.max(0, Math.min(quantity, max - current))
      if (added === 0) return 0

      if (existing) {
        existing.quantity += added
        existing.product = product
      } else {
        this.items.push({ id: product.id, product_id: product.id, quantity: added, product })
      }
      this.persist()
      return added
    },

    updateQuantity(productId: number, quantity: number) {
      const item = this.items.find(i => i.product_id === productId)
      if (!item) return
      const max = item.product.stock_quantity ?? Infinity
      item.quantity = Math.max(1, Math.min(quantity, max))
      this.persist()
    },

    removeItem(productId: number) {
      this.items = this.items.filter(i => i.product_id !== productId)
      this.persist()
    },

    clearCart() {
      this.items = []
      this.persist()
    },
  },
})
