<template>
  <footer class="bg-artisan-dark text-white border-t-8 border-artisan-accent pt-20 pb-10">
    <div class="max-w-7xl mx-auto px-4 grid grid-cols-1 md:grid-cols-4 gap-12 mb-16">
      <div class="col-span-1 md:col-span-2 space-y-6">
        <div class="flex items-center gap-3">
          <img src="/logo-sinfondo.png" class="h-12 w-auto invert brightness-0" alt="Logo" />
          <span class="text-2xl font-bold tracking-tight">Asociación de Artesanos</span>
        </div>
        <p class="text-gray-400 max-w-sm leading-relaxed">
          Un espacio dedicado a preservar y difundir el arte manual de Catriel. Conectamos directamente a quienes crean con quienes valoran lo auténtico.
        </p>
      </div>
      <div>
        <h4 class="text-lg font-bold mb-6 text-artisan-accent">Explorar</h4>
        <ul class="space-y-4 text-gray-400">
          <li><router-link to="/catalogo" class="hover:text-white transition-colors">Catálogo</router-link></li>
          <li><router-link to="/nosotros" class="hover:text-white transition-colors">Nosotros</router-link></li>
          <li><router-link to="/login" class="hover:text-white transition-colors">Ingresar</router-link></li>
        </ul>
      </div>
      <div>
        <h4 class="text-lg font-bold mb-6 text-artisan-accent">Contacto</h4>
        <ul class="space-y-4 text-gray-400">
          <li class="flex items-center gap-3">📍 Catriel, Río Negro</li>
          <li class="flex items-center gap-3">✉️ asociaciondeartesanosdecatriel@gmail.com</li>
        </ul>
      </div>
    </div>
    <div class="max-w-7xl mx-auto px-4 pt-10 border-t border-white/10 text-center text-gray-500 text-sm">
      <p>&copy; {{ new Date().getFullYear() }} Mercado de Artesanos de Catriel. Tradición y Calidad.</p>
    </div>
  </footer>
</template>
