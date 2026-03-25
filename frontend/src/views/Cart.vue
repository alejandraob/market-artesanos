<template>
  <div class="max-w-4xl mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-8 text-artisan-dark">Tu Carrito</h1>
    
    <div v-if="loading" class="text-center py-12">
      <p class="text-gray-500">Cargando carrito...</p>
    </div>

    <div v-else-if="!cartItems || cartItems.length === 0" class="bg-white rounded-lg shadow p-8 text-center">
      <p class="text-xl text-gray-600 mb-6">Tu carrito está vacío</p>
      <router-link to="/catalogo" class="btn-primary inline-block px-8 py-3">Volver al Catálogo</router-link>
    </div>

    <div v-else class="space-y-6">
      <div v-for="item in cartItems" :key="item.id" class="bg-white rounded-lg shadow p-6 flex items-center gap-6">
        <img :src="item.product?.images?.length ? storageUrl(item.product.images[0]) : 'https://placehold.co/100x100'" class="w-24 h-24 object-cover rounded" />
        <div class="flex-grow">
          <h3 class="font-bold text-lg">{{ item.product?.name }}</h3>
          <p class="text-gray-500">Por {{ item.product?.artisan?.user?.name }}</p>
          <div class="flex items-center gap-4 mt-2">
            <span class="font-bold text-artisan-accent">${{ item.product?.price }}</span>
            <div class="flex items-center border rounded">
              <button @click="updateQuantity(item, -1)" class="px-3 py-1 hover:bg-gray-100">-</button>
              <span class="px-3 py-1 border-x">{{ item.quantity }}</span>
              <button @click="updateQuantity(item, 1)" class="px-3 py-1 hover:bg-gray-100">+</button>
            </div>
          </div>
        </div>
        <button @click="removeItem(item.id)" class="text-red-500 hover:text-red-700">Eliminar</button>
      </div>

      <!-- Aviso producto a pedido -->
      <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 flex items-start gap-3">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <p class="text-blue-700 text-sm">Los productos son <strong>elaborados a pedido</strong>. Una vez confirmada la compra, recibiras los datos del artesano para coordinar detalles como color, material y personalizacion. El envio se realiza en un plazo de <strong>15 a 20 dias habiles</strong>.</p>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex justify-between items-center text-xl font-bold">
          <span>Total:</span>
          <span>${{ total }}</span>
        </div>
        <router-link to="/checkout" class="btn-primary w-full block text-center mt-6 py-3 text-lg rounded-full">Finalizar Compra</router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api, { storageUrl } from '../utils/api'
import { useCartStore } from '../stores/cart'

const cartStore = useCartStore()

const cartItems = ref([])
const loading = ref(true)

const total = computed(() => {
  return cartItems.value.reduce((acc, item) => acc + (item.product?.price * item.quantity), 0)
})

const fetchCart = async () => {
  try {
    const res = await api.get('/cart')
    cartItems.value = res.data.items || []
  } catch (error) {
    console.error('Error fetching cart', error)
  } finally {
    loading.value = false
  }
}

const updateQuantity = async (item, delta) => {
  const newQty = item.quantity + delta
  if (newQty < 1) return
  try {
    await api.put(`/cart/${item.id}`, { quantity: newQty })
    item.quantity = newQty
    cartStore.fetchCount()
  } catch (error) {
    console.error('Error updating quantity', error)
  }
}

const removeItem = async (id) => {
  try {
    await api.delete(`/cart/${id}`)
    cartItems.value = cartItems.value.filter(i => i.id !== id)
    cartStore.fetchCount()
  } catch (error) {
    console.error('Error removing item', error)
  }
}

onMounted(fetchCart)
</script>
