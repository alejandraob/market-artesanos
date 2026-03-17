<template>
  <div class="-mx-4 -my-12 min-h-screen flex">
    <!-- Sidebar -->
    <aside class="w-64 bg-white border-r border-gray-200 flex flex-col shrink-0">
      <div class="p-6 border-b border-gray-100">
        <h2 class="text-lg font-black text-artisan-brown">Panel Presidente</h2>
        <p class="text-sm text-gray-500 mt-1">{{ auth.user?.name }}</p>
      </div>
      <nav class="flex-1 p-4 space-y-1">
        <button
          v-for="item in menuItems"
          :key="item.key"
          @click="activeSection = item.key"
          class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-left transition-all duration-200"
          :class="activeSection === item.key
            ? 'bg-artisan-brown text-white shadow-md'
            : 'text-gray-600 hover:bg-artisan-bg hover:text-artisan-brown'"
        >
          <span class="text-xl">{{ item.icon }}</span>
          <span class="font-semibold text-sm">{{ item.label }}</span>
          <span
            v-if="item.badge"
            class="ml-auto text-xs font-bold px-2 py-0.5 rounded-full"
            :class="activeSection === item.key ? 'bg-white/20 text-white' : 'bg-artisan-accent/10 text-artisan-accent'"
          >{{ item.badge }}</span>
        </button>
      </nav>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 bg-artisan-bg p-8 overflow-y-auto">
      <!-- RESUMEN -->
      <section v-if="activeSection === 'resumen'">
        <h1 class="text-2xl font-black text-artisan-dark mb-6">Resumen General</h1>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
          <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100">
            <p class="text-xs text-gray-400 uppercase font-bold tracking-wider">Artesanos</p>
            <p class="text-3xl font-black mt-1">{{ artisans.length }}</p>
          </div>
          <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100">
            <p class="text-xs text-gray-400 uppercase font-bold tracking-wider">Productos</p>
            <p class="text-3xl font-black mt-1">{{ allProducts.length }}</p>
          </div>
          <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100">
            <p class="text-xs text-gray-400 uppercase font-bold tracking-wider">Clientes</p>
            <p class="text-3xl font-black mt-1">{{ clients.length }}</p>
          </div>
          <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100">
            <p class="text-xs text-gray-400 uppercase font-bold tracking-wider">Pedidos</p>
            <p class="text-3xl font-black mt-1 text-artisan-accent">{{ orders.length }}</p>
          </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h3 class="font-bold text-gray-700 mb-4">Ultimos Pedidos</h3>
            <div v-if="orders.length === 0" class="text-gray-400 text-sm py-4">Sin pedidos aun</div>
            <div v-else class="space-y-3">
              <div v-for="order in orders.slice(0, 5)" :key="order.id" class="flex items-center justify-between py-2 border-b border-gray-50 last:border-0">
                <div>
                  <p class="font-semibold text-sm">#{{ order.id }}</p>
                  <p class="text-xs text-gray-400">{{ formatDate(order.created_at) }}</p>
                </div>
                <span class="text-xs font-bold px-2 py-1 rounded-full" :class="statusClass(order.status)">{{ statusLabel(order.status) }}</span>
                <span class="font-bold text-sm">${{ order.total }}</span>
              </div>
            </div>
          </div>
          <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h3 class="font-bold text-gray-700 mb-4">Productos con Bajo Stock</h3>
            <div v-if="lowStockProducts.length === 0" class="text-gray-400 text-sm py-4">Todos los productos tienen stock suficiente</div>
            <div v-else class="space-y-3">
              <div v-for="prod in lowStockProducts" :key="prod.id" class="flex items-center justify-between py-2 border-b border-gray-50 last:border-0">
                <span class="font-semibold text-sm">{{ prod.name }}</span>
                <span class="text-xs font-bold px-2 py-1 rounded-full bg-red-100 text-red-700">{{ prod.stock }} unidades</span>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- ARTESANOS -->
      <section v-if="activeSection === 'artesanos'">
        <div class="flex justify-between items-center mb-6">
          <h1 class="text-2xl font-black text-artisan-dark">Artesanos</h1>
          <button @click="openArtisanModal()" class="btn-primary text-sm py-2 px-6">+ Nuevo Artesano</button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
          <div v-for="art in artisans" :key="art.id" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow">
            <div class="h-40 bg-gradient-to-br from-artisan-brown/10 to-artisan-accent/10 flex items-center justify-center relative">
              <img
                v-if="art.photo"
                :src="storageUrl(art.photo)"
                class="w-full h-full object-cover"
                :alt="art.user?.name"
              />
              <div v-else class="w-20 h-20 bg-artisan-brown/20 rounded-full flex items-center justify-center">
                <span class="text-3xl font-black text-artisan-brown/50">{{ art.user?.name?.charAt(0) }}</span>
              </div>
              <span
                class="absolute top-3 right-3 text-xs font-bold px-2 py-1 rounded-full"
                :class="art.is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'"
              >{{ art.is_active ? 'Activo' : 'Inactivo' }}</span>
            </div>
            <div class="p-5">
              <h3 class="font-bold text-lg">{{ art.user?.name }}</h3>
              <p class="text-sm text-artisan-accent font-semibold">{{ art.specialty }}</p>
              <div v-if="art.categories?.length" class="flex flex-wrap gap-1 mt-2">
                <span v-for="cat in art.categories" :key="cat.id" class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-artisan-brown/10 text-artisan-brown">{{ cat.name }}</span>
              </div>
              <p class="text-xs text-gray-400 mt-1">{{ art.location || 'Sin ubicacion' }}</p>
              <p class="text-xs text-gray-400">{{ art.products_count ?? art.products?.length ?? 0 }} productos</p>
              <div class="flex gap-2 mt-4">
                <button @click="openArtisanModal(art)" class="flex-1 text-center text-sm font-bold py-2 rounded-xl border border-artisan-brown text-artisan-brown hover:bg-artisan-brown hover:text-white transition-colors">Editar</button>
                <button @click="deleteArtisan(art.id)" class="text-sm font-bold py-2 px-3 rounded-xl border border-red-300 text-red-500 hover:bg-red-500 hover:text-white transition-colors">Eliminar</button>
              </div>
            </div>
          </div>
        </div>

        <div v-if="artisans.length === 0" class="text-center py-16 text-gray-400">
          <p class="text-5xl mb-4">🧑‍🎨</p>
          <p class="font-semibold">No hay artesanos registrados</p>
          <p class="text-sm">Agrega el primer artesano para comenzar</p>
        </div>
      </section>

      <!-- PRODUCTOS -->
      <section v-if="activeSection === 'productos'">
        <div class="flex justify-between items-center mb-6">
          <h1 class="text-2xl font-black text-artisan-dark">Productos</h1>
          <button @click="openProductModal()" class="btn-primary text-sm py-2 px-6">+ Nuevo Producto</button>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
          <table class="w-full text-left">
            <thead class="bg-gray-50 border-b border-gray-100">
              <tr>
                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase">Producto</th>
                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase">Artesano</th>
                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase">Categoria</th>
                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase">Precio</th>
                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase">Stock</th>
                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase">Estado</th>
                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase text-right">Acciones</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
              <tr v-for="prod in allProducts" :key="prod.id" class="hover:bg-gray-50/50 transition-colors">
                <td class="px-6 py-4">
                  <div class="flex items-center gap-3">
                    <img :src="prod.images?.length ? storageUrl(prod.images[0]) : 'https://placehold.co/40x40?text=...'" class="w-10 h-10 rounded-lg object-cover" />
                    <div>
                      <p class="font-semibold text-sm">{{ prod.name }}</p>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 text-sm text-gray-600">{{ prod.artisan?.user?.name || '-' }}</td>
                <td class="px-6 py-4 text-sm text-gray-600">{{ prod.category?.name || '-' }}</td>
                <td class="px-6 py-4 font-bold text-sm">${{ prod.price }}</td>
                <td class="px-6 py-4">
                  <span class="text-sm font-semibold" :class="prod.stock <= 5 ? 'text-red-500' : 'text-gray-700'">{{ prod.stock }}</span>
                </td>
                <td class="px-6 py-4">
                  <button
                    @click="toggleProductActive(prod)"
                    class="text-xs font-bold px-3 py-1 rounded-full cursor-pointer transition-colors"
                    :class="prod.is_active ? 'bg-green-100 text-green-700 hover:bg-green-200' : 'bg-red-100 text-red-700 hover:bg-red-200'"
                  >{{ prod.is_active ? 'Activo' : 'Inactivo' }}</button>
                </td>
                <td class="px-6 py-4 text-right">
                  <div class="flex gap-2 justify-end">
                    <button @click="openProductModal(prod)" class="text-xs font-bold px-3 py-1.5 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-100 transition-colors">Editar</button>
                    <button @click="deleteProduct(prod.id)" class="text-xs font-bold px-3 py-1.5 rounded-lg border border-red-200 text-red-500 hover:bg-red-50 transition-colors">Eliminar</button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
          <div v-if="allProducts.length === 0" class="text-center py-12 text-gray-400">
            <p class="text-4xl mb-3">📦</p>
            <p class="font-semibold">No hay productos</p>
          </div>
        </div>
      </section>

      <!-- CLIENTES -->
      <section v-if="activeSection === 'clientes'">
        <h1 class="text-2xl font-black text-artisan-dark mb-6">Clientes Registrados</h1>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
          <table class="w-full text-left">
            <thead class="bg-gray-50 border-b border-gray-100">
              <tr>
                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase">Cliente</th>
                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase">Email</th>
                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase">Telefono</th>
                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase">Compras</th>
                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase">Registrado</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
              <tr v-for="client in clients" :key="client.id" class="hover:bg-gray-50/50 transition-colors">
                <td class="px-6 py-4">
                  <div class="flex items-center gap-3">
                    <div class="w-9 h-9 bg-artisan-accent/10 rounded-full flex items-center justify-center">
                      <span class="text-sm font-black text-artisan-accent">{{ client.name?.charAt(0) }}</span>
                    </div>
                    <span class="font-semibold text-sm">{{ client.name }}</span>
                  </div>
                </td>
                <td class="px-6 py-4 text-sm text-gray-600">{{ client.email }}</td>
                <td class="px-6 py-4 text-sm text-gray-600">{{ client.phone || '-' }}</td>
                <td class="px-6 py-4">
                  <span class="text-sm font-bold">{{ client.orders_count }}</span>
                </td>
                <td class="px-6 py-4 text-sm text-gray-400">{{ formatDate(client.created_at) }}</td>
              </tr>
            </tbody>
          </table>
          <div v-if="clients.length === 0" class="text-center py-12 text-gray-400">
            <p class="text-4xl mb-3">👥</p>
            <p class="font-semibold">No hay clientes registrados</p>
          </div>
        </div>
      </section>

      <!-- PEDIDOS -->
      <section v-if="activeSection === 'pedidos'">
        <h1 class="text-2xl font-black text-artisan-dark mb-6">Pedidos</h1>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
          <table class="w-full text-left">
            <thead class="bg-gray-50 border-b border-gray-100">
              <tr>
                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase"># Pedido</th>
                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase">Fecha</th>
                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase">Productos</th>
                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase">Total</th>
                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase">Envio</th>
                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase">Estado</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
              <tr v-for="order in orders" :key="order.id" class="hover:bg-gray-50/50 transition-colors">
                <td class="px-6 py-4 font-bold text-sm">#{{ order.id }}</td>
                <td class="px-6 py-4 text-sm text-gray-600">{{ formatDate(order.created_at) }}</td>
                <td class="px-6 py-4">
                  <div class="flex flex-col gap-0.5">
                    <span v-for="item in order.items" :key="item.id" class="text-xs text-gray-500">
                      {{ item.quantity }}x {{ item.product?.name || 'Producto eliminado' }}
                    </span>
                  </div>
                </td>
                <td class="px-6 py-4 font-bold text-sm">${{ order.total }}</td>
                <td class="px-6 py-4 text-xs text-gray-500">{{ order.shipping_tracking || '-' }}</td>
                <td class="px-6 py-4">
                  <span class="text-xs font-bold px-2 py-1 rounded-full" :class="statusClass(order.status)">{{ statusLabel(order.status) }}</span>
                </td>
              </tr>
            </tbody>
          </table>
          <div v-if="orders.length === 0" class="text-center py-12 text-gray-400">
            <p class="text-4xl mb-3">🛒</p>
            <p class="font-semibold">No hay pedidos</p>
          </div>
        </div>
      </section>
    </main>

    <!-- MODAL ARTESANO -->
    <div v-if="showArtisanModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4 z-[100]">
      <div class="bg-white rounded-3xl p-8 max-w-lg w-full shadow-2xl max-h-[90vh] overflow-y-auto">
        <h2 class="text-xl font-black mb-6">{{ editingArtisan ? 'Editar Artesano' : 'Nuevo Artesano' }}</h2>
        <form @submit.prevent="saveArtisan" class="space-y-4">
          <div>
            <label class="text-xs font-bold text-gray-500 uppercase mb-1 block">Nombre completo</label>
            <input v-model="artisanForm.name" placeholder="Nombre del artesano" class="input-field" required />
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="text-xs font-bold text-gray-500 uppercase mb-1 block">Email</label>
              <input v-model="artisanForm.email" type="email" placeholder="email@ejemplo.com" class="input-field" required />
            </div>
            <div>
              <label class="text-xs font-bold text-gray-500 uppercase mb-1 block">Telefono</label>
              <input v-model="artisanForm.phone" placeholder="Telefono" class="input-field" />
            </div>
          </div>
          <div>
            <label class="text-xs font-bold text-gray-500 uppercase mb-1 block">Especialidad (texto libre)</label>
            <input v-model="artisanForm.specialty" placeholder="Ej: Ceramica, Tejido, Madera..." class="input-field" required />
          </div>
          <div>
            <label class="text-xs font-bold text-gray-500 uppercase mb-1 block">Categorias / Rubros</label>
            <div class="input-field flex flex-wrap gap-2 min-h-[48px] cursor-pointer" @click="showCategoryDropdown = !showCategoryDropdown">
              <span v-if="artisanForm.category_ids.length === 0" class="text-gray-400 text-sm">Seleccionar categorias...</span>
              <span
                v-for="catId in artisanForm.category_ids"
                :key="catId"
                class="inline-flex items-center gap-1 text-xs font-bold px-2 py-1 rounded-full bg-artisan-brown/10 text-artisan-brown"
              >
                {{ categories.find(c => c.id === catId)?.name }}
                <button type="button" @click.stop="removeCategoryFromArtisan(catId)" class="hover:text-red-500 ml-0.5">&times;</button>
              </span>
            </div>
            <div v-if="showCategoryDropdown" class="mt-1 bg-white border border-gray-200 rounded-xl shadow-lg max-h-40 overflow-y-auto z-10 relative">
              <label
                v-for="cat in categories"
                :key="cat.id"
                class="flex items-center gap-2 px-3 py-2 hover:bg-artisan-bg cursor-pointer text-sm"
              >
                <input
                  type="checkbox"
                  :value="cat.id"
                  v-model="artisanForm.category_ids"
                  class="accent-artisan-brown"
                />
                {{ cat.name }}
              </label>
              <div v-if="categories.length === 0" class="px-3 py-2 text-sm text-gray-400">No hay categorias creadas</div>
            </div>
          </div>
          <div>
            <label class="text-xs font-bold text-gray-500 uppercase mb-1 block">Ubicacion</label>
            <input v-model="artisanForm.location" placeholder="Ciudad, Provincia" class="input-field" />
          </div>
          <div>
            <label class="text-xs font-bold text-gray-500 uppercase mb-1 block">Biografia</label>
            <textarea v-model="artisanForm.bio" placeholder="Breve descripcion del artesano y su trabajo..." class="input-field h-24" rows="3"></textarea>
          </div>
          <div>
            <label class="text-xs font-bold text-gray-500 uppercase mb-1 block">Foto de perfil</label>
            <input type="file" accept="image/*" @change="onArtisanPhoto" class="input-field text-sm" />
            <div v-if="artisanPhotoPreview" class="mt-2">
              <img :src="artisanPhotoPreview" class="w-24 h-24 object-cover rounded-xl" />
            </div>
          </div>
          <div v-if="editingArtisan" class="flex items-center gap-3 pt-2">
            <label class="relative inline-flex items-center cursor-pointer">
              <input type="checkbox" v-model="artisanForm.is_active" class="sr-only peer" />
              <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-500"></div>
            </label>
            <span class="text-sm font-semibold text-gray-600">{{ artisanForm.is_active ? 'Activo' : 'Inactivo' }}</span>
          </div>
          <div v-if="artisanError" class="text-red-500 text-sm font-semibold bg-red-50 p-3 rounded-xl">{{ artisanError }}</div>
          <div class="flex gap-3 pt-4">
            <button type="button" @click="showArtisanModal = false" class="flex-1 py-3 border border-gray-200 rounded-full font-bold text-gray-600 hover:bg-gray-50 transition-colors">Cancelar</button>
            <button type="submit" :disabled="savingArtisan" class="flex-1 btn-primary py-3 rounded-full">
              {{ savingArtisan ? 'Guardando...' : 'Guardar' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- MODAL PRODUCTO -->
    <div v-if="showProductModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4 z-[100]">
      <div class="bg-white rounded-3xl p-8 max-w-lg w-full shadow-2xl max-h-[90vh] overflow-y-auto">
        <h2 class="text-xl font-black mb-6">{{ editingProduct ? 'Editar Producto' : 'Nuevo Producto' }}</h2>
        <form @submit.prevent="saveProduct" class="space-y-4">
          <div>
            <label class="text-xs font-bold text-gray-500 uppercase mb-1 block">Nombre</label>
            <input v-model="productForm.name" placeholder="Nombre del producto" class="input-field" required />
          </div>
          <div>
            <label class="text-xs font-bold text-gray-500 uppercase mb-1 block">Descripcion</label>
            <textarea v-model="productForm.description" placeholder="Descripcion detallada del producto..." class="input-field h-24" required></textarea>
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="text-xs font-bold text-gray-500 uppercase mb-1 block">Precio</label>
              <input v-model.number="productForm.price" type="number" step="0.01" placeholder="0.00" class="input-field" required />
            </div>
            <div>
              <label class="text-xs font-bold text-gray-500 uppercase mb-1 block">Stock</label>
              <input v-model.number="productForm.stock" type="number" placeholder="0" class="input-field" required />
            </div>
          </div>
          <div>
            <label class="text-xs font-bold text-gray-500 uppercase mb-1 block">Categoria</label>
            <select v-model="productForm.category_id" class="input-field" required>
              <option disabled value="">Seleccionar categoria</option>
              <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
            </select>
          </div>
          <div>
            <label class="text-xs font-bold text-gray-500 uppercase mb-1 block">Artesano</label>
            <select v-model="productForm.artisan_id" class="input-field" required>
              <option disabled value="">Seleccionar artesano</option>
              <option v-for="art in artisans" :key="art.id" :value="art.id">{{ art.user?.name }} - {{ art.specialty }}</option>
            </select>
          </div>
          <div>
            <label class="text-xs font-bold text-gray-500 uppercase mb-1 block">Imagenes</label>
            <input type="file" accept="image/*" multiple @change="onProductImages" class="input-field text-sm" />
          </div>
          <div v-if="productError" class="text-red-500 text-sm font-semibold bg-red-50 p-3 rounded-xl">{{ productError }}</div>
          <div class="flex gap-3 pt-4">
            <button type="button" @click="showProductModal = false" class="flex-1 py-3 border border-gray-200 rounded-full font-bold text-gray-600 hover:bg-gray-50 transition-colors">Cancelar</button>
            <button type="submit" :disabled="savingProduct" class="flex-1 btn-primary py-3 rounded-full">
              {{ savingProduct ? 'Guardando...' : 'Guardar' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue'
import { useAuthStore } from '../../stores/auth'
import api, { storageUrl } from '../../utils/api'

const auth = useAuthStore()

const activeSection = ref('resumen')
const artisans = ref([])
const allProducts = ref([])
const categories = ref([])
const clients = ref([])
const orders = ref([])

// Artisan modal
const showArtisanModal = ref(false)
const editingArtisan = ref(null)
const savingArtisan = ref(false)
const artisanError = ref('')
const artisanPhotoFile = ref(null)
const artisanPhotoPreview = ref(null)
const showCategoryDropdown = ref(false)
const artisanForm = reactive({
  name: '', email: '', phone: '', specialty: '', bio: '', location: '', is_active: true, category_ids: []
})

// Product modal
const showProductModal = ref(false)
const editingProduct = ref(null)
const savingProduct = ref(false)
const productError = ref('')
const productImageFiles = ref(null)
const productForm = reactive({
  name: '', description: '', price: null, stock: null, category_id: '', artisan_id: ''
})

const menuItems = computed(() => [
  { key: 'resumen', label: 'Resumen', icon: '📊' },
  { key: 'artesanos', label: 'Artesanos', icon: '🧑‍🎨', badge: artisans.value.length || null },
  { key: 'productos', label: 'Productos', icon: '📦', badge: allProducts.value.length || null },
  { key: 'clientes', label: 'Clientes', icon: '👥', badge: clients.value.length || null },
  { key: 'pedidos', label: 'Pedidos', icon: '🛒', badge: orders.value.length || null },
])

const lowStockProducts = computed(() => allProducts.value.filter(p => p.stock <= 5 && p.stock > 0))

// Fetch data
const fetchAll = async () => {
  try {
    const [resArt, resProd, resCat, resClients, resOrders] = await Promise.all([
      api.get('/admin/artisans'),
      api.get('/admin/products'),
      api.get('/categories'),
      api.get('/admin/clients'),
      api.get('/orders'),
    ])
    artisans.value = resArt.data
    allProducts.value = resProd.data
    categories.value = resCat.data
    clients.value = resClients.data
    orders.value = resOrders.data
  } catch (error) {
    console.error('Error loading dashboard data', error)
  }
}

// Artisan CRUD
const openArtisanModal = (art = null) => {
  editingArtisan.value = art
  artisanError.value = ''
  artisanPhotoFile.value = null
  showCategoryDropdown.value = false
  artisanPhotoPreview.value = art?.photo ? storageUrl(art.photo) : null
  if (art) {
    artisanForm.name = art.user?.name || ''
    artisanForm.email = art.user?.email || ''
    artisanForm.phone = art.user?.phone || ''
    artisanForm.specialty = art.specialty || ''
    artisanForm.bio = art.bio || ''
    artisanForm.location = art.location || ''
    artisanForm.is_active = art.is_active
    artisanForm.category_ids = art.categories?.map(c => c.id) || []
  } else {
    Object.assign(artisanForm, { name: '', email: '', phone: '', specialty: '', bio: '', location: '', is_active: true, category_ids: [] })
  }
  showArtisanModal.value = true
}

const removeCategoryFromArtisan = (catId) => {
  artisanForm.category_ids = artisanForm.category_ids.filter(id => id !== catId)
}

const onArtisanPhoto = (e) => {
  const file = e.target.files[0]
  if (file) {
    artisanPhotoFile.value = file
    artisanPhotoPreview.value = URL.createObjectURL(file)
  }
}

const saveArtisan = async () => {
  savingArtisan.value = true
  artisanError.value = ''
  try {
    const formData = new FormData()
    formData.append('name', artisanForm.name)
    formData.append('email', artisanForm.email)
    if (artisanForm.phone) formData.append('phone', artisanForm.phone)
    formData.append('specialty', artisanForm.specialty)
    if (artisanForm.bio) formData.append('bio', artisanForm.bio)
    if (artisanForm.location) formData.append('location', artisanForm.location)
    if (artisanPhotoFile.value) formData.append('photo', artisanPhotoFile.value)
    formData.append('category_ids', JSON.stringify(artisanForm.category_ids))

    if (editingArtisan.value) {
      formData.append('is_active', artisanForm.is_active ? '1' : '0')
      formData.append('_method', 'PUT')
      await api.post(`/artisans/${editingArtisan.value.id}`, formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      })
    } else {
      await api.post('/artisans', formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      })
    }
    showArtisanModal.value = false
    await fetchAll()
  } catch (error) {
    artisanError.value = error.response?.data?.message || 'Error al guardar artesano'
  } finally {
    savingArtisan.value = false
  }
}

const deleteArtisan = async (id) => {
  if (!confirm('¿Seguro que deseas eliminar este artesano? Se eliminaran tambien sus productos.')) return
  try {
    await api.delete(`/artisans/${id}`)
    await fetchAll()
  } catch (error) {
    alert('Error al eliminar artesano')
  }
}

// Product CRUD
const openProductModal = (prod = null) => {
  editingProduct.value = prod
  productError.value = ''
  productImageFiles.value = null
  if (prod) {
    productForm.name = prod.name
    productForm.description = prod.description || ''
    productForm.price = prod.price
    productForm.stock = prod.stock
    productForm.category_id = prod.category_id
    productForm.artisan_id = prod.artisan_id
  } else {
    Object.assign(productForm, { name: '', description: '', price: null, stock: null, category_id: '', artisan_id: '' })
  }
  showProductModal.value = true
}

const onProductImages = (e) => {
  productImageFiles.value = e.target.files
}

const saveProduct = async () => {
  savingProduct.value = true
  productError.value = ''
  try {
    const formData = new FormData()
    formData.append('name', productForm.name)
    formData.append('description', productForm.description)
    formData.append('price', productForm.price)
    formData.append('stock', productForm.stock)
    formData.append('category_id', productForm.category_id)
    formData.append('artisan_id', productForm.artisan_id)

    if (productImageFiles.value) {
      for (const file of productImageFiles.value) {
        formData.append('image_files[]', file)
      }
    }

    if (editingProduct.value) {
      formData.append('_method', 'PUT')
      await api.post(`/products/${editingProduct.value.id}`, formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      })
    } else {
      await api.post('/products', formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      })
    }
    showProductModal.value = false
    await fetchAll()
  } catch (error) {
    productError.value = error.response?.data?.message || 'Error al guardar producto'
  } finally {
    savingProduct.value = false
  }
}

const toggleProductActive = async (prod) => {
  try {
    await api.patch(`/products/${prod.id}/toggle-active`)
    prod.is_active = !prod.is_active
  } catch (error) {
    alert('Error al cambiar estado')
  }
}

const deleteProduct = async (id) => {
  if (!confirm('¿Seguro que deseas eliminar este producto?')) return
  try {
    await api.delete(`/products/${id}`)
    await fetchAll()
  } catch (error) {
    alert('Error al eliminar producto')
  }
}

// Helpers
const formatDate = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('es-AR', { day: '2-digit', month: '2-digit', year: 'numeric' })
}

const statusLabels = { pending: 'Pendiente', paid: 'Pagado', shipped: 'Enviado', delivered: 'Entregado', cancelled: 'Cancelado' }
const statusLabel = (s) => statusLabels[s] || s

const statusClass = (s) => {
  const map = {
    pending: 'bg-yellow-100 text-yellow-700',
    paid: 'bg-blue-100 text-blue-700',
    shipped: 'bg-purple-100 text-purple-700',
    delivered: 'bg-green-100 text-green-700',
    cancelled: 'bg-red-100 text-red-700',
  }
  return map[s] || 'bg-gray-100 text-gray-700'
}

onMounted(fetchAll)
</script>
