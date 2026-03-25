<template>
  <div class="flex flex-col lg:flex-row gap-16">
    <!-- Premium Sidebar Filters -->
    <aside class="w-full lg:w-80 space-y-12 shrink-0">
      <div class="bg-white p-10 rounded-[2.5rem] shadow-xl border border-gray-50 space-y-10 sticky top-32">
        <div>
          <h3 class="font-black text-xs uppercase tracking-[0.3em] mb-8 text-artisan-brown">Categorías</h3>
          <div class="space-y-2">
            <button 
              @click="setCategory(null)"
              class="group flex items-center justify-between w-full text-left px-5 py-3 rounded-2xl transition-all duration-300"
              :class="!selectedCategory ? 'bg-artisan-brown text-white shadow-lg' : 'hover:bg-artisan-bg text-gray-500'"
            >
              <span class="font-bold">Todas las Obras</span>
              <span class="text-xs opacity-50">✨</span>
            </button>

            <div v-for="cat in categories" :key="cat.id" class="space-y-1">
              <!-- Parent Category -->
              <button 
                @click="toggleParent(cat.id); setCategory(cat.id)"
                class="group flex items-center justify-between w-full text-left px-5 py-3 rounded-2xl transition-all duration-300"
                :class="selectedCategory == cat.id ? 'bg-artisan-brown text-white shadow-lg' : 'hover:bg-artisan-bg text-gray-500'"
              >
                <span class="font-bold">{{ cat.name }}</span>
                <span v-if="cat.subcategories && cat.subcategories.length" class="text-xs transition-transform duration-300" :class="expandedParents.includes(cat.id) ? 'rotate-90' : ''">▶</span>
              </button>

              <!-- Subcategories -->
              <div v-if="cat.subcategories && cat.subcategories.length && expandedParents.includes(cat.id)" class="pl-6 space-y-1">
                <button
                  v-for="sub in cat.subcategories"
                  :key="sub.id"
                  @click="setCategory(sub.id)"
                  class="group flex items-center w-full text-left px-4 py-2 rounded-xl text-sm transition-all duration-300"
                  :class="selectedCategory == sub.id ? 'bg-artisan-accent text-white shadow' : 'hover:bg-artisan-bg text-gray-400'"
                >
                  <span class="font-semibold">{{ sub.name }}</span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </aside>

    <!-- Product Grid Area -->
    <main class="flex-grow">
      <!-- Buscador -->
      <div class="mb-8">
        <div class="relative">
          <input
            v-model="searchQuery"
            @input="onSearchInput"
            type="text"
            placeholder="Buscar productos..."
            class="w-full bg-white border border-gray-200 rounded-2xl pl-12 pr-4 py-4 outline-none focus:ring-2 focus:ring-artisan-accent/50 focus:border-artisan-accent transition-all text-sm shadow-sm"
          />
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400 absolute left-4 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
          <button v-if="searchQuery" @click="clearSearch" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </div>

      <div v-if="loading" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-12">
        <div v-for="i in 6" :key="i" class="aspect-[4/5] bg-white animate-pulse rounded-[2.5rem] shadow-sm"></div>
      </div>

      <div v-else-if="products.length === 0" class="flex flex-col items-center justify-center py-40 bg-white rounded-[4rem] border border-dashed border-gray-200">
        <div class="text-6xl mb-6">🔍</div>
        <p class="text-2xl font-black text-artisan-dark">No se encontraron productos</p>
        <p class="text-gray-400 mt-2">{{ searchQuery ? 'Proba con otros terminos de busqueda' : 'Proba seleccionando otra categoria' }}</p>
      </div>

      <div v-else class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-12">
        <transition-group name="list">
          <ProductCard 
            v-for="product in products" 
            :key="product.id" 
            :product="product"
            @add-to-cart="handleAddToCart"
          />
        </transition-group>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '../utils/api'
import ProductCard from '../components/common/ProductCard.vue'

const route = useRoute()
const router = useRouter()
const categories = ref([])
const products = ref([])
const loading = ref(true)
const selectedCategory = ref(route.query.categoria || null)
const expandedParents = ref([])
const searchQuery = ref(route.query.buscar || '')
let searchTimeout = null

const toggleParent = (id) => {
  const idx = expandedParents.value.indexOf(id)
  if (idx >= 0) {
    expandedParents.value.splice(idx, 1)
  } else {
    expandedParents.value.push(id)
  }
}

const fetchData = async () => {
  loading.value = true
  try {
    const [catsRes, prodsRes] = await Promise.all([
      api.get('/categories'),
      api.get(`/products?category_id=${selectedCategory.value || ''}&search=${searchQuery.value || ''}`)
    ])
    categories.value = catsRes.data
    products.value = prodsRes.data.data
  } catch (error) {
    console.error("Error fetching catalog", error)
  } finally {
    loading.value = false
  }
}

const handleAddToCart = (product) => {
  // Add to cart logic or redirect to detail
  router.push(`/producto/${product.slug}`)
}

const onSearchInput = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    router.push({ query: { ...route.query, buscar: searchQuery.value || undefined } })
    fetchData()
  }, 400)
}

const clearSearch = () => {
  searchQuery.value = ''
  router.push({ query: { ...route.query, buscar: undefined } })
  fetchData()
}

const setCategory = (id) => {
  selectedCategory.value = id
  router.push({ query: { ...route.query, categoria: id } })
}

watch(() => route.query.categoria, (newVal) => {
  selectedCategory.value = newVal
  fetchData()
})

onMounted(fetchData)
</script>

<style scoped>
.list-enter-active,
.list-leave-active {
  transition: all 0.5s ease;
}
.list-enter-from,
.list-leave-to {
  opacity: 0;
  transform: translateY(30px);
}
</style>
