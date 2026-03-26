<template>
  <div class="group bg-white rounded-3xl overflow-hidden shadow-sm hover:shadow-2xl transition-all duration-500 border border-gray-100 flex flex-col h-full relative">
    <div class="relative aspect-[4/5] overflow-hidden">
      <img 
        :src="product.images ? storageUrl(product.images[0]) : 'https://placehold.co/400x500?text=Artesanía'" 
        class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700 ease-out"
        :alt="product.name"
      />
      <div class="absolute inset-0 bg-gradient-to-t from-artisan-dark/60 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500 flex items-end p-6">
         <router-link 
            :to="`/producto/${product.slug}`" 
            class="w-full bg-white text-artisan-dark font-bold py-3 rounded-xl text-center transform translate-y-4 group-hover:translate-y-0 transition-transform duration-500"
          >
            Ver Detalles
          </router-link>
      </div>
      <div v-if="product.is_featured" class="absolute top-4 left-4 bg-artisan-accent text-white text-[10px] font-black uppercase tracking-widest px-3 py-1 rounded-full shadow-lg">
        Destacado
      </div>
      <button
        v-if="authStore.isAuthenticated"
        @click.prevent="toggleWishlist"
        class="absolute top-4 right-4 w-9 h-9 bg-white/80 backdrop-blur-sm rounded-full flex items-center justify-center shadow-md hover:scale-110 transition-transform z-10"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transition-colors" :class="isInWishlist ? 'text-red-500' : 'text-gray-400'" :fill="isInWishlist ? 'currentColor' : 'none'" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
        </svg>
      </button>
      <div class="absolute bottom-3 left-3 right-3 bg-black/50 backdrop-blur-sm text-white text-[10px] font-medium px-3 py-1.5 rounded-lg text-center group-hover:opacity-0 transition-opacity">
        Imagen ilustrativa de muestra
      </div>
    </div>

    <div class="p-6 flex flex-col flex-grow">
      <div class="flex justify-between items-start mb-2">
        <h3 class="font-bold text-xl text-artisan-dark group-hover:text-artisan-brown transition-colors">{{ product.name }}</h3>
        <span class="font-black text-xl text-artisan-accent">${{ product.price }}</span>
      </div>
      <p class="text-sm text-gray-500 mb-4 italic">Por {{ product.artisan?.user?.name }}</p>
      
      <div class="mt-auto pt-4 border-t border-gray-50 flex items-center justify-between">
        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">{{ product.category?.name }}</span>
        <button @click="$emit('add-to-cart', product)" class="text-artisan-brown hover:scale-125 transition-transform">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { storageUrl } from '../../utils/api'
import { useAuthStore } from '../../stores/auth'
import { useWishlistStore } from '../../stores/wishlist'

const authStore = useAuthStore()
const wishlistStore = useWishlistStore()

const props = defineProps({
  product: {
    type: Object,
    required: true
  }
})

defineEmits(['add-to-cart'])

const isInWishlist = computed(() => wishlistStore.isInWishlist(props.product.id))

const toggleWishlist = () => {
  wishlistStore.toggle(props.product.id)
}
</script>
