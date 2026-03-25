<template>
  <div class="max-w-md mx-auto mt-12 bg-white p-8 rounded-lg shadow">
    <h2 class="text-2xl font-bold mb-2 text-center">Nueva contrasena</h2>
    <p class="text-sm text-gray-500 text-center mb-6">Ingresa tu nueva contrasena.</p>

    <form @submit.prevent="handleSubmit" class="space-y-4">
      <div>
        <label class="block text-sm font-medium mb-1">Nueva contrasena</label>
        <input v-model="form.password" type="password" class="input-field" required placeholder="Minimo 8 caracteres" />
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">Confirmar contrasena</label>
        <input v-model="form.password_confirmation" type="password" class="input-field" required placeholder="Repetir contrasena" />
      </div>

      <div v-if="successMsg" class="text-green-600 text-sm font-semibold bg-green-50 p-3 rounded-xl">
        {{ successMsg }}
        <router-link to="/login" class="block mt-2 text-artisan-brown font-bold underline">Ir al login</router-link>
      </div>
      <div v-if="errorMsg" class="text-red-500 text-sm font-semibold bg-red-50 p-3 rounded-xl">{{ errorMsg }}</div>

      <button :disabled="loading || !!successMsg" class="btn-primary w-full py-2 mt-4">
        {{ loading ? 'Guardando...' : 'Restablecer contrasena' }}
      </button>
    </form>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import api from '../../utils/api'

const route = useRoute()
const loading = ref(false)
const successMsg = ref('')
const errorMsg = ref('')
const token = ref('')
const email = ref('')

const form = reactive({
  password: '',
  password_confirmation: '',
})

onMounted(() => {
  token.value = route.params.token || ''
  email.value = route.query.email || ''
})

const handleSubmit = async () => {
  loading.value = true
  successMsg.value = ''
  errorMsg.value = ''
  try {
    const res = await api.post('/reset-password', {
      email: email.value,
      token: token.value,
      password: form.password,
      password_confirmation: form.password_confirmation,
    })
    successMsg.value = res.data.message
  } catch (error) {
    const data = error.response?.data
    if (data?.errors) {
      errorMsg.value = Object.values(data.errors).flat().join('. ')
    } else {
      errorMsg.value = data?.message || 'Error al restablecer la contrasena.'
    }
  } finally {
    loading.value = false
  }
}
</script>
