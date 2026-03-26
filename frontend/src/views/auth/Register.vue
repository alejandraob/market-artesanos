<template>
  <div class="max-w-md mx-auto mt-12 bg-white p-8 rounded-lg shadow">
    <h2 class="text-2xl font-bold mb-6 text-center">Registrarse</h2>
    <form @submit.prevent="handleRegister" class="space-y-4">
      <div>
        <label class="block text-sm font-medium mb-1">Nombre Completo</label>
        <input v-model="form.name" type="text" class="input-field" required />
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">Email</label>
        <input v-model="form.email" type="email" class="input-field" required />
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">Contraseña</label>
        <PasswordInput v-model="form.password" required placeholder="Minimo 8 caracteres" />
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">Confirmar Contraseña</label>
        <PasswordInput v-model="form.password_confirmation" required placeholder="Repetir contraseña" />
      </div>
      <div v-if="errorMsg" class="text-red-500 text-sm font-semibold bg-red-50 p-3 rounded-xl">{{ errorMsg }}</div>
      <button :disabled="loading" class="btn-primary w-full py-2 mt-4">
        {{ loading ? 'Registrando...' : 'Crear Cuenta' }}
      </button>
    </form>
    <p class="mt-4 text-center text-sm text-gray-600">
      ¿Ya tienes cuenta? <router-link to="/login" class="text-artisan-brown font-bold">Inicia Sesión</router-link>
    </p>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import PasswordInput from '../../components/common/PasswordInput.vue'
import api from '../../utils/api'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/auth'
import { useCartStore } from '../../stores/cart'

const router = useRouter()
const auth = useAuthStore()
const cartStore = useCartStore()
const loading = ref(false)
const errorMsg = ref('')
const form = reactive({ name: '', email: '', password: '', password_confirmation: '' })

const handleRegister = async () => {
  loading.value = true
  errorMsg.value = ''
  try {
    const res = await api.post('/register', form)
    auth.token = res.data.access_token
    auth.user = res.data.user
    localStorage.setItem('token', auth.token)
    localStorage.setItem('user', JSON.stringify(auth.user))
    api.defaults.headers.common['Authorization'] = `Bearer ${auth.token}`
    await cartStore.fetchCount()
    router.push('/')
  } catch (error) {
    const data = error.response?.data
    if (data?.errors) {
      errorMsg.value = Object.values(data.errors).flat().join('. ')
    } else {
      errorMsg.value = data?.message || 'Error al registrarse'
    }
  } finally {
    loading.value = false
  }
}
</script>
