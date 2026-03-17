import { defineStore } from 'pinia'
import api from '../utils/api'

export const useAuthStore = defineStore('auth', {
  state: () => {
    const savedUser = localStorage.getItem('user')
    let user = null
    try {
      user = (savedUser && savedUser !== 'undefined') ? JSON.parse(savedUser) : null
    } catch (e) {
      user = null
    }
    return {
      user,
      token: localStorage.getItem('token') || null,
    }
  },
  getters: {
    isAuthenticated: (state) => !!state.token,
    isAdmin: (state) => state.user?.role === 'admin',
    isPresidente: (state) => state.user?.role === 'presidente',
    isStaff: (state) => state.user?.role === 'admin' || state.user?.role === 'presidente',
  },
  actions: {
    async login(credentials) {
      try {
        const res = await api.post('/login', credentials)
        this.token = res.data.access_token
        this.user = res.data.user
        localStorage.setItem('token', this.token)
        localStorage.setItem('user', JSON.stringify(this.user))
        api.defaults.headers.common['Authorization'] = `Bearer ${this.token}`
        return true
      } catch (error) {
        console.error('Login failed', error)
        return false
      }
    },
    async fetchUser() {
      if (!this.token) return
      try {
        const res = await api.get('/user')
        this.user = res.data
        localStorage.setItem('user', JSON.stringify(this.user))
      } catch (error) {
        this.logout()
      }
    },
    logout() {
      this.user = null
      this.token = null
      localStorage.removeItem('token')
      localStorage.removeItem('user')
      delete api.defaults.headers.common['Authorization']
    }
  }
})
