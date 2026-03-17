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
      <div v-if="loading" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-12">
        <div v-for="i in 6" :key="i" class="aspect-[4/5] bg-white animate-pulse rounded-[2.5rem] shadow-sm"></div>
      </div>

      <div v-else-if="products.length === 0" class="flex flex-col items-center justify-center py-40 bg-white rounded-[4rem] border border-dashed border-gray-200">
        <div class="text-6xl mb-6">🔍</div>
        <p class="text-2xl font-black text-artisan-dark">Aún no hay obras aquí</p>
        <p class="text-gray-400 mt-2">Prueba seleccionando otra categoría</p>
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
      api.get(`/products?category_id=${selectedCategory.value || ''}`)
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
