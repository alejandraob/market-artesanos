import axios from 'axios'
import { v4 as uuidv4 } from 'uuid'

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || 'http://127.0.0.1:8000/api',
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json'
  }
})

const BACKEND_URL = import.meta.env.VITE_BACKEND_URL || 'http://127.0.0.1:8000'

export const storageUrl = (path) => {
  if (!path) return 'https://placehold.co/400x300?text=Sin+Imagen'
  if (path.startsWith('http')) return path
  return `${BACKEND_URL}/storage/${path}`
}

// Token Handling
const token = localStorage.getItem('token')
if (token) {
  api.defaults.headers.common['Authorization'] = `Bearer ${token}`
}

// Manage Guest Cart Session ID
let sessionId = localStorage.getItem('cart_session_id')
if (!sessionId) {
  sessionId = uuidv4()
  localStorage.setItem('cart_session_id', sessionId)
}

api.interceptors.request.use(config => {
  config.headers['X-Session-Id'] = sessionId
  return config
})

export default api
