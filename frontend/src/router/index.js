import { createRouter, createWebHistory } from 'vue-router'
import Home from '../views/Home.vue'
import Catalog from '../views/Catalog.vue'
import ProductDetail from '../views/ProductDetail.vue'

const routes = [
  { path: '/', name: 'home', component: Home, meta: { title: 'Inicio' } },
  { path: '/catalogo', name: 'catalog', component: Catalog, meta: { title: 'Catalogo' } },
  { path: '/producto/:slug', name: 'product-detail', component: ProductDetail, meta: { title: 'Producto' } },
  { path: '/carrito', name: 'cart', component: () => import('../views/Cart.vue'), meta: { title: 'Carrito' } },
  { path: '/nosotros', name: 'nosotros', component: () => import('../views/Nosotros.vue'), meta: { title: 'Nosotros' } },
  { path: '/contacto', name: 'contact', component: () => import('../views/Contact.vue'), meta: { title: 'Contacto' } },
  { path: '/preguntas-frecuentes', name: 'faq', component: () => import('../views/FAQ.vue'), meta: { title: 'Preguntas Frecuentes' } },
  { path: '/login', name: 'login', component: () => import('../views/auth/Login.vue'), meta: { title: 'Ingresar' } },
  { path: '/registro', name: 'register', component: () => import('../views/auth/Register.vue'), meta: { title: 'Registrarse' } },
  { path: '/terminos-y-condiciones', name: 'terms', component: () => import('../views/legal/TermsConditions.vue'), meta: { title: 'Terminos y Condiciones' } },
  { path: '/politica-de-privacidad', name: 'privacy', component: () => import('../views/legal/PrivacyPolicy.vue'), meta: { title: 'Politica de Privacidad' } },
  { path: '/politica-de-devoluciones', name: 'returns', component: () => import('../views/legal/ReturnsPolicy.vue'), meta: { title: 'Politica de Devoluciones' } },
  { path: '/recuperar-contrasena', name: 'forgot-password', component: () => import('../views/auth/ForgotPassword.vue'), meta: { title: 'Recuperar Contrasena' } },
  { path: '/restablecer-contrasena/:token', name: 'reset-password', component: () => import('../views/auth/ResetPassword.vue'), meta: { title: 'Restablecer Contrasena' } },
  { path: '/verificar-email', name: 'verify-email', component: () => import('../views/auth/VerifyEmail.vue'), meta: { title: 'Verificar Email' } },
  { path: '/favoritos', name: 'wishlist', component: () => import('../views/Wishlist.vue'), meta: { requiresAuth: true, title: 'Mis Favoritos' } },
  { path: '/checkout', name: 'checkout', component: () => import('../views/Checkout.vue'), meta: { requiresAuth: true, title: 'Finalizar Compra' } },
  { path: '/pedido-confirmado/:id', name: 'order-confirmation', component: () => import('../views/OrderConfirmation.vue'), meta: { requiresAuth: true, title: 'Pedido Confirmado' } },
  { path: '/dashboard', name: 'dashboard', component: () => import('../views/admin/Dashboard.vue'), meta: { requiresAuth: true, roles: ['admin', 'presidente'], title: 'Panel Admin' } },
  { path: '/mi-perfil', name: 'profile', component: () => import('../views/Profile.vue'), meta: { requiresAuth: true, title: 'Mi Perfil' } },
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

router.beforeEach((to) => {
  const token = localStorage.getItem('token')
  const user = JSON.parse(localStorage.getItem('user') || 'null')

  if (to.meta.requiresAuth && !token) {
    return { name: 'login' }
  }

  if (to.meta.roles && user && !to.meta.roles.includes(user.role)) {
    if (user.role === 'admin') return true
    return { name: 'home' }
  }

  return true
})

router.afterEach((to) => {
  const base = 'Artesanos de Catriel'
  document.title = to.meta.title ? `${to.meta.title} | ${base}` : base
})

export default router
