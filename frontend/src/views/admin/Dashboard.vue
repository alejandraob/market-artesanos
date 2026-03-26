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
            <h3 class="font-bold text-gray-700 mb-4">Productos Mas Vendidos</h3>
            <div v-if="topProducts.length === 0" class="text-gray-400 text-sm py-4">Sin ventas aun</div>
            <div v-else class="space-y-3">
              <div v-for="(prod, idx) in topProducts" :key="prod.name" class="flex items-center justify-between py-2 border-b border-gray-50 last:border-0">
                <div class="flex items-center gap-3">
                  <span class="text-xs font-black text-artisan-accent w-5">{{ idx + 1 }}</span>
                  <span class="font-semibold text-sm">{{ prod.name }}</span>
                </div>
                <span class="text-xs font-bold px-2 py-1 rounded-full bg-artisan-accent/10 text-artisan-accent">{{ prod.total }} vendidos</span>
              </div>
            </div>
          </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
          <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h3 class="font-bold text-gray-700 mb-4">Provincias con Mas Pedidos</h3>
            <div v-if="topProvinces.length === 0" class="text-gray-400 text-sm py-4">Sin pedidos con direccion aun</div>
            <div v-else class="space-y-3">
              <div v-for="(prov, idx) in topProvinces" :key="prov.province" class="flex items-center justify-between py-2 border-b border-gray-50 last:border-0">
                <div class="flex items-center gap-3">
                  <span class="text-xs font-black text-artisan-brown w-5">{{ idx + 1 }}</span>
                  <span class="font-semibold text-sm">{{ prov.province }}</span>
                </div>
                <span class="text-xs font-bold px-2 py-1 rounded-full bg-artisan-brown/10 text-artisan-brown">{{ prov.count }} {{ prov.count === 1 ? 'pedido' : 'pedidos' }}</span>
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

        <!-- Filtro por estado -->
        <div class="flex gap-2 mb-4 flex-wrap">
          <button
            v-for="st in ['todos', 'pending', 'paid', 'shipped', 'delivered', 'cancelled']"
            :key="st"
            @click="orderFilter = st"
            class="text-xs font-bold px-3 py-1.5 rounded-full transition-colors"
            :class="orderFilter === st ? 'bg-artisan-brown text-white' : 'bg-white text-gray-600 border border-gray-200 hover:border-gray-300'"
          >{{ st === 'todos' ? 'Todos' : statusLabel(st) }}</button>
        </div>

        <div class="space-y-4">
          <div v-for="order in filteredOrders" :key="order.id" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <!-- Header del pedido -->
            <div class="flex items-center justify-between px-6 py-4 cursor-pointer hover:bg-gray-50/50 transition-colors" @click="toggleOrderDetail(order.id)">
              <div class="flex items-center gap-4">
                <span class="font-black text-sm">#{{ order.id }}</span>
                <span class="text-sm text-gray-500">{{ formatDate(order.created_at) }}</span>
                <span class="text-xs font-bold px-2 py-1 rounded-full" :class="statusClass(order.status)">{{ statusLabel(order.status) }}</span>
              </div>
              <div class="flex items-center gap-4">
                <span class="font-bold text-sm">${{ order.total }}</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400 transition-transform" :class="expandedOrder === order.id ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </div>
            </div>

            <!-- Detalle expandido -->
            <div v-if="expandedOrder === order.id" class="border-t border-gray-100 px-6 py-5 space-y-5">
              <!-- Productos -->
              <div>
                <h4 class="text-xs font-bold text-gray-400 uppercase mb-2">Productos</h4>
                <div class="space-y-2">
                  <div v-for="item in order.items" :key="item.id" class="flex items-center gap-3 text-sm">
                    <img :src="item.product?.images?.length ? storageUrl(item.product.images[0]) : 'https://placehold.co/40x40'" class="w-10 h-10 rounded-lg object-cover" />
                    <span class="flex-1">{{ item.product?.name || 'Producto eliminado' }}</span>
                    <span class="text-gray-500">x{{ item.quantity }}</span>
                    <span class="font-bold">${{ item.unit_price }}</span>
                  </div>
                </div>
              </div>

              <!-- Datos de envio -->
              <div v-if="order.shipping_name" class="grid grid-cols-2 gap-4">
                <div>
                  <h4 class="text-xs font-bold text-gray-400 uppercase mb-2">Direccion de envio</h4>
                  <p class="text-sm text-gray-700">{{ order.shipping_name }}</p>
                  <p class="text-sm text-gray-700">{{ order.shipping_address }}</p>
                  <p class="text-sm text-gray-700">{{ order.shipping_city }}, {{ order.shipping_province }}</p>
                  <p class="text-sm text-gray-700">CP {{ order.shipping_postal_code }}</p>
                  <p class="text-sm text-gray-700">Tel: {{ order.shipping_phone }}</p>
                </div>
                <div>
                  <h4 class="text-xs font-bold text-gray-400 uppercase mb-2">Envio</h4>
                  <p class="text-sm text-gray-700">Costo: ${{ order.shipping_cost }}</p>
                  <p class="text-sm text-gray-700">Tracking: {{ order.shipping_tracking || 'Sin asignar' }}</p>
                </div>
              </div>

              <!-- Cambiar estado -->
              <div class="bg-gray-50 rounded-xl p-4">
                <h4 class="text-xs font-bold text-gray-400 uppercase mb-3">Gestionar pedido</h4>
                <div class="flex flex-wrap items-end gap-4">
                  <div>
                    <label class="text-xs font-bold text-gray-500 mb-1 block">Estado</label>
                    <select v-model="orderStatusEdit[order.id]" class="input-field text-sm py-2 w-48">
                      <option value="pending">Pendiente</option>
                      <option value="paid">Pagado</option>
                      <option value="shipped">Enviado</option>
                      <option value="delivered">Entregado</option>
                      <option value="cancelled">Cancelado</option>
                    </select>
                  </div>
                  <div>
                    <label class="text-xs font-bold text-gray-500 mb-1 block">N. de seguimiento</label>
                    <input v-model="orderTrackingEdit[order.id]" class="input-field text-sm py-2 w-56" placeholder="Ej: MARKET-123" />
                  </div>
                  <button
                    @click="updateOrderStatus(order)"
                    :disabled="savingOrder === order.id"
                    class="bg-artisan-brown hover:bg-[#5b3a27] text-white font-bold text-sm px-5 py-2.5 rounded-xl transition-colors disabled:opacity-50"
                  >
                    {{ savingOrder === order.id ? 'Guardando...' : 'Actualizar' }}
                  </button>
                </div>
                <p v-if="orderUpdateMsg === order.id" class="text-green-600 text-xs font-bold mt-2">Estado actualizado correctamente</p>
              </div>
            </div>
          </div>

          <div v-if="filteredOrders.length === 0" class="bg-white rounded-2xl shadow-sm border border-gray-100 text-center py-12 text-gray-400">
            <p class="text-4xl mb-3">🛒</p>
            <p class="font-semibold">No hay pedidos</p>
          </div>
        </div>
      </section>

      <!-- CATEGORIAS -->
      <section v-if="activeSection === 'categorias'">
        <div class="flex justify-between items-center mb-6">
          <h1 class="text-2xl font-black text-artisan-dark">Categorias</h1>
          <button @click="openCategoryModal()" class="bg-artisan-brown hover:bg-[#5b3a27] text-white font-bold text-sm px-5 py-2.5 rounded-xl transition-colors">+ Nueva Categoria</button>
        </div>

        <div class="space-y-3">
          <div v-for="cat in categories" :key="cat.id" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center justify-between">
              <div>
                <span class="font-bold text-artisan-dark">{{ cat.name }}</span>
                <span v-if="cat.description" class="text-gray-400 text-sm ml-2">- {{ cat.description }}</span>
              </div>
              <div class="flex gap-2">
                <button @click="openCategoryModal(cat)" class="text-xs font-bold px-3 py-1.5 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors">Editar</button>
                <button @click="deleteCategory(cat.id)" class="text-xs font-bold px-3 py-1.5 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition-colors">Eliminar</button>
              </div>
            </div>
            <!-- Subcategorias -->
            <div v-if="cat.subcategories?.length" class="mt-3 pl-6 space-y-2">
              <div v-for="sub in cat.subcategories" :key="sub.id" class="flex items-center justify-between bg-gray-50 rounded-xl px-4 py-2.5">
                <div>
                  <span class="font-semibold text-sm text-gray-700">{{ sub.name }}</span>
                  <span v-if="sub.description" class="text-gray-400 text-xs ml-2">- {{ sub.description }}</span>
                </div>
                <div class="flex gap-2">
                  <button @click="openCategoryModal(sub, cat.id)" class="text-xs font-bold px-2 py-1 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors">Editar</button>
                  <button @click="deleteCategory(sub.id)" class="text-xs font-bold px-2 py-1 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition-colors">Eliminar</button>
                </div>
              </div>
            </div>
            <button @click="openCategoryModal(null, cat.id)" class="mt-2 ml-6 text-xs text-artisan-accent font-bold hover:underline">+ Agregar subcategoria</button>
          </div>
        </div>

        <div v-if="categories.length === 0" class="bg-white rounded-2xl shadow-sm border border-gray-100 text-center py-12 text-gray-400">
          <p class="font-semibold">No hay categorias</p>
        </div>
      </section>
    </main>

    <!-- MODAL CATEGORIA -->
    <div v-if="showCategoryModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4 z-[100]">
      <div class="bg-white rounded-3xl p-8 max-w-md w-full shadow-2xl">
        <h2 class="text-xl font-black mb-6">{{ editingCategory ? 'Editar Categoria' : 'Nueva Categoria' }}</h2>
        <form @submit.prevent="saveCategory" class="space-y-4">
          <div>
            <label class="text-xs font-bold text-gray-500 uppercase mb-1 block">Nombre</label>
            <input v-model="categoryForm.name" class="input-field" required />
          </div>
          <div>
            <label class="text-xs font-bold text-gray-500 uppercase mb-1 block">Descripcion (opcional)</label>
            <input v-model="categoryForm.description" class="input-field" />
          </div>
          <div>
            <label class="text-xs font-bold text-gray-500 uppercase mb-1 block">Categoria padre</label>
            <select v-model="categoryForm.parent_id" class="input-field">
              <option :value="null">Ninguna (categoria principal)</option>
              <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
            </select>
          </div>
          <div v-if="categoryError" class="text-red-500 text-sm font-semibold bg-red-50 p-3 rounded-xl">{{ categoryError }}</div>
          <div class="flex gap-3 pt-2">
            <button type="submit" :disabled="savingCategory" class="btn-primary flex-1 py-3">{{ savingCategory ? 'Guardando...' : 'Guardar' }}</button>
            <button type="button" @click="showCategoryModal = false" class="flex-1 py-3 border-2 border-gray-200 rounded-full font-bold text-gray-500 hover:bg-gray-50 transition-colors">Cancelar</button>
          </div>
        </form>
      </div>
    </div>

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
import { useToastStore } from '../../stores/toast'

