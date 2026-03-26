import { defineStore } from 'pinia'
import api from '../utils/api'

export const useWishlistStore = defineStore('wishlist', {
  state: () => ({
    items: [],
  }),
  getters: {
    productIds: (state) => state.items.map(i => i.product_id),
    count: (state) => state.items.length,
  },
  actions: {
    async fetch() {
      try {
        const res = await api.get('/wishlist')
        this.items = res.data
      } catch {
        this.items = []
      }
    },
    async toggle(productId) {
      try {
        const res = await api.post('/wishlist/toggle', { product_id: productId })
        await this.fetch()
        return res.data
      } catch {
        return null
      }
    },
    isInWishlist(productId) {
      return this.productIds.includes(productId)
    },
    clear() {
      this.items = []
    }
  }
})
