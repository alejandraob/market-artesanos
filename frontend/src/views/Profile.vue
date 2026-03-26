<template>
  <div class="max-w-3xl mx-auto">
    <h1 class="text-3xl font-black text-artisan-dark mb-8">Mi Perfil</h1>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
      <!-- Sidebar info -->
      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 text-center">
        <div class="w-20 h-20 mx-auto bg-gradient-to-tr from-artisan-brown to-artisan-accent rounded-full flex items-center justify-center mb-4">
          <span class="text-3xl font-black text-white">{{ auth.user?.name?.charAt(0) }}</span>
        </div>
        <h3 class="font-bold text-lg">{{ auth.user?.name }}</h3>
        <p class="text-sm text-gray-500">{{ auth.user?.email }}</p>
        <span class="inline-block mt-3 text-xs font-bold uppercase px-3 py-1 rounded-full bg-artisan-accent/10 text-artisan-accent">{{ auth.user?.role }}</span>
        <p class="text-xs text-gray-400 mt-4">Miembro desde {{ formatDate(auth.user?.created_at) }}</p>

        <!-- Verificacion email -->
        <div v-if="!auth.user?.email_verified_at" class="mt-4 bg-amber-50 border border-amber-200 rounded-xl p-3 text-left">
          <p class="text-amber-700 text-xs font-semibold">Email no verificado</p>
          <button @click="resendVerification" :disabled="resending" class="text-xs text-amber-600 font-bold underline mt-1">
            {{ resending ? 'Enviando...' : 'Reenviar verificacion' }}
          </button>
          <p v-if="resendMsg" class="text-green-600 text-xs mt-1">{{ resendMsg }}</p>
        </div>
        <div v-else class="mt-4">
          <span class="text-xs text-green-600 font-bold">Email verificado</span>
        </div>
      </div>

      <!-- Edit form -->
      <div class="md:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <h2 class="font-bold text-lg mb-6">Editar datos</h2>
        <form @submit.prevent="saveProfile" class="space-y-4">
          <div>
            <label class="text-xs font-bold text-gray-500 uppercase mb-1 block">Nombre completo</label>
            <input v-model="form.name" class="input-field" required />
          </div>
          <div>
            <label class="text-xs font-bold text-gray-500 uppercase mb-1 block">Email</label>
            <input v-model="form.email" type="email" class="input-field" required />
          </div>
          <div>
            <label class="text-xs font-bold text-gray-500 uppercase mb-1 block">Telefono</label>
            <input v-model="form.phone" class="input-field" placeholder="Tu numero de telefono" />
          </div>

          <hr class="my-4 border-gray-100" />
          <p class="text-xs text-gray-400 font-semibold">Dejar en blanco para mantener la contrasena actual</p>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="text-xs font-bold text-gray-500 uppercase mb-1 block">Nueva contrasena</label>
              <input v-model="form.password" type="password" class="input-field" placeholder="********" />
            </div>
            <div>
              <label class="text-xs font-bold text-gray-500 uppercase mb-1 block">Confirmar contrasena</label>
              <input v-model="form.password_confirmation" type="password" class="input-field" placeholder="********" />
            </div>
          </div>

          <div v-if="errorMsg" class="text-red-500 text-sm font-semibold bg-red-50 p-3 rounded-xl">{{ errorMsg }}</div>
          <div v-if="successMsg" class="text-green-600 text-sm font-semibold bg-green-50 p-3 rounded-xl">{{ successMsg }}</div>

          <button type="submit" :disabled="saving" class="btn-primary py-3 px-8 rounded-full w-full">
            {{ saving ? 'Guardando...' : 'Guardar Cambios' }}
          </button>
        </form>
      </div>
    </div>

    <!-- Order History -->
    <div class="mt-10">
      <h2 class="text-2xl font-black text-artisan-dark mb-6">Historial de Compras</h2>
      <div v-if="orders.length === 0" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 text-center text-gray-400">
        <p class="text-4xl mb-3">🛍️</p>
        <p class="font-semibold">Aun no tienes compras</p>
        <router-link to="/catalogo" class="inline-block mt-4 text-artisan-accent font-bold hover:underline">Explorar productos</router-link>
      </div>
      <div v-else class="space-y-4">
        <div v-for="order in orders" :key="order.id" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
          <div class="flex justify-between items-start mb-4">
            <div>
              <span class="font-bold text-sm">Pedido #{{ order.id }}</span>
              <p class="text-xs text-gray-400">{{ formatDate(order.created_at) }}</p>
            </div>
            <span class="text-xs font-bold px-3 py-1 rounded-full" :class="statusClass(order.status)">{{ statusLabel(order.status) }}</span>
          </div>
          <div class="space-y-2 mb-4">
            <div v-for="item in order.items" :key="item.id" class="flex items-center gap-3 text-sm">
              <img
                :src="item.product?.images?.length ? storageUrl(item.product.images[0]) : 'https://placehold.co/40x40?text=...'"
                class="w-10 h-10 rounded-lg object-cover"
              />
              <span class="flex-1">{{ item.product?.name || 'Producto eliminado' }}</span>
              <span class="text-gray-500">x{{ item.quantity }}</span>
              <span class="font-bold">${{ item.unit_price }}</span>
            </div>
          </div>
          <div class="flex justify-between items-center pt-3 border-t border-gray-100">
            <div>
              <span v-if="order.shipping_tracking" class="text-xs text-gray-400 block">Tracking: {{ order.shipping_tracking }}</span>
              <router-link :to="{ name: 'order-confirmation', params: { id: order.id } }" class="text-xs text-artisan-accent font-bold hover:underline">Ver detalle y datos del artesano</router-link>
            </div>
            <span class="font-black text-lg">${{ order.total }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useAuthStore } from '../stores/auth'
