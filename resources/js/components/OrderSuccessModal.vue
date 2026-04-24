<template>
  <Teleport to="body">
    <Transition name="order-modal">
      <div v-if="visible" class="fixed inset-0 z-50 flex items-center justify-center px-4">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" />
        <div class="relative bg-white rounded-2xl shadow-2xl p-8 max-w-md w-full text-center">

          <!-- Animated check icon -->
          <div class="flex items-center justify-center mx-auto mb-5 w-20 h-20">
            <svg viewBox="0 0 80 80" class="w-20 h-20" fill="none">
              <circle
                cx="40" cy="40" r="36"
                stroke="#22c55e" stroke-width="4"
                fill="#f0fdf4"
                class="check-circle"
              />
              <path
                d="M24 40 l12 12 l20 -22"
                stroke="#16a34a" stroke-width="4.5"
                stroke-linecap="round" stroke-linejoin="round"
                class="check-mark"
              />
            </svg>
          </div>

          <h2 class="text-2xl font-bold text-gray-900 mb-1">{{ title }}</h2>
          <p class="text-gray-500 text-sm mb-1">{{ subtitle }}</p>
          <p class="font-mono text-sm text-green-600 font-semibold mb-5">{{ order.order_number }}</p>

          <!-- Items summary -->
          <div class="bg-gray-50 rounded-xl divide-y divide-gray-100 text-left mb-4 max-h-40 overflow-y-auto">
            <div v-for="item in order.items" :key="item.id" class="flex justify-between items-center px-4 py-2.5 text-sm">
              <span class="text-gray-700 truncate mr-3">{{ item.product_name }} <span class="text-gray-400">× {{ item.quantity }}</span></span>
              <span class="font-medium text-gray-900 whitespace-nowrap">{{ formatPrice(item.unit_price * item.quantity) }}</span>
            </div>
          </div>

          <!-- Total -->
          <div class="flex justify-between items-center px-4 py-3 bg-green-50 rounded-xl border border-green-100 mb-6 text-sm">
            <span class="font-semibold text-gray-900">Total</span>
            <span class="font-bold text-green-700 text-base">{{ formatPrice(order.total) }}</span>
          </div>

          <!-- CTAs -->
          <div class="flex flex-col sm:flex-row gap-3">
            <Link
              v-if="!isGuest"
              :href="route('account.orders')"
              class="flex-1 py-2.5 px-4 rounded-xl text-sm font-semibold bg-green-600 text-white hover:bg-green-700 transition-colors text-center"
            >
              {{ viewOrdersLabel }}
            </Link>
            <Link
              v-else
              :href="route('register')"
              class="flex-1 py-2.5 px-4 rounded-xl text-sm font-semibold bg-green-600 text-white hover:bg-green-700 transition-colors text-center"
            >
              Create an Account
            </Link>
            <button
              @click="visible = false"
              class="flex-1 py-2.5 px-4 rounded-xl text-sm font-semibold bg-gray-100 text-gray-700 hover:bg-gray-200 transition-colors cursor-pointer"
            >
              {{ shopLabel }}
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'

const props = defineProps({
  order: { type: Object, required: true },
  isGuest: { type: Boolean, default: false },
  title: { type: String, default: 'Order Confirmed!' },
  subtitle: { type: String, default: 'Thank you for your purchase.' },
  viewOrdersLabel: { type: String, default: 'View My Orders' },
  shopLabel: { type: String, default: 'Continue Shopping' },
})

const visible = ref(false)
const page = usePage()
const route = window.route

onMounted(() => {
  setTimeout(() => { visible.value = true }, 150)
})

function formatPrice(price) {
  return new Intl.NumberFormat('el-GR', {
    style: 'currency',
    currency: page.props.currency ?? 'EUR',
  }).format(price)
}
</script>

<style scoped>
.order-modal-enter-active {
  transition: opacity 0.25s ease, transform 0.25s ease;
}
.order-modal-leave-active {
  transition: opacity 0.2s ease, transform 0.2s ease;
}
.order-modal-enter-from,
.order-modal-leave-to {
  opacity: 0;
  transform: scale(0.95);
}

.check-circle {
  transform-origin: center;
  animation: circle-pop 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) 0.25s both;
}
.check-mark {
  stroke-dasharray: 50;
  stroke-dashoffset: 50;
  animation: draw-check 0.35s ease-out 0.55s forwards;
}

@keyframes circle-pop {
  from { transform: scale(0); opacity: 0; }
  to   { transform: scale(1); opacity: 1; }
}
@keyframes draw-check {
  to { stroke-dashoffset: 0; }
}
</style>
