<template>
  <div class="max-w-5xl mx-auto px-4 py-12">
    <h1 class="text-3xl font-black text-artisan-dark mb-8">Mis Favoritos</h1>

    <div v-if="loading" class="text-center py-12 text-gray-500">Cargando...</div>

    <div v-else-if="wishlist.items.length === 0" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center">
      <p class="text-xl text-gray-600 mb-4">No tenes productos en favoritos</p>
      <router-link to="/catalogo" class="btn-primary inline-block px-8 py-3">Explorar catalogo</router-link>
    </div>

    <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
      <div v-for="item in wishlist.items" :key="item.id" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden group">
        <router-link :to="`/producto/${item.product?.slug}`" class="block">
          <img :src="item.product?.images?.length ? storageUrl(item.product.images[0]) : 'https://placehold.co/400x300'" class="w-full h-48 object-cover group-hover:scale-105 transition-transform duration-300" />
        </router-link>
        <div class="p-5">
          <router-link :to="`/producto/${item.product?.slug}`">
            <h3 class="font-bold text-artisan-dark hover:text-artisan-accent transition-colors">{{ item.product?.name }}</h3>
          </router-link>
          <p class="text-sm text-gray-500 mt-1">Por {{ item.product?.artisan?.user?.name }}</p>
          <div class="flex items-center justify-between mt-3">
            <span class="font-black text-lg text-artisan-dark">${{ item.product?.price }}</span>
            <button @click="removeFromWishlist(item.product_id)" class="text-red-400 hover:text-red-600 transition-colors">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor"><path d="M11.645 20.91l-.007-.003-.022-.012a15.247 15.247 0 01-.383-.218 25.18 25.18 0 01-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0112 5.052 5.5 5.5 0 0116.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 01-4.244 3.17 15.247 15.247 0 01-.383.219l-.022.012-.007.004-.003.001a.752.752 0 01-.704 0l-.003-.001z" /></svg>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useWishlistStore } from '../stores/wishlist'
import { storageUrl } from '../utils/api'

const wishlist = useWishlistStore()
const loading = ref(true)

const removeFromWishlist = async (productId) => {
  await wishlist.toggle(productId)
}

onMounted(async () => {
  await wishlist.fetch()
  loading.value = false
})
</script>
