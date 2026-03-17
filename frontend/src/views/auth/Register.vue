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
        <input v-model="form.password" type="password" class="input-field" required />
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">Confirmar Contraseña</label>
        <input v-model="form.password_confirmation" type="password" class="input-field" required />
      </div>
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
import api from '../../utils/api'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/auth'

const router = useRouter()
const auth = useAuthStore()
const loading = ref(false)
const form = reactive({ name: '', email: '', password: '', password_confirmation: '' })

const handleRegister = async () => {
  loading.value = true
  try {
    const res = await api.post('/register', form)
    auth.token = res.data.access_token
    auth.user = res.data.user
    localStorage.setItem('token', auth.token)
    localStorage.setItem('user', JSON.stringify(auth.user))
    router.push('/')
  } catch (error) {
    alert('Error al registrarse')
  } finally {
    loading.value = false
  }
}
</script>
