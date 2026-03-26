<template>
  <div>
  <div v-if="loading" class="text-center py-24">Cargando producto...</div>
  <div v-else-if="!product" class="text-center py-24">Producto no encontrado</div>

  <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-12">
    <!-- Image Gallery -->
    <div class="space-y-4">
      <div class="aspect-square bg-white rounded-3xl overflow-hidden shadow-inner border border-gray-100 flex items-center justify-center p-8 relative">
        <img :src="activeImage" :alt="product.name" class="w-full h-full object-contain" decoding="async" />
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
          <img :src="storageUrl(img)" :alt="product.name + ' - imagen ' + (idx + 1)" class="w-full h-full object-cover" loading="lazy" decoding="async" />
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

      <!-- Compartir -->
      <div class="flex items-center gap-3 mt-6">
        <span class="text-xs font-bold text-gray-400 uppercase">Compartir:</span>
        <a :href="`https://wa.me/?text=${encodeURIComponent(product.name + ' - ' + shareUrl)}`" target="_blank" aria-label="Compartir en WhatsApp" class="w-9 h-9 bg-green-100 rounded-lg flex items-center justify-center hover:bg-green-200 transition-colors">
          <svg class="h-4 w-4 text-green-600" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
        </a>
        <a :href="`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(shareUrl)}`" target="_blank" aria-label="Compartir en Facebook" class="w-9 h-9 bg-blue-100 rounded-lg flex items-center justify-center hover:bg-blue-200 transition-colors">
          <svg class="h-4 w-4 text-blue-600" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
        </a>
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

  <!-- Resenas -->
  <div v-if="product" class="mt-12 col-span-2">
    <h2 class="text-2xl font-black text-artisan-dark mb-6">Resenas</h2>

    <!-- Resumen -->
    <div v-if="reviewsData.count > 0" class="flex items-center gap-4 mb-6">
      <div class="flex items-center gap-1">
        <span v-for="i in 5" :key="i" class="text-xl" :class="i <= Math.round(reviewsData.average) ? 'text-artisan-accent' : 'text-gray-300'">&#9733;</span>
      </div>
      <span class="text-lg font-bold text-artisan-dark">{{ reviewsData.average }}</span>
      <span class="text-sm text-gray-500">({{ reviewsData.count }} {{ reviewsData.count === 1 ? 'resena' : 'resenas' }})</span>
    </div>

    <!-- Formulario -->
    <div v-if="authStore.isAuthenticated && !userHasReview" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
      <h3 class="font-bold text-sm mb-3">Deja tu resena</h3>
      <div class="flex items-center gap-1 mb-3">
        <button v-for="i in 5" :key="i" @click="reviewForm.rating = i" class="text-2xl transition-colors" :class="i <= reviewForm.rating ? 'text-artisan-accent' : 'text-gray-300'">&#9733;</button>
      </div>
      <textarea v-model="reviewForm.comment" class="input-field text-sm min-h-[80px] resize-y mb-3" placeholder="Contanos tu experiencia (opcional)"></textarea>
      <div v-if="reviewError" class="text-red-500 text-xs mb-2">{{ reviewError }}</div>
      <button @click="submitReview" :disabled="!reviewForm.rating || submittingReview" class="bg-artisan-brown hover:bg-[#5b3a27] text-white font-bold text-sm px-5 py-2.5 rounded-xl transition-colors disabled:opacity-50">
        {{ submittingReview ? 'Enviando...' : 'Enviar Resena' }}
      </button>
    </div>
    <div v-else-if="!authStore.isAuthenticated" class="bg-gray-50 rounded-2xl p-5 mb-6 text-center">
      <p class="text-sm text-gray-500"><router-link to="/login" class="text-artisan-accent font-bold hover:underline">Inicia sesion</router-link> para dejar una resena.</p>
    </div>

    <!-- Lista -->
    <div v-if="reviewsData.reviews.length" class="space-y-4">
      <div v-for="review in reviewsData.reviews" :key="review.id" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
        <div class="flex items-center justify-between mb-2">
          <div class="flex items-center gap-3">
            <div class="w-8 h-8 bg-artisan-brown/10 rounded-full flex items-center justify-center">
              <span class="text-xs font-bold text-artisan-brown">{{ review.user?.name?.charAt(0) }}</span>
            </div>
            <span class="font-bold text-sm">{{ review.user?.name }}</span>
            <div class="flex">
              <span v-for="i in 5" :key="i" class="text-sm" :class="i <= review.rating ? 'text-artisan-accent' : 'text-gray-300'">&#9733;</span>
            </div>
          </div>
          <span class="text-xs text-gray-400">{{ formatReviewDate(review.created_at) }}</span>
        </div>
        <p v-if="review.comment" class="text-sm text-gray-600">{{ review.comment }}</p>
      </div>
    </div>
    <div v-else-if="reviewsData.count === 0" class="text-center py-8 text-gray-400">
      <p class="font-semibold">Aun no hay resenas para este producto.</p>
    </div>
  </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import api, { storageUrl } from '../utils/api'
import { useCartStore } from '../stores/cart'
import { useAuthStore } from '../stores/auth'
import { useToastStore } from '../stores/toast'

const cartStore = useCartStore()
const authStore = useAuthStore()
const toastStore = useToastStore()

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
const shareUrl = computed(() => window.location.href)

// Reviews
const reviewsData = ref({ reviews: [], average: null, count: 0 })
const reviewForm = reactive({ rating: 0, comment: '' })
const submittingReview = ref(false)
const reviewError = ref('')

const userHasReview = computed(() => {
  if (!authStore.user) return false
  return reviewsData.value.reviews.some(r => r.user_id === authStore.user.id)
})

const fetchReviews = async () => {
  if (!product.value) return
  try {
    const res = await api.get(`/products/${product.value.id}/reviews`)
    reviewsData.value = res.data
  } catch (e) {
    // silencioso
  }
}

const submitReview = async () => {
  if (!reviewForm.rating) return
  submittingReview.value = true
  reviewError.value = ''
  try {
    await api.post(`/products/${product.value.id}/reviews`, reviewForm)
    reviewForm.rating = 0
    reviewForm.comment = ''
    await fetchReviews()
  } catch (error) {
    reviewError.value = error.response?.data?.message || 'Error al enviar la resena.'
  } finally {
    submittingReview.value = false
  }
}

const formatReviewDate = (date) => {
  if (!date) return ''
  return new Date(date).toLocaleDateString('es-AR', { day: '2-digit', month: '2-digit', year: 'numeric' })
}

onMounted(async () => {
  try {
    const res = await api.get(`/products/${route.params.slug}`)
    product.value = res.data
    if (product.value.images?.length > 0) {
      activeImage.value = storageUrl(product.value.images[0])
    }
    fetchReviews()
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
    cartStore.fetchCount()
    cartMessage.value = '¡Agregado al carrito!'
    setTimeout(() => cartMessage.value = '', 3000)
  } catch (error) {
    toastStore.error('Error al agregar al carrito')
  } finally {
    isAdding.value = false
  }
}
</script>
