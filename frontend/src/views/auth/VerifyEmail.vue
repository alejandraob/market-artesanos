<template>
  <div class="max-w-md mx-auto mt-12 bg-white p-8 rounded-lg shadow text-center">
    <div v-if="loading">
      <p class="text-gray-500">Verificando email...</p>
    </div>
    <div v-else-if="success">
      <div class="w-16 h-16 mx-auto bg-green-100 rounded-full flex items-center justify-center mb-4">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
        </svg>
      </div>
      <h2 class="text-2xl font-bold mb-2">Email verificado</h2>
      <p class="text-gray-500 mb-6">{{ message }}</p>
      <router-link to="/" class="btn-primary inline-block px-8 py-3">Ir al inicio</router-link>
    </div>
    <div v-else>
      <div class="w-16 h-16 mx-auto bg-red-100 rounded-full flex items-center justify-center mb-4">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </div>
      <h2 class="text-2xl font-bold mb-2">Error de verificacion</h2>
      <p class="text-red-500 mb-6">{{ message }}</p>
      <router-link to="/" class="btn-primary inline-block px-8 py-3">Ir al inicio</router-link>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '../../stores/auth'
import api from '../../utils/api'

const route = useRoute()
const auth = useAuthStore()
const loading = ref(true)
const success = ref(false)
const message = ref('')

onMounted(async () => {
  try {
    const res = await api.post('/verify-email', {
      id: route.query.id,
      token: route.query.token,
    })
    success.value = true
    message.value = res.data.message

    // Actualizar el usuario local si esta logueado
    if (auth.isAuthenticated) {
      await auth.fetchUser()
    }
  } catch (error) {
    success.value = false
    message.value = error.response?.data?.message || 'No se pudo verificar el email.'
  } finally {
    loading.value = false
  }
})
</script>