const toastStore = useToastStore()

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

// Order management
const expandedOrder = ref(null)
const orderFilter = ref('todos')
const orderStatusEdit = reactive({})
const orderTrackingEdit = reactive({})
const savingOrder = ref(null)
const orderUpdateMsg = ref(null)

const filteredOrders = computed(() => {
  if (orderFilter.value === 'todos') return orders.value
  return orders.value.filter(o => o.status === orderFilter.value)
})

const toggleOrderDetail = (id) => {
  if (expandedOrder.value === id) {
    expandedOrder.value = null
    return
  }
  expandedOrder.value = id
  const order = orders.value.find(o => o.id === id)
  if (order) {
    orderStatusEdit[id] = order.status
    orderTrackingEdit[id] = order.shipping_tracking || ''
  }
}

const updateOrderStatus = async (order) => {
  savingOrder.value = order.id
  orderUpdateMsg.value = null
  try {
    const res = await api.patch(`/orders/${order.id}/status`, {
      status: orderStatusEdit[order.id],
      shipping_tracking: orderTrackingEdit[order.id] || null,
    })
    order.status = res.data.status
    order.shipping_tracking = res.data.shipping_tracking
    orderUpdateMsg.value = order.id
    setTimeout(() => { if (orderUpdateMsg.value === order.id) orderUpdateMsg.value = null }, 3000)
  } catch (error) {
    toastStore.error('Error al actualizar el pedido')
  } finally {
    savingOrder.value = null
  }
}

