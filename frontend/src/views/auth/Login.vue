<template>
  <div class="max-w-md mx-auto mt-12 bg-white p-8 rounded-lg shadow">
    <h2 class="text-2xl font-bold mb-6 text-center">Ingresar</h2>
    <form @submit.prevent="handleLogin" class="space-y-4">
      <div>
        <label class="block text-sm font-medium mb-1">Email</label>
        <input v-model="form.email" type="email" class="input-field" required />
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">Contraseña</label>
        <input v-model="form.password" type="password" class="input-field" required />
      </div>
      <div v-if="errorMsg" class="text-red-500 text-sm font-semibold bg-red-50 p-3 rounded-xl">{{ errorMsg }}</div>
      <button :disabled="loading" class="btn-primary w-full py-2 mt-4">
        {{ loading ? 'Ingresando...' : 'Entrar' }}
      </button>
    </form>
    <p class="mt-3 text-center">
      <router-link to="/recuperar-contrasena" class="text-sm text-artisan-accent font-semibold hover:underline">¿Olvidaste tu contrasena?</router-link>
    </p>
    <p class="mt-2 text-center text-sm text-gray-600">
      ¿No tienes cuenta? <router-link to="/registro" class="text-artisan-brown font-bold">Registrate</router-link>
    </p>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useAuthStore } from '../../stores/auth'
import { useCartStore } from '../../stores/cart'
import { useRouter } from 'vue-router'

const auth = useAuthStore()
const cartStore = useCartStore()
const router = useRouter()
const loading = ref(false)
const errorMsg = ref('')
const form = reactive({ email: '', password: '' })

const handleLogin = async () => {
  loading.value = true
  errorMsg.value = ''
  const success = await auth.login(form)
  loading.value = false
  if (success) {
    await cartStore.fetchCount()
    router.push('/')
  } else {
    errorMsg.value = 'Credenciales incorrectas'
  }
}
</script>