import api, { storageUrl } from '../utils/api'

const auth = useAuthStore()
const orders = ref([])
const saving = ref(false)
const errorMsg = ref('')
const successMsg = ref('')
const resending = ref(false)
const resendMsg = ref('')

const resendVerification = async () => {
  resending.value = true
  resendMsg.value = ''
  try {
    const res = await api.post('/resend-verification')
    resendMsg.value = res.data.message
  } catch (error) {
    resendMsg.value = 'Error al reenviar.'
  } finally {
    resending.value = false
  }
}

const form = reactive({
  name: '',
  email: '',
  phone: '',
  password: '',
  password_confirmation: '',
})

const loadData = () => {
  form.name = auth.user?.name || ''
  form.email = auth.user?.email || ''
  form.phone = auth.user?.phone || ''
  form.password = ''
  form.password_confirmation = ''
}

const saveProfile = async () => {
  saving.value = true
  errorMsg.value = ''
  successMsg.value = ''
  try {
    const data = { name: form.name, email: form.email, phone: form.phone }
    if (form.password) {
      data.password = form.password
      data.password_confirmation = form.password_confirmation
    }
    const res = await api.put('/user', data)
    auth.user = res.data
    localStorage.setItem('user', JSON.stringify(res.data))
    successMsg.value = 'Datos actualizados correctamente'
    form.password = ''
    form.password_confirmation = ''
  } catch (error) {
    errorMsg.value = error.response?.data?.message || 'Error al guardar los datos'
  } finally {
    saving.value = false
  }
}

const fetchOrders = async () => {
  try {
    const res = await api.get('/orders')
    orders.value = res.data
  } catch (error) {
    console.error('Error loading orders', error)
  }
}

const formatDate = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('es-AR', { day: '2-digit', month: '2-digit', year: 'numeric' })
}

const statusLabels = { pending: 'Pendiente', paid: 'Pagado', shipped: 'Enviado', delivered: 'Entregado', cancelled: 'Cancelado' }
const statusLabel = (s) => statusLabels[s] || s
const statusClass = (s) => {
  const map = {
    pending: 'bg-yellow-100 text-yellow-700',
    paid: 'bg-blue-100 text-blue-700',
    shipped: 'bg-purple-100 text-purple-700',
    delivered: 'bg-green-100 text-green-700',
    cancelled: 'bg-red-100 text-red-700',
  }
  return map[s] || 'bg-gray-100 text-gray-700'
}

onMounted(() => {
  loadData()
  fetchOrders()
})
</script>