// Category management
const showCategoryModal = ref(false)
const editingCategory = ref(null)
const savingCategory = ref(false)
const categoryError = ref('')
const categoryForm = reactive({ name: '', description: '', parent_id: null })

const openCategoryModal = (cat = null, parentId = null) => {
  editingCategory.value = cat
  categoryError.value = ''
  if (cat) {
    categoryForm.name = cat.name || ''
    categoryForm.description = cat.description || ''
    categoryForm.parent_id = parentId || cat.parent_id || null
  } else {
    Object.assign(categoryForm, { name: '', description: '', parent_id: parentId })
  }
  showCategoryModal.value = true
}

const saveCategory = async () => {
  savingCategory.value = true
  categoryError.value = ''
  try {
    if (editingCategory.value) {
      await api.put(`/categories/${editingCategory.value.id}`, categoryForm)
    } else {
      await api.post('/categories', categoryForm)
    }
    showCategoryModal.value = false
    await fetchAll()
  } catch (error) {
    categoryError.value = error.response?.data?.message || 'Error al guardar categoria'
  } finally {
    savingCategory.value = false
  }
}

const deletingCategory = ref(false)
const deleteCategory = async (id) => {
  if (deletingCategory.value) return
  if (!confirm('¿Seguro que deseas eliminar esta categoria?')) return
  deletingCategory.value = true
  try {
    await api.delete(`/categories/${id}`)
    await fetchAll()
  } catch (error) {
    toastStore.error(error.response?.data?.message || 'Error al eliminar categoria')
  } finally {
    deletingCategory.value = false
  }
}

