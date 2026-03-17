import { createRouter, createWebHistory } from 'vue-router'
import Home from '../views/Home.vue'
import Catalog from '../views/Catalog.vue'
import ProductDetail from '../views/ProductDetail.vue'

const routes = [
  { path: '/', name: 'home', component: Home },
  { path: '/catalogo', name: 'catalog', component: Catalog },
  { path: '/producto/:slug', name: 'product-detail', component: ProductDetail },
  { path: '/carrito', name: 'cart', component: () => import('../views/Cart.vue') },
  { path: '/nosotros', name: 'nosotros', component: () => import('../views/Nosotros.vue') },
  { path: '/login', name: 'login', component: () => import('../views/auth/Login.vue') },
  { path: '/registro', name: 'register', component: () => import('../views/auth/Register.vue') },
  { path: '/dashboard', name: 'dashboard', component: () => import('../views/admin/Dashboard.vue'), meta: { requiresAuth: true, roles: ['admin', 'presidente'] } },
  { path: '/mi-perfil', name: 'profile', component: () => import('../views/Profile.vue'), meta: { requiresAuth: true } },
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('token')
  const user = JSON.parse(localStorage.getItem('user') || 'null')

  if (to.meta.requiresAuth && !token) {
    return next({ name: 'login' })
  }

  if (to.meta.roles && user && !to.meta.roles.includes(user.role)) {
    // Admin siempre tiene acceso
    if (user.role === 'admin') return next()
    return next({ name: 'home' })
  }

  next()
})

export default router
