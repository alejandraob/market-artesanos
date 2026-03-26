import { defineStore } from 'pinia'

export const useToastStore = defineStore('toast', {
  state: () => ({
    message: '',
    type: 'info', // 'success', 'error', 'info'
    visible: false,
  }),
  actions: {
    show(message, type = 'info', duration = 3000) {
      this.message = message
      this.type = type
      this.visible = true
      setTimeout(() => { this.visible = false }, duration)
    },
    success(message) { this.show(message, 'success') },
    error(message) { this.show(message, 'error') },
    info(message) { this.show(message, 'info') },
  }
})
