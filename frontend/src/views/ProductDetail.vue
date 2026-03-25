<template>
  <div v-if="loading" class="text-center py-24">Cargando producto...</div>
  <div v-else-if="!product" class="text-center py-24">Producto no encontrado</div>
  
  <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-12">
    <!-- Image Gallery -->
    <div class="space-y-4">
      <div class="aspect-square bg-white rounded-3xl overflow-hidden shadow-inner border border-gray-100 flex items-center justify-center p-8 relative">
        <img :src="activeImage" :alt="product.name" class="w-full h-full object-contain" />
        <div class="absolute bottom-4 left-4 right-4 bg-artisan-brown/80 backdrop-blur-sm text-white text-xs font-semibold px-4 py-2 rounded-xl text-center">
          Imagen ilustrativa de muestra - El producto final se elabora a pedido
        </div>
      </div>
      <div class="flex gap-4 overflow-x-auto pb-2">
        <button 
          v-for="(img, idx) in product.images" 
          :key="idx"
          @click="activeImage = storageUrl(img)"
          class="w-20 h-20 rounded-xl border-2 flex-shrink-0 overflow-hidden"
          :class="activeImage === storageUrl(img) ? 'border-artisan-accent' : 'border-transparent'"
        >
          <img :src="storageUrl(img)" class="w-full h-full object-cover" />
        </button>
      </div>
    </div>

    <!-- Product Info -->
    <div class="flex flex-col">
      <h1 class="text-4xl font-extrabold text-artisan-dark mb-2">{{ product.name }}</h1>
      <p class="text-xl text-artisan-accent font-bold mb-6">Por {{ product.artisan?.user?.name }}</p>
      
      <div class="prose prose-stone mb-8">
        <p>{{ product.description }}</p>
      </div>

      <div class="bg-gray-50 p-8 rounded-3xl border border-gray-100 mt-auto">
        <div class="mb-6">
          <span class="text-3xl font-black text-artisan-dark">${{ product.price }}</span>
          <span class="text-sm text-gray-500 font-medium ml-2">por unidad</span>
        </div>

        <div class="flex gap-4">
          <div class="flex items-center border rounded-full bg-white px-4">
            <button @click="quantity > 1 && quantity--" class="p-2">-</button>
            <span class="w-12 text-center font-bold">{{ quantity }}</span>
            <button @click="quantity++" class="p-2">+</button>
          </div>
          <button 
            @click="addToCart" 
            :disabled="isAdding"
            class="flex-grow btn-primary py-4 rounded-full font-bold shadow-lg hover:-translate-y-1 transition-all"
          >
            {{ isAdding ? 'Agregando...' : 'Agregar al Carrito' }}
          </button>
        </div>
        <p v-if="cartMessage" class="mt-4 text-center text-green-600 font-bold animate-bounce">{{ cartMessage }}</p>
      </div>

      <!-- Aviso producto artesanal -->
      <div class="bg-blue-50 border border-blue-200 rounded-2xl p-5 mt-6 flex items-start gap-4">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
        </svg>
        <div>
          <p class="font-bold text-blue-800 text-sm">Producto elaborado a pedido</p>
          <p class="text-blue-700 text-sm mt-1">Las imagenes son <strong>muestras ilustrativas</strong>. Una vez confirmada la compra, se te proporcionaran los datos del artesano para que puedas coordinar los detalles del producto: color, material, medidas y personalizacion.</p>
        </div>
      </div>

      <!-- Calcular envío -->
      <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5 mt-4">
        <div class="flex items-start gap-4 mb-4">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-amber-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <div>
            <p class="font-bold text-amber-800 text-sm">Calcular costo de envio</p>
            <p class="text-amber-700 text-sm mt-1">Ingresa tu codigo postal para conocer el costo del envio via Correo Argentino.</p>
          </div>
        </div>
        <div class="flex gap-3">
          <input
            v-model="postalCode"
            type="text"
            placeholder="Ej: 1414"
            maxlength="8"
            class="flex-grow px-4 py-3 border border-amber-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-400 text-sm"
            @keyup.enter="calcularEnvio"
          />
          <button
            @click="calcularEnvio"
            :disabled="calculandoEnvio || !postalCode"
            class="bg-amber-600 hover:bg-amber-700 text-white px-6 py-3 rounded-xl font-bold text-sm transition-colors disabled:opacity-50"
          >
            {{ calculandoEnvio ? 'Calculando...' : 'Calcular' }}
          </button>
        </div>
        <div v-if="shippingRates" class="mt-4 space-y-2">
          <div v-for="(rate, idx) in shippingRates" :key="idx" class="bg-white border border-amber-200 rounded-xl p-3 flex justify-between items-center">
            <div>
              <p class="font-bold text-sm text-artisan-dark">{{ rate.serviceDescription || rate.serviceType }}</p>
              <p v-if="rate.deliveryTime" class="text-xs text-gray-500">Entrega estimada: {{ rate.deliveryTime }} dias habiles</p>
            </div>
            <span class="font-black text-lg text-amber-700">${{ rate.price || rate.total }}</span>
          </div>
        </div>
        <p v-if="shippingError" class="mt-3 text-red-600 text-sm font-medium">{{ shippingError }}</p>
        <p class="text-amber-700 text-xs mt-3">Una vez efectuada la compra, el producto sera elaborado y enviado en un plazo de <strong>15 a 20 dias habiles</strong>.</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import api, { storageUrl } from '../utils/api'

const route = useRoute()
const product = ref(null)
const loading = ref(true)
const activeImage = ref('https://placehold.co/800x800?text=Artesanía')
const quantity = ref(1)
const isAdding = ref(false)
const cartMessage = ref('')
const postalCode = ref('')
const calculandoEnvio = ref(false)
const shippingRates = ref(null)
const shippingError = ref('')

onMounted(async () => {
  try {
    const res = await api.get(`/products/${route.params.slug}`)
    product.value = res.data
    if (product.value.images?.length > 0) {
      activeImage.value = storageUrl(product.value.images[0])
    }
  } catch (error) {
    console.error("Error loading product", error)
  } finally {
    loading.value = false
  }
})

const calcularEnvio = async () => {
  if (!postalCode.value) return
  calculandoEnvio.value = true
  shippingRates.value = null
  shippingError.value = ''
  try {
    const res = await api.post('/shipping-rates', {
      postal_code: postalCode.value,
      product_id: product.value.id
    })
    shippingRates.value = res.data
    if (!res.data || res.data.length === 0) {
      shippingError.value = 'No se encontraron opciones de envio para ese codigo postal.'
    }
  } catch (error) {
    shippingError.value = 'No se pudo calcular el envio. Verifica el codigo postal e intenta nuevamente.'
  } finally {
    calculandoEnvio.value = false
  }
}

const addToCart = async () => {
  isAdding.value = true
  try {
    await api.post('/cart', {
      product_id: product.value.id,
      quantity: quantity.value
    })
    cartMessage.value = '¡Agregado al carrito!'
    setTimeout(() => cartMessage.value = '', 3000)
  } catch (error) {
    alert('Error al agregar al carrito')
  } finally {
    isAdding.value = false
  }
}
</script>
