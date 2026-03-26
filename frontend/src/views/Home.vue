<template>
  <div class="space-y-32">
    <!-- Premium Hero Section -->
    <section class="relative min-h-[80vh] flex items-center overflow-hidden rounded-[4rem] bg-white shadow-2xl border border-gray-100 group">
      <div class="absolute inset-0 bg-[url('https://images.unsplash.com/photo-1459411552884-8419124cb1bc?q=80&w=2000')] bg-cover bg-center opacity-10 group-hover:scale-105 transition-transform duration-1000"></div>
      
      <div class="relative z-10 grid grid-cols-1 lg:grid-cols-2 gap-12 p-12 md:p-24 items-center w-full">
        <div class="space-y-8">
          <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-artisan-accent/10 text-artisan-accent font-black uppercase tracking-widest text-[10px]">
            <span class="relative flex h-2 w-2">
              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-artisan-accent opacity-75"></span>
              <span class="relative inline-flex rounded-full h-2 w-2 bg-artisan-accent"></span>
            </span>
            Catriel, Río Negro
          </div>
          <h1 class="text-6xl md:text-8xl font-black text-artisan-dark leading-tight">
            Manos que <br/>
            <span class="text-transparent bg-clip-text bg-gradient-to-r from-artisan-brown to-artisan-accent">Cuentan</span> Historias
          </h1>
          <p class="text-xl text-gray-500 max-w-lg leading-relaxed font-light">
            Descubre piezas únicas, forjadas con el alma y la herencia de nuestra tierra. Un encuentro entre la tradición milenaria y el diseño contemporáneo.
          </p>
          <div class="flex flex-wrap gap-6 pt-4">
            <router-link to="/catalogo" class="btn-primary">
              Explorar Colección
            </router-link>
            <router-link to="/nosotros" class="px-8 py-4 rounded-full font-bold text-artisan-dark border-2 border-artisan-brown/20 hover:bg-artisan-brown hover:text-white transition-all">
              Nuestra Historia
            </router-link>
          </div>
        </div>
        <div class="hidden lg:flex justify-end order-first lg:order-last">
          <div class="relative w-full max-w-md aspect-square">
            <div class="absolute -inset-10 bg-artisan-accent/20 rounded-full blur-3xl animate-pulse"></div>
            <img src="/logo-sinfondo.webp" alt="Artesanos" class="relative w-full h-full object-contain drop-shadow-2xl" />
          </div>
        </div>
      </div>
    </section>

    <!-- Categories Showcase -->
    <section>
      <div class="text-center mb-20 space-y-4">
        <h2 class="text-4xl md:text-5xl font-black text-artisan-dark">Familias de Oficios</h2>
        <div class="h-1.5 w-24 bg-artisan-accent mx-auto rounded-full"></div>
      </div>
      <div class="grid grid-cols-2 md:grid-cols-5 gap-8">
        <router-link 
          v-for="cat in categories" 
          :key="cat.id" 
          :to="`/catalogo?categoria=${cat.id}`"
          class="group bg-white rounded-3xl p-10 text-center shadow-sm hover:shadow-2xl hover:-translate-y-4 transition-all duration-500 border border-gray-100 flex flex-col items-center"
        >
          <div class="h-24 w-24 bg-artisan-bg rounded-2xl mb-6 flex items-center justify-center text-4xl group-hover:rotate-12 group-hover:scale-125 transition-all duration-500 shadow-inner">
            <span>{{ getCategoryIcon(cat.name) }}</span>
          </div>
          <h3 class="font-black text-gray-800 uppercase text-xs tracking-[0.2em] group-hover:text-artisan-accent transition-colors">{{ cat.name }}</h3>
        </router-link>
      </div>
    </section>

    <!-- Featured Products Grid -->
    <section class="bg-white p-12 md:p-24 rounded-[4rem] shadow-xl border border-gray-50">
      <div class="flex flex-col md:flex-row justify-between items-end mb-20 gap-8">
        <div class="space-y-4">
          <h2 class="text-4xl md:text-5xl font-black text-artisan-dark leading-none">Selección Maestra</h2>
          <p class="text-gray-400 font-light">Obras seleccionadas por su excepcional técnica y acabado.</p>
        </div>
        <router-link to="/catalogo" class="group flex items-center gap-3 text-artisan-brown font-black uppercase tracking-widest text-sm hover:text-artisan-accent transition-colors">
          Ver Todo la Colección 
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 group-hover:translate-x-2 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
        </router-link>
      </div>
      
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-12">
        <ProductCard 
          v-for="product in featuredProducts" 
          :key="product.id" 
          :product="product" 
        />
      </div>
    </section>

    <!-- Process Section -->
    <section class="grid grid-cols-1 lg:grid-cols-2 gap-24 items-center py-20">
      <div class="relative order-last lg:order-first">
        <div class="absolute -inset-10 bg-artisan-brown/10 rounded-[4rem] blur-2xl"></div>
        <img src="/manos-bordado.webp" class="relative rounded-[3rem] shadow-2xl hover:scale-105 transition-all duration-700 object-cover w-full h-96" alt="Manos y bordado artesanal" />
      </div>
      <div class="space-y-10">
        <h2 class="text-5xl font-black text-artisan-dark">El Alma en el <span class="text-artisan-brown">Proceso</span></h2>
        <div class="space-y-8">
          <div class="flex gap-6">
            <div class="h-12 w-12 rounded-2xl bg-artisan-accent flex-shrink-0 flex items-center justify-center font-bold text-white text-xl">1</div>
            <div>
              <h4 class="text-xl font-bold mb-2">Materia Prima Noble</h4>
              <p class="text-gray-500 font-light">Seleccionamos cuidadosamente recursos naturales de nuestra región, honrando a la Pachamama.</p>
            </div>
          </div>
          <div class="flex gap-6">
            <div class="h-12 w-12 rounded-2xl bg-artisan-brown flex-shrink-0 flex items-center justify-center font-bold text-white text-xl">2</div>
            <div>
              <h4 class="text-xl font-bold mb-2">Técnica Ancestral</h4>
              <p class="text-gray-500 font-light">Transmitimos conocimientos de generación en generación, preservando la identidad de Catriel.</p>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '../utils/api'
import ProductCard from '../components/common/ProductCard.vue'

const categories = ref([])
const featuredProducts = ref([])

const getCategoryIcon = (name) => {
  const icons = {
    'Textiles y Tejidos': '🧶',
    'Joyería y Accesorios': '💍',
    'Hogar y Decoración': '🏺',
    'Materiales Nobles': '🔨',
    'Arte y Juguetes': '🎨'
  }
  return icons[name] || '✨'
}

onMounted(async () => {
  try {
    const [catsRes, prodsRes] = await Promise.all([
      api.get('/categories'),
      api.get('/products?featured=1')
    ])
    categories.value = catsRes.data
    featuredProducts.value = prodsRes.data.data
  } catch (error) {
    console.error("Error loading home data", error)
  }
})
</script>
