<template>
  <nav class="sticky top-0 z-50 backdrop-blur-xl bg-white/70 border-b border-artisan-brown/10 shadow-sm transition-all duration-300">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between items-center h-24">
        
        <!-- Brand / Logo -->
        <div class="flex-shrink-0 flex items-center group cursor-pointer z-50">
          <router-link to="/" class="flex items-center gap-4 outline-none">
            <div class="relative w-16 h-16 md:w-20 md:h-20 flex justify-center items-center drop-shadow-md group-hover:scale-110 transition-transform duration-500 ease-in-out">
               <img src="/logo-sinfondo.png" alt="Asociación de Artesanos Logo" class="h-full w-auto object-contain" />
            </div>
            <div class="hidden md:flex flex-col">
              <span class="font-extrabold text-2xl md:text-3xl text-transparent bg-clip-text bg-gradient-to-br from-artisan-brown to-artisan-green tracking-tight leading-none group-hover:from-artisan-accent group-hover:to-artisan-brown transition-all duration-500">Asociación de</span>
              <span class="font-semibold text-sm md:text-base text-gray-500 tracking-[0.2em] uppercase leading-none mt-1">Artesanos</span>
            </div>
          </router-link>
        </div>

        <!-- Desktop Navigation -->
        <div class="hidden md:flex space-x-8 items-center">
          <router-link to="/catalogo" class="nav-link text-lg font-medium text-gray-700 hover:text-artisan-accent transition-colors">
            Colección
          </router-link>
          <router-link to="/nosotros" class="nav-link text-lg font-medium text-gray-700 hover:text-artisan-accent transition-colors">
            Nosotros
          </router-link>
          
          <router-link to="/carrito" class="relative group p-2 hover:bg-artisan-accent/10 rounded-full transition-colors flex items-center justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-artisan-brown group-hover:text-artisan-accent transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
            </svg>
          </router-link>

          <!-- Authentication Controls -->
          <div class="flex items-center space-x-4 pl-4 border-l border-gray-200">
            <template v-if="auth.isAuthenticated">
              <div class="relative group">
                <router-link :to="auth.user?.role === 'cliente' ? '/mi-perfil' : '/dashboard'" class="flex items-center gap-3 py-2 px-3 hover:bg-gray-50 rounded-lg transition-colors border border-transparent hover:border-gray-200">
                  <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-artisan-brown to-artisan-accent p-[2px] shadow-sm group-hover:shadow-md transition-shadow">
                    <div class="w-full h-full bg-white rounded-full flex items-center justify-center font-bold text-artisan-brown text-lg">
                      {{ auth.user?.name?.charAt(0) || 'U' }}
                    </div>
                  </div>
                  <div class="flex flex-col text-left">
                     <span class="text-sm font-semibold text-artisan-dark line-clamp-1 max-w-[120px]">{{ auth.user?.name }}</span>
                     <span class="text-xs text-artisan-accent font-medium uppercase">{{ auth.user?.role }}</span>
                  </div>
                </router-link>
                <div class="absolute right-0 w-48 mt-2 py-2 bg-white rounded-xl shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 border border-gray-100 transform origin-top-right scale-95 group-hover:scale-100">
                  <router-link v-if="auth.user?.role === 'admin' || auth.user?.role === 'presidente'" to="/dashboard" class="block px-4 py-2 text-sm text-gray-700 hover:bg-artisan-bg hover:text-artisan-brown transition-colors">Mi Panel</router-link>
                  <router-link to="/mi-perfil" class="block px-4 py-2 text-sm text-gray-700 hover:bg-artisan-bg hover:text-artisan-brown transition-colors">Mi Perfil</router-link>
                  <button @click="logout" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors">Cerrar Sesion</button>
                </div>
              </div>
            </template>
            <template v-else>
              <router-link to="/login" class="text-artisan-dark hover:text-artisan-brown font-medium px-4 py-2 transition-colors">Ingresar</router-link>
              <router-link to="/registro" class="bg-artisan-brown text-white font-medium hover:bg-[#5b3a27] px-6 py-2.5 rounded-full shadow-md hover:shadow-lg transition-all transform hover:-translate-y-0.5">
                Crear Cuenta
              </router-link>
            </template>
          </div>
        </div>
      </div>
    </div>
  </nav>
</template>

<script setup>
import { useAuthStore } from '../../stores/auth'
import { useRouter } from 'vue-router'

const auth = useAuthStore()
const router = useRouter()

const logout = () => {
  auth.logout()
  router.push('/login')
}
</script>