const menuItems = computed(() => [
  { key: 'resumen', label: 'Resumen', icon: '📊' },
  { key: 'artesanos', label: 'Artesanos', icon: '🧑‍🎨', badge: artisans.value.length || null },
  { key: 'productos', label: 'Productos', icon: '📦', badge: allProducts.value.length || null },
  { key: 'clientes', label: 'Clientes', icon: '👥', badge: clients.value.length || null },
  { key: 'pedidos', label: 'Pedidos', icon: '🛒', badge: orders.value.length || null },
  { key: 'categorias', label: 'Categorias', icon: '📂', badge: categories.value.length || null },
])

const topProducts = computed(() => {
  const productMap = {}
  for (const order of orders.value) {
    for (const item of (order.items || [])) {
      const name = item.product?.name || 'Producto eliminado'
      productMap[name] = (productMap[name] || 0) + item.quantity
    }
  }
  return Object.entries(productMap)
    .map(([name, total]) => ({ name, total }))
    .sort((a, b) => b.total - a.total)
    .slice(0, 5)
})

const topProvinces = computed(() => {
  const provMap = {}
  for (const order of orders.value) {
    const prov = order.shipping_province
    if (prov) {
      provMap[prov] = (provMap[prov] || 0) + 1
    }
  }
  return Object.entries(provMap)
    .map(([province, count]) => ({ province, count }))
    .sort((a, b) => b.count - a.count)
    .slice(0, 5)
})

// Fetch data
const fetchAll = async () => {
  try {
    const [resArt, resProd, resCat, resClients, resOrders] = await Promise.all([
      api.get('/admin/artisans'),
      api.get('/admin/products'),
      api.get('/categories'),
      api.get('/admin/clients'),
      api.get('/orders?scope=all'),
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

const deletingItem = ref(false)
const deleteArtisan = async (id) => {
  if (deletingItem.value) return
  if (!confirm('¿Seguro que deseas eliminar este artesano? Se eliminaran tambien sus productos.')) return
  deletingItem.value = true
  try {
    await api.delete(`/artisans/${id}`)
    await fetchAll()
  } catch (error) {
    toastStore.error('Error al eliminar artesano')
  } finally {
    deletingItem.value = false
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
    toastStore.error('Error al cambiar estado')
  }
}

const deleteProduct = async (id) => {
  if (deletingItem.value) return
  if (!confirm('¿Seguro que deseas eliminar este producto?')) return
  deletingItem.value = true
  try {
    await api.delete(`/products/${id}`)
    await fetchAll()
  } catch (error) {
    toastStore.error('Error al eliminar producto')
  } finally {
    deletingItem.value = false
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
