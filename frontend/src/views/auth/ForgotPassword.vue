<template>
  <div class="max-w-md mx-auto mt-12 bg-white p-8 rounded-lg shadow">
    <h2 class="text-2xl font-bold mb-2 text-center">Recuperar contrasena</h2>
    <p class="text-sm text-gray-500 text-center mb-6">Ingresa tu email y te enviaremos un enlace para restablecer tu contrasena.</p>

    <form @submit.prevent="handleSubmit" class="space-y-4">
      <div>
        <label class="block text-sm font-medium mb-1">Email</label>
        <input v-model="email" type="email" class="input-field" required placeholder="tu@email.com" />
      </div>

      <div v-if="successMsg" class="text-green-600 text-sm font-semibold bg-green-50 p-3 rounded-xl">{{ successMsg }}</div>
      <div v-if="errorMsg" class="text-red-500 text-sm font-semibold bg-red-50 p-3 rounded-xl">{{ errorMsg }}</div>

      <button :disabled="loading" class="btn-primary w-full py-2 mt-4">
        {{ loading ? 'Enviando...' : 'Enviar enlace' }}
      </button>
    </form>

    <p class="mt-4 text-center text-sm text-gray-600">
      <router-link to="/login" class="text-artisan-brown font-bold">Volver al login</router-link>
    </p>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import api from '../../utils/api'

const email = ref('')
const loading = ref(false)
const successMsg = ref('')
const errorMsg = ref('')

const handleSubmit = async () => {
  loading.value = true
  successMsg.value = ''
  errorMsg.value = ''
  try {
    const res = await api.post('/forgot-password', { email: email.value })
    successMsg.value = res.data.message
  } catch (error) {
    errorMsg.value = error.response?.data?.message || 'Error al enviar el enlace.'
  } finally {
    loading.value = false
  }
}
</script>
