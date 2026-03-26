import { defineStore } from 'pinia'
import api from '../utils/api'

export const useCartStore = defineStore('cart', {
  state: () => ({
    items: [],
    loaded: false,
  }),
  getters: {
    count: (state) => state.items.reduce((sum, item) => sum + item.quantity, 0),
  },
  actions: {
    async fetchCart() {
      try {
        const res = await api.get('/cart')
        this.items = res.data.items || []
        this.loaded = true
      } catch {
        this.items = []
      }
    },
    async fetchCount() {
      // Alias para compatibilidad - ahora carga todo
      await this.fetchCart()
    },
    clear() {
      this.items = []
      this.loaded = false
    }
  }
})
