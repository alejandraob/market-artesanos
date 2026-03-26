<template>
  <div class="max-w-3xl mx-auto px-4 py-12">
    <h1 class="text-3xl font-black text-artisan-dark mb-2">Preguntas Frecuentes</h1>
    <p class="text-gray-500 mb-10">Respuestas a las dudas mas comunes sobre nuestros productos y el proceso de compra.</p>

    <div class="space-y-3">
      <div v-for="(item, idx) in faqs" :key="idx" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <button
          @click="toggle(idx)"
          class="w-full flex items-center justify-between px-6 py-5 text-left hover:bg-gray-50/50 transition-colors"
        >
          <span class="font-bold text-sm text-artisan-dark pr-4">{{ item.question }}</span>
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400 shrink-0 transition-transform duration-300" :class="open === idx ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
          </svg>
        </button>
        <div v-if="open === idx" class="px-6 pb-5 text-sm text-gray-600 leading-relaxed border-t border-gray-50 pt-4">
          <p v-html="sanitize(item.answer)"></p>
        </div>
      </div>
    </div>

    <div class="mt-12 bg-artisan-accent/10 border border-artisan-accent/20 rounded-2xl p-8 text-center">
      <h2 class="font-bold text-lg text-artisan-dark mb-2">¿No encontraste lo que buscabas?</h2>
      <p class="text-gray-500 text-sm mb-4">Escribinos y te responderemos a la brevedad.</p>
      <router-link to="/contacto" class="btn-primary inline-block px-8 py-3 text-sm">Ir a Contacto</router-link>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const open = ref(null)

const toggle = (idx) => {
  open.value = open.value === idx ? null : idx
}

const sanitize = (html) => {
  const div = document.createElement('div')
  div.innerHTML = html
  div.querySelectorAll('*').forEach(el => {
    const allowed = ['STRONG', 'B', 'I', 'EM', 'A', 'BR', 'P']
    if (!allowed.includes(el.tagName)) {
      el.replaceWith(...el.childNodes)
      return
    }
    // Solo permitir href en <a> y quitar el resto de atributos
    const attrs = [...el.attributes]
    attrs.forEach(attr => {
      if (el.tagName === 'A' && (attr.name === 'href' || attr.name === 'class')) return
      el.removeAttribute(attr.name)
    })
  })
  return div.innerHTML
}

const faqs = [
  {
    question: '¿Los productos son hechos a mano?',
    answer: 'Si, todos los productos son <strong>artesanales y elaborados a mano</strong> por los artesanos de la Asociacion de Artesanos de Catriel. Cada pieza es unica y puede presentar pequenas variaciones propias del trabajo manual.'
  },
  {
    question: '¿Los productos tienen stock o se fabrican a pedido?',
    answer: 'Los productos se <strong>fabrican bajo demanda</strong> una vez confirmada la compra. No manejamos stock. Las imagenes publicadas son muestras ilustrativas del trabajo del artesano.'
  },
  {
    question: '¿Cuanto tiempo tarda en llegar mi pedido?',
    answer: 'El tiempo estimado de elaboracion y envio es de <strong>15 a 20 dias habiles</strong> para pedidos de 1 a 5 unidades. Para pedidos de mas de 5 unidades, el plazo puede ser mayor y se coordina directamente con el artesano.'
  },
  {
    question: '¿Puedo personalizar un producto?',
    answer: 'Si. Una vez confirmada la compra, recibiras los <strong>datos de contacto del artesano</strong> para coordinar los detalles: color, material, medidas, inscripciones y cualquier personalizacion que necesites.'
  },
  {
    question: '¿Como me comunico con el artesano?',
    answer: 'Al confirmar tu compra, el sistema te mostrara el <strong>nombre, telefono, email y ubicacion del artesano</strong>. Tambien recibiras esta informacion por email. Podes contactarlo por WhatsApp, email o telefono.'
  },
  {
    question: '¿Hacen envios a todo el pais?',
    answer: 'Si, realizamos envios a todo el pais a traves de <strong>Correo Argentino</strong>. El costo de envio se calcula al momento de la compra segun tu codigo postal.'
  },
  {
    question: '¿Puedo cancelar mi pedido?',
    answer: 'Podes solicitar la cancelacion <strong>antes de que el artesano haya comenzado la elaboracion</strong>. Una vez iniciada la fabricacion, no se aceptan cancelaciones ya que los productos se hacen de forma personalizada.'
  },
  {
    question: '¿Que pasa si el producto llega con un defecto?',
    answer: 'Si el producto presenta un defecto de fabricacion o no coincide con lo coordinado, podes solicitar reparacion, reemplazo o devolucion del importe dentro de los <strong>10 dias habiles</strong> posteriores a la recepcion. Consulta nuestra <a href="/politica-de-devoluciones" class="text-artisan-accent font-bold hover:underline">Politica de Devoluciones</a>.'
  },
  {
    question: '¿Las imagenes son exactas al producto que voy a recibir?',
    answer: 'Las imagenes son <strong>muestras ilustrativas</strong> del trabajo del artesano. El producto final se elabora segun las especificaciones que coordines directamente con el artesano, por lo que puede variar respecto a la foto.'
  },
  {
    question: '¿Necesito crear una cuenta para comprar?',
    answer: 'Si, necesitas <strong>registrarte y verificar tu email</strong> para poder realizar una compra. El registro es gratuito y te permite hacer seguimiento de tus pedidos.'
  },
  {
    question: '¿Que medios de pago aceptan?',
    answer: 'Actualmente estamos integrando la pasarela de pago <strong>PayWay</strong>. Proximamente podras pagar con tarjeta de credito, debito y otros medios.'
  },
]
</script>
