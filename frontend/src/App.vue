<template>
  <div class="min-h-screen flex flex-col bg-artisan-bg selection:bg-artisan-accent selection:text-white font-sans text-artisan-dark">
    <AppNavbar />

    <main class="flex-grow container mx-auto px-4 py-12">
      <router-view v-slot="{ Component }">
        <transition name="fade" mode="out-in">
          <component :is="Component" />
        </transition>
      </router-view>
    </main>

    <AppFooter />
    <AppToast />
  </div>
</template>

<script setup>
import { onMounted } from 'vue'
import { useAuthStore } from './stores/auth'
import { useWishlistStore } from './stores/wishlist'
import AppNavbar from './components/layout/AppNavbar.vue'
import AppFooter from './components/layout/AppFooter.vue'
import AppToast from './components/common/AppToast.vue'

const auth = useAuthStore()
const wishlist = useWishlistStore()

onMounted(() => {
  auth.fetchUser()
  if (auth.isAuthenticated) wishlist.fetch()
})
</script>

<style>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
