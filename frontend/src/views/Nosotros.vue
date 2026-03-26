<template>
  <div>
    <!-- Header -->
    <section class="text-center mb-20 pt-12">
      <h1 class="text-5xl font-extrabold text-artisan-dark mb-6">Nuestra Historia</h1>
      <p class="text-xl text-gray-600 leading-relaxed font-light italic max-w-2xl mx-auto">
        "Transformando la herencia de Catriel en piezas de valor eterno."
      </p>
    </section>

    <!-- Tradición que Late -->
    <div class="max-w-5xl mx-auto px-4 grid grid-cols-1 md:grid-cols-2 gap-16 items-center mb-24">
      <div class="space-y-6">
        <h2 class="text-3xl font-bold text-artisan-brown">Tradición que Late</h2>
        <p class="text-gray-700 leading-relaxed">
          El Mercado de Artesanos de Catriel nació con el propósito de unir las manos más talentosas de nuestra comunidad. Cada pieza que ves en este catálogo cuenta una historia de esfuerzo, paciencia y amor por lo hecho a mano.
        </p>
        <p class="text-gray-700 leading-relaxed">
          Nuestra organización fomenta el comercio justo, asegurando que cada artesano reciba el reconocimiento y la valoración que su arte merece, sin intermediarios que diluyan el valor del trabajo genuino.
        </p>
      </div>
      <div class="relative group">
        <div class="absolute -inset-4 bg-artisan-accent/10 rounded-3xl group-hover:bg-artisan-accent/20 transition-all duration-500 blur-xl"></div>
        <img src="/asociacion-artesanos.jpg" alt="Asociación de Artesanos de Catriel" class="relative rounded-3xl shadow-2xl object-cover h-96 w-full" />
      </div>
    </div>

    <!-- Nuestros Valores — Full Width Bar -->
    <section class="w-screen relative left-1/2 right-1/2 -ml-[50vw] -mr-[50vw] bg-artisan-brown text-white py-16 mb-24">
      <div class="max-w-7xl mx-auto px-4">
        <h2 class="text-4xl font-black text-center mb-12">Nuestros Valores</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-12 text-center">
          <div class="space-y-3">
            <div class="text-5xl">🌱</div>
            <h3 class="text-xl font-bold">Sustentabilidad</h3>
            <p class="text-white/70 max-w-xs mx-auto">Utilizamos materiales nobles y técnicas que respetan nuestro entorno natural.</p>
          </div>
          <div class="space-y-3">
            <div class="text-5xl">🤝</div>
            <h3 class="text-xl font-bold">Comunidad</h3>
            <p class="text-white/70 max-w-xs mx-auto">Fortalecemos los lazos sociales a través del intercambio cultural y el apoyo mutuo.</p>
          </div>
          <div class="space-y-3">
            <div class="text-5xl">🏺</div>
            <h3 class="text-xl font-bold">Tradición</h3>
            <p class="text-white/70 max-w-xs mx-auto">Preservamos las técnicas ancestrales transmitidas de generación en generación.</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Nuestros Artesanos -->
    <section class="max-w-5xl mx-auto px-4 space-y-16 pb-24">
      <h2 class="text-4xl font-black text-artisan-dark text-center mb-8">Nuestros Artesanos</h2>

      <div v-if="artisans.length === 0" class="text-center text-gray-400 py-20 text-lg">
        Cargando artesanos...
      </div>

      <div v-for="artisan in artisans" :key="artisan.id" class="bg-white rounded-3xl shadow-lg border border-gray-100 p-8 md:p-12 flex flex-col md:flex-row gap-10 items-start">
        <!-- Avatar -->
        <div class="flex-shrink-0">
          <div class="w-32 h-32 rounded-full bg-gradient-to-tr from-artisan-brown to-artisan-accent p-[3px] shadow-xl">
            <div class="w-full h-full rounded-full bg-artisan-bg flex items-center justify-center overflow-hidden">
              <img
                v-if="artisan.photo"
                :src="storageUrl(artisan.photo)"
                :alt="artisan.user?.name"
                class="w-full h-full object-cover rounded-full"
                loading="lazy"
                decoding="async"
              />
              <span v-else class="text-5xl font-black text-artisan-brown">{{ artisan.user?.name?.charAt(0) || '?' }}</span>
            </div>
          </div>
        </div>

        <!-- Info -->
        <div class="flex-grow space-y-4 min-w-0">
          <div>
            <h3 class="text-2xl font-black text-artisan-dark">{{ artisan.user?.name }}</h3>
            <p class="text-artisan-accent font-bold uppercase tracking-wider text-sm">{{ artisan.specialty || 'Artesano' }}</p>
          </div>
          <p class="text-gray-600 leading-relaxed">{{ artisan.bio || 'Este artesano aún no ha escrito su historia.' }}</p>

          <!-- Mini Gallery Carousel (hidden if no products with images) -->
          <div v-if="getArtisanImages(artisan).length > 0" class="pt-4">
            <div class="relative">
              <div class="flex gap-4 overflow-x-auto pb-3 scrollbar-hide snap-x snap-mandatory">
                <button 
                  v-for="(img, idx) in getArtisanImages(artisan)" 
                  :key="idx"
                  @click="openLightbox(img)"
                  class="flex-shrink-0 snap-start w-28 h-28 rounded-xl overflow-hidden border-2 border-transparent hover:border-artisan-accent transition-all shadow-sm hover:shadow-lg cursor-pointer"
                >
                  <img :src="storageUrl(img)" class="w-full h-full object-cover" loading="lazy" decoding="async" />
                </button>
              </div>
              <!-- Scroll indicator dots -->
              <div v-if="getArtisanImages(artisan).length > 4" class="flex justify-center gap-1 mt-3">
                <span class="w-2 h-2 bg-artisan-brown/30 rounded-full"></span>
                <span class="w-2 h-2 bg-artisan-brown/60 rounded-full"></span>
                <span class="w-2 h-2 bg-artisan-brown/30 rounded-full"></span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Lightbox -->
    <div v-if="lightboxImage" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-[200] flex items-center justify-center p-8 cursor-pointer" @click="lightboxImage = null">
      <button class="absolute top-6 right-8 text-white text-4xl font-bold hover:scale-125 transition-transform">&times;</button>
      <img :src="storageUrl(lightboxImage)" class="max-w-full max-h-full rounded-2xl shadow-2xl object-contain" @click.stop />
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api, { storageUrl } from '../utils/api'

const artisans = ref([])
const lightboxImage = ref(null)

onMounted(async () => {
  try {
    const res = await api.get('/artisans')
    artisans.value = res.data
  } catch (error) {
    console.error("Error loading artisans", error)
  }
})

const getArtisanImages = (artisan) => {
  if (!artisan.products) return []
  return artisan.products
    .filter(p => p.images && p.images.length > 0)
    .flatMap(p => p.images)
}

const openLightbox = (img) => {
  lightboxImage.value = img
}
</script>

<style scoped>
.scrollbar-hide::-webkit-scrollbar {
  display: none;
}
.scrollbar-hide {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>
