import { defineStore } from 'pinia'
import api from '../utils/api'

export const useCartStore = defineStore('cart', {
  state: () => ({
    count: 0,
  }),
  actions: {
    async fetchCount() {
      try {
        const res = await api.get('/cart')
        const items = res.data.items || []
        this.count = items.reduce((sum, item) => sum + item.quantity, 0)
      } catch {
        this.count = 0
      }
    },
    clear() {
      this.count = 0
    }
  }
})
