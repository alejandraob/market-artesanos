<template>
  <div class="max-w-3xl mx-auto px-4 py-8">

    <!-- Loading -->
    <div v-if="loading" class="text-center py-12">
      <p class="text-gray-500">Cargando pedido...</p>
    </div>

    <!-- Error -->
    <div v-else-if="error" class="bg-white rounded-lg shadow p-8 text-center">
      <p class="text-xl text-red-600 mb-6">{{ error }}</p>
      <router-link to="/mi-perfil" class="btn-primary inline-block px-8 py-3">Ver mis pedidos</router-link>
    </div>

    <!-- Confirmacion -->
    <div v-else-if="order" class="space-y-6">

      <!-- Header exito -->
      <div class="bg-green-50 border border-green-200 rounded-2xl p-8 text-center">
        <div class="w-16 h-16 mx-auto bg-green-100 rounded-full flex items-center justify-center mb-4">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
          </svg>
        </div>
        <h1 class="text-2xl font-black text-green-800 mb-2">Pedido confirmado</h1>
        <p class="text-green-700">Tu pedido <strong>#{{ order.id }}</strong> fue registrado correctamente.</p>
        <p class="text-green-600 text-sm mt-2">{{ formatDate(order.created_at) }}</p>
      </div>

      <!-- Datos de contacto del artesano -->
      <div class="bg-artisan-accent/10 border border-artisan-accent/30 rounded-2xl p-6">
        <h2 class="font-bold text-lg text-artisan-dark mb-2">Contacta al artesano</h2>
        <p class="text-sm text-gray-600 mb-4">
          Comunicate con el artesano para coordinar los detalles de tu pedido: color, material, medidas, personalizacion, etc.
        </p>

        <div v-for="artisan in artesanosUnicos" :key="artisan.id" class="bg-white rounded-xl p-4 mb-3 last:mb-0 flex items-start gap-4">
          <div class="w-12 h-12 bg-artisan-brown/10 rounded-full flex items-center justify-center flex-shrink-0">
            <span class="text-lg font-black text-artisan-brown">{{ artisan.name?.charAt(0) }}</span>
          </div>
          <div>
            <p class="font-bold text-artisan-dark">{{ artisan.name }}</p>
            <p v-if="artisan.specialty" class="text-xs text-gray-500 mb-2">{{ artisan.specialty }}</p>
            <div class="space-y-1">
              <p v-if="artisan.phone" class="text-sm flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                </svg>
                <a :href="'https://wa.me/54' + artisan.phone.replace(/\D/g, '')" target="_blank" class="text-artisan-accent font-semibold hover:underline">
                  {{ artisan.phone }}
                </a>
              </p>
              <p v-if="artisan.email" class="text-sm flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                <a :href="'mailto:' + artisan.email" class="text-artisan-accent font-semibold hover:underline">{{ artisan.email }}</a>
              </p>
              <p v-if="artisan.location" class="text-sm flex items-center gap-2 text-gray-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                {{ artisan.location }}
              </p>
            </div>
            <p class="text-xs text-gray-400 mt-2">Productos en tu pedido: {{ artisan.productos.join(', ') }}</p>
          </div>
        </div>
      </div>

      <!-- Aviso tiempos -->
      <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 flex items-start gap-3">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <div>
          <p class="font-bold text-blue-800 text-sm">Tiempo estimado de entrega</p>
          <p v-if="!hayMasDe5" class="text-blue-700 text-sm mt-1">
            Tu pedido sera elaborado y enviado en un plazo de <strong>15 a 20 dias habiles</strong>.
          </p>
          <p v-else class="text-blue-700 text-sm mt-1">
            Tu pedido incluye productos con mas de 5 unidades. El tiempo de elaboracion <strong>puede superar los 20 dias habiles</strong>. El artesano te contactara para coordinar el plazo.
          </p>
        </div>
      </div>

      <!-- Detalle del pedido -->
      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <h2 class="font-bold text-lg mb-4">Detalle del pedido</h2>
        <div class="space-y-3">
          <div v-for="item in order.items" :key="item.id" class="flex items-center gap-4">
            <img
              :src="item.product?.images?.length ? storageUrl(item.product.images[0]) : 'https://placehold.co/50x50'"
              :alt="item.product?.name"
              class="w-12 h-12 rounded-lg object-cover flex-shrink-0"
              loading="lazy" decoding="async"
            />
            <div class="flex-grow">
              <p class="font-semibold text-sm">{{ item.product?.name }}</p>
              <p class="text-xs text-gray-500">Por {{ item.product?.artisan?.user?.name }}</p>
            </div>
            <span class="text-sm text-gray-500">x{{ item.quantity }}</span>
            <span class="font-bold text-sm">${{ (item.unit_price * item.quantity).toFixed(2) }}</span>
          </div>
        </div>

        <hr class="border-gray-100 my-4" />

        <div class="space-y-2 text-sm">
          <div class="flex justify-between">
            <span class="text-gray-500">Subtotal</span>
            <span>${{ (order.total - order.shipping_cost).toFixed(2) }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-gray-500">Envio</span>
            <span>${{ parseFloat(order.shipping_cost).toFixed(2) }}</span>
          </div>
          <div class="flex justify-between text-lg font-black pt-2 border-t border-gray-100">
            <span>Total</span>
            <span>${{ parseFloat(order.total).toFixed(2) }}</span>
          </div>
        </div>
      </div>

      <!-- Direccion de envio -->
      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <h2 class="font-bold text-lg mb-3">Direccion de envio</h2>
        <p class="text-sm text-gray-700">{{ order.shipping_name }}</p>
        <p class="text-sm text-gray-700">{{ order.shipping_address }}</p>
        <p class="text-sm text-gray-700">{{ order.shipping_city }}, {{ order.shipping_province }}</p>
        <p class="text-sm text-gray-700">CP {{ order.shipping_postal_code }}</p>
        <p class="text-sm text-gray-700">Tel: {{ order.shipping_phone }}</p>
        <p v-if="order.shipping_tracking" class="text-sm text-gray-500 mt-2">Tracking: {{ order.shipping_tracking }}</p>
      </div>

      <!-- Acciones -->
      <div class="flex flex-col sm:flex-row gap-4 justify-center">
        <router-link to="/mi-perfil" class="btn-primary text-center py-3 px-8">Ver mis pedidos</router-link>
        <router-link to="/catalogo" class="text-center py-3 px-8 border-2 border-artisan-brown text-artisan-brown font-bold rounded-full hover:bg-artisan-brown hover:text-white transition-colors">Seguir comprando</router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import api, { storageUrl } from '../utils/api'

const route = useRoute()
const router = useRouter()
const auth = useAuthStore()

const order = ref(null)
const loading = ref(true)
const error = ref('')

const artesanosUnicos = computed(() => {
  if (!order.value?.items) return []

  const map = new Map()
  for (const item of order.value.items) {
    const artisan = item.product?.artisan
    if (!artisan) continue

    if (!map.has(artisan.id)) {
      map.set(artisan.id, {
        id: artisan.id,
        name: artisan.user?.name,
        email: artisan.user?.email,
        phone: artisan.user?.phone,
        specialty: artisan.specialty,
        location: artisan.location,
        productos: []
      })
    }
    map.get(artisan.id).productos.push(item.product.name)
  }
  return Array.from(map.values())
})

const hayMasDe5 = computed(() => {
  return order.value?.items?.some(item => item.quantity > 5) || false
})

const formatDate = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('es-AR', {
    day: '2-digit', month: '2-digit', year: 'numeric',
    hour: '2-digit', minute: '2-digit'
  })
}

onMounted(async () => {
  if (!auth.isAuthenticated) {
    router.push('/login')
    return
  }

  try {
    const res = await api.get(`/orders/${route.params.id}`)
    order.value = res.data
  } catch (err) {
    error.value = 'No se pudo cargar el pedido.'
  } finally {
    loading.value = false
  }
})
</script>
