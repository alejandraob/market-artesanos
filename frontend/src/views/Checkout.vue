<template>
  <div class="max-w-5xl mx-auto px-4 py-8">

    <!-- Loading -->
    <div v-if="loading" class="text-center py-12">
      <p class="text-gray-500">Cargando carrito...</p>
    </div>

    <!-- Carrito vacio -->
    <div v-else-if="!cartItems.length" class="bg-white rounded-lg shadow p-8 text-center">
      <p class="text-xl text-gray-600 mb-6">Tu carrito esta vacio</p>
      <router-link to="/catalogo" class="btn-primary inline-block px-8 py-3">Volver al Catalogo</router-link>
    </div>

    <!-- Paso de pago simulado -->
    <div v-else-if="step === 'payment'" class="max-w-lg mx-auto space-y-6">
      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 text-center">
        <div class="w-16 h-16 mx-auto bg-artisan-accent/10 rounded-full flex items-center justify-center mb-4">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-artisan-accent" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
          </svg>
        </div>
        <h2 class="text-2xl font-black text-artisan-dark mb-2">Procesando Pago</h2>
        <p class="text-gray-500 mb-6">Total a pagar: <strong class="text-artisan-dark text-xl">${{ total.toFixed(2) }}</strong></p>

        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-6 text-left">
          <p class="text-amber-800 text-sm font-semibold">Modo de prueba</p>
          <p class="text-amber-700 text-sm mt-1">La pasarela de pago PayWay se encuentra en proceso de integracion. Al presionar "Simular Pago" se creara el pedido como si el pago se hubiera realizado correctamente.</p>
        </div>

        <div class="space-y-3">
          <button
            @click="procesarPago"
            :disabled="processing"
            class="btn-primary w-full py-4 text-lg rounded-full"
          >
            {{ processing ? 'Procesando...' : 'Simular Pago' }}
          </button>
          <button @click="step = 'form'" class="w-full py-3 text-sm text-gray-500 hover:text-artisan-dark transition-colors font-medium">
            Volver a los datos de envio
          </button>
        </div>
        <p v-if="checkoutError" class="text-red-500 text-sm font-semibold mt-4">{{ checkoutError }}</p>
      </div>
    </div>

    <!-- Formulario de checkout -->
    <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-8">

      <h1 class="text-3xl font-bold text-artisan-dark lg:col-span-3">Finalizar Compra</h1>

      <!-- Columna izquierda: Formulario -->
      <div class="lg:col-span-2 space-y-6">

        <!-- Datos de envio -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
          <h2 class="font-bold text-lg mb-4">Datos de envio</h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
              <label class="text-xs font-bold text-gray-500 uppercase mb-1 block">Nombre completo</label>
              <input v-model="form.shipping_name" class="input-field" placeholder="Nombre y apellido" />
              <p v-if="errors.shipping_name" class="text-red-500 text-xs mt-1">{{ errors.shipping_name }}</p>
            </div>
            <div class="md:col-span-2">
              <label class="text-xs font-bold text-gray-500 uppercase mb-1 block">Direccion</label>
              <input v-model="form.shipping_address" class="input-field" placeholder="Calle, numero, piso, depto" />
              <p v-if="errors.shipping_address" class="text-red-500 text-xs mt-1">{{ errors.shipping_address }}</p>
            </div>
            <div>
              <label class="text-xs font-bold text-gray-500 uppercase mb-1 block">Ciudad / Localidad</label>
              <input v-model="form.shipping_city" class="input-field" placeholder="Ej: Viedma" />
              <p v-if="errors.shipping_city" class="text-red-500 text-xs mt-1">{{ errors.shipping_city }}</p>
            </div>
            <div>
              <label class="text-xs font-bold text-gray-500 uppercase mb-1 block">Provincia</label>
              <select v-model="form.shipping_province" class="input-field">
                <option value="">Seleccionar...</option>
                <option v-for="prov in provincias" :key="prov" :value="prov">{{ prov }}</option>
              </select>
              <p v-if="errors.shipping_province" class="text-red-500 text-xs mt-1">{{ errors.shipping_province }}</p>
            </div>
            <div>
              <label class="text-xs font-bold text-gray-500 uppercase mb-1 block">Codigo Postal</label>
              <input v-model="form.shipping_postal_code" class="input-field" placeholder="Ej: 8500" maxlength="8" />
              <p v-if="errors.shipping_postal_code" class="text-red-500 text-xs mt-1">{{ errors.shipping_postal_code }}</p>
            </div>
            <div>
              <label class="text-xs font-bold text-gray-500 uppercase mb-1 block">Telefono de contacto</label>
              <input v-model="form.shipping_phone" class="input-field" placeholder="Ej: 2920-000000" />
              <p v-if="errors.shipping_phone" class="text-red-500 text-xs mt-1">{{ errors.shipping_phone }}</p>
            </div>
          </div>
        </div>

        <!-- Cotizacion de envio -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
          <h2 class="font-bold text-lg mb-4">Costo de envio</h2>

          <div v-if="!form.shipping_postal_code" class="text-gray-400 text-sm">
            Completa el codigo postal arriba para calcular el envio.
          </div>

          <div v-else>
            <button
              @click="calcularEnvio"
              :disabled="calculandoEnvio"
              class="bg-amber-600 hover:bg-amber-700 text-white px-6 py-3 rounded-xl font-bold text-sm transition-colors disabled:opacity-50"
            >
              {{ calculandoEnvio ? 'Calculando...' : 'Calcular costo de envio' }}
            </button>

            <div v-if="shippingRates && shippingRates.length" class="mt-4 space-y-2">
              <label
                v-for="(rate, idx) in shippingRates"
                :key="idx"
                class="flex items-center gap-4 bg-gray-50 border rounded-xl p-4 cursor-pointer transition-colors"
                :class="selectedShipping === idx ? 'border-artisan-accent bg-amber-50' : 'border-gray-200 hover:border-gray-300'"
              >
                <input type="radio" :value="idx" v-model="selectedShipping" class="accent-artisan-accent" />
                <div class="flex-grow">
                  <p class="font-bold text-sm">{{ rate.serviceDescription || rate.serviceType }}</p>
                  <p v-if="rate.deliveryTime" class="text-xs text-gray-500">Entrega estimada: {{ rate.deliveryTime }} dias habiles</p>
                </div>
                <span class="font-black text-lg text-amber-700">${{ rate.price || rate.total }}</span>
              </label>

              <p v-if="usandoSimulado" class="text-xs text-amber-600 mt-2 italic">* Tarifas estimadas (modo simulacion). El costo real se calculara con Correo Argentino al momento del envio.</p>
            </div>

            <p v-if="shippingError" class="mt-3 text-red-600 text-sm font-medium">{{ shippingError }}</p>
          </div>
        </div>

        <!-- Aviso tiempos de entrega -->
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 flex items-start gap-3">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <div>
            <p class="font-bold text-blue-800 text-sm">Productos elaborados a pedido</p>
            <p v-if="!hayMasDe5" class="text-blue-700 text-sm mt-1">
              Los productos son artesanales y se fabrican una vez confirmada la compra. El tiempo estimado de elaboracion y envio es de <strong>15 a 20 dias habiles</strong>.
            </p>
            <p v-else class="text-blue-700 text-sm mt-1">
              Tu pedido incluye productos con mas de 5 unidades. Para estas cantidades, el tiempo de elaboracion <strong>puede superar los 20 dias habiles</strong>. Una vez confirmada la compra, el artesano te contactara para coordinar el plazo de entrega.
            </p>
            <p class="text-blue-700 text-sm mt-2">
              Al confirmar, recibiras los <strong>datos de contacto del artesano</strong> para coordinar detalles como color, material, medidas y personalizacion.
            </p>
          </div>
        </div>
      </div>

      <!-- Columna derecha: Resumen -->
      <div class="lg:col-span-1">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sticky top-24">
          <h2 class="font-bold text-lg mb-4">Resumen del pedido</h2>

          <div class="space-y-4 mb-6">
            <div v-for="item in cartItems" :key="item.id" class="flex gap-3">
              <img :src="item.product?.images?.length ? storageUrl(item.product.images[0]) : 'https://placehold.co/60x60'" :alt="item.product?.name" class="w-14 h-14 object-cover rounded-lg flex-shrink-0" loading="lazy" decoding="async" />
              <div class="flex-grow min-w-0">
                <p class="font-semibold text-sm truncate">{{ item.product?.name }}</p>
                <p class="text-xs text-gray-500">Por {{ item.product?.artisan?.user?.name }}</p>
                <div class="flex justify-between mt-1">
                  <span class="text-xs text-gray-400">x{{ item.quantity }}</span>
                  <span class="font-bold text-sm">${{ (item.product?.price * item.quantity).toFixed(2) }}</span>
                </div>
              </div>
            </div>
          </div>

          <hr class="border-gray-100 mb-4" />

          <div class="space-y-2 text-sm">
            <div class="flex justify-between">
              <span class="text-gray-500">Subtotal</span>
              <span class="font-semibold">${{ subtotal.toFixed(2) }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-500">Envio</span>
              <span v-if="shippingCostSelected !== null" class="font-semibold">${{ shippingCostSelected.toFixed(2) }}</span>
              <span v-else class="text-gray-400 text-xs">Por calcular</span>
            </div>
          </div>

          <hr class="border-gray-100 my-4" />

          <div class="flex justify-between text-xl font-black">
            <span>Total</span>
            <span>${{ total.toFixed(2) }}</span>
          </div>

          <!-- Aviso cantidad > 5 por item -->
          <div v-if="hayMasDe5" class="mt-4 bg-amber-50 border border-amber-200 rounded-xl p-3">
            <p class="text-amber-700 text-xs font-semibold">Algunos productos superan las 5 unidades. El plazo de entrega sera mayor al habitual.</p>
          </div>

          <!-- Email no verificado -->
          <div v-if="!emailVerified" class="mt-4 bg-red-50 border border-red-200 rounded-xl p-3">
            <p class="text-red-700 text-xs font-semibold">Debes verificar tu email antes de comprar. Revisa tu casilla de correo.</p>
            <button @click="resendVerification" :disabled="resending" class="text-xs text-red-600 font-bold underline mt-1">
              {{ resending ? 'Enviando...' : 'Reenviar email de verificacion' }}
            </button>
            <p v-if="resendMsg" class="text-green-600 text-xs mt-1">{{ resendMsg }}</p>
          </div>

          <button
            @click="irAlPago"
            :disabled="shippingCostSelected === null || !emailVerified"
            class="btn-primary w-full mt-6 py-4 text-lg rounded-full disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Continuar al Pago
          </button>

          <p v-if="shippingCostSelected === null" class="text-center text-xs text-gray-400 mt-2">Calcula el envio para continuar</p>

          <router-link to="/carrito" class="block text-center text-sm text-artisan-accent font-bold mt-4 hover:underline">Volver al carrito</router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { useCartStore } from '../stores/cart'
import api, { storageUrl } from '../utils/api'

const router = useRouter()
const auth = useAuthStore()
const cartStore = useCartStore()

const cartItems = ref([])
const loading = ref(true)
const processing = ref(false)
const checkoutError = ref('')
const step = ref('form') // 'form' o 'payment'
const resending = ref(false)
const resendMsg = ref('')

const emailVerified = computed(() => !!auth.user?.email_verified_at)

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

const calculandoEnvio = ref(false)
const shippingRates = ref(null)
const shippingError = ref('')
const selectedShipping = ref(null)
const usandoSimulado = ref(false)

const errors = reactive({})

const form = reactive({
  shipping_name: '',
  shipping_address: '',
  shipping_city: '',
  shipping_province: '',
  shipping_postal_code: '',
  shipping_phone: '',
})

const provincias = [
  'Buenos Aires', 'CABA', 'Catamarca', 'Chaco', 'Chubut', 'Córdoba',
  'Corrientes', 'Entre Ríos', 'Formosa', 'Jujuy', 'La Pampa', 'La Rioja',
  'Mendoza', 'Misiones', 'Neuquén', 'Río Negro', 'Salta', 'San Juan',
  'San Luis', 'Santa Cruz', 'Santa Fe', 'Santiago del Estero',
  'Tierra del Fuego', 'Tucumán'
]

const shippingSimuladas = [
  { serviceDescription: 'Envio estandar (Correo Argentino)', deliveryTime: '5 a 7', price: 8500 },
  { serviceDescription: 'Envio expreso (Correo Argentino)', deliveryTime: '2 a 4', price: 14500 },
]

const subtotal = computed(() => {
  return cartItems.value.reduce((acc, item) => acc + (item.product?.price * item.quantity), 0)
})

const shippingCostSelected = computed(() => {
  if (selectedShipping.value === null || !shippingRates.value) return null
  const rate = shippingRates.value[selectedShipping.value]
  return parseFloat(rate.price || rate.total || 0)
})

const total = computed(() => {
  return subtotal.value + (shippingCostSelected.value || 0)
})

const hayMasDe5 = computed(() => {
  return cartItems.value.some(item => item.quantity > 5)
})

onMounted(async () => {
  if (!auth.isAuthenticated) {
    router.push('/login')
    return
  }

  try {
    const res = await api.get('/cart')
    cartItems.value = res.data.items || []
    if (!cartItems.value.length) return
  } catch (error) {
    console.error('Error fetching cart', error)
  } finally {
    loading.value = false
  }

  if (auth.user) {
    form.shipping_name = auth.user.name || ''
    form.shipping_phone = auth.user.phone || ''
  }
})

const calcularEnvio = async () => {
  if (!form.shipping_postal_code) return
  calculandoEnvio.value = true
  shippingRates.value = null
  shippingError.value = ''
  selectedShipping.value = null
  usandoSimulado.value = false

  try {
    const firstProduct = cartItems.value[0]?.product
    const res = await api.post('/shipping-rates', {
      postal_code: form.shipping_postal_code,
      product_id: firstProduct?.id
    })
    if (res.data && res.data.length > 0) {
      shippingRates.value = res.data
    } else {
      // Sin resultados de la API, usar simuladas
      shippingRates.value = shippingSimuladas
      usandoSimulado.value = true
    }
  } catch (error) {
    // API no disponible, usar tarifas simuladas
    shippingRates.value = shippingSimuladas
    usandoSimulado.value = true
  } finally {
    calculandoEnvio.value = false
  }
}

const validate = () => {
  Object.keys(errors).forEach(k => delete errors[k])
  let valid = true

  const required = {
    shipping_name: 'El nombre es obligatorio',
    shipping_address: 'La direccion es obligatoria',
    shipping_city: 'La ciudad es obligatoria',
    shipping_province: 'La provincia es obligatoria',
    shipping_postal_code: 'El codigo postal es obligatorio',
    shipping_phone: 'El telefono es obligatorio',
  }

  for (const [field, msg] of Object.entries(required)) {
    if (!form[field]?.trim()) {
      errors[field] = msg
      valid = false
    }
  }

  return valid
}

const irAlPago = () => {
  if (!validate()) return
  if (shippingCostSelected.value === null) return
  checkoutError.value = ''
  step.value = 'payment'
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

const procesarPago = async () => {
  processing.value = true
  checkoutError.value = ''

  try {
    const res = await api.post('/orders/checkout', {
      ...form,
      shipping_cost: shippingCostSelected.value,
    })

    cartStore.clear()
    router.push({ name: 'order-confirmation', params: { id: res.data.id } })
  } catch (error) {
    checkoutError.value = error.response?.data?.message || 'Error al procesar el pedido. Intenta nuevamente.'
  } finally {
    processing.value = false
  }
}
</script>
