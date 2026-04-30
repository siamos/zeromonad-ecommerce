<template>
  <Layout>
    <Head :title="`Order ${order.order_number}`" />
    <div class="max-w-3xl mx-auto px-4 py-10">

      <div class="flex items-center gap-4 mb-8">
        <Link :href="route('account.orders')"
          class="text-sm text-indigo-600 hover:text-indigo-800 flex items-center gap-1">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
          </svg>
          Back to Orders
        </Link>
      </div>

      <div class="flex flex-wrap items-center justify-between gap-4 mb-8">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">{{ order.order_number }}</h1>
          <p class="text-sm text-gray-500 mt-1">Placed on {{ formatDate(order.created_at) }}</p>
        </div>
        <div class="flex items-center gap-3 flex-wrap">
          <span class="text-sm px-3 py-1.5 rounded-full font-medium"
            :class="{
              'bg-yellow-100 text-yellow-800': order.status === 'pending',
              'bg-blue-100 text-blue-800': order.status === 'processing',
              'bg-green-100 text-green-800': order.status === 'delivered',
              'bg-red-100 text-red-800': order.status === 'cancelled',
            }">
            {{ order.status }}
          </span>
          <a :href="route('account.orders.invoice', order.id)"
            class="text-sm text-indigo-600 hover:text-indigo-800 underline">
            Download Invoice
          </a>
          <button v-if="canRequestReturn"
            @click="showReturnModal = true"
            class="text-sm px-3 py-1.5 rounded-lg border border-red-200 text-red-600 hover:bg-red-50 transition">
            Request Return
          </button>
        </div>
      </div>

      <!-- Status timeline -->
      <div v-if="!['cancelled','refunded'].includes(order.status)" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
        <h2 class="font-semibold text-gray-900 mb-5 text-sm">Order Progress</h2>
        <div class="flex items-center">
          <template v-for="(step, i) in timelineSteps" :key="step.key">
            <div class="flex flex-col items-center flex-1">
              <div :class="[
                'w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition-colors',
                stepDone(step.key) ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-400'
              ]">{{ i + 1 }}</div>
              <span class="text-xs mt-1.5 text-center leading-tight"
                :class="stepDone(step.key) ? 'text-indigo-600 font-medium' : 'text-gray-400'">
                {{ step.label }}
              </span>
            </div>
            <div v-if="i < timelineSteps.length - 1"
              :class="['flex-1 h-0.5 mb-5', stepDone(timelineSteps[i + 1].key) ? 'bg-indigo-600' : 'bg-gray-200']" />
          </template>
        </div>
      </div>
      <div v-else class="bg-red-50 border border-red-100 rounded-xl p-4 mb-6 text-sm text-red-700 font-medium">
        This order was {{ order.status }}.
      </div>

      <!-- Items -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-100 mb-6">
        <div class="p-6 border-b border-gray-100">
          <h2 class="font-semibold text-gray-900">Items</h2>
        </div>
        <div class="divide-y divide-gray-100">
          <div v-for="item in order.items" :key="item.id"
            class="px-6 py-4 flex items-center justify-between gap-4">
            <div>
              <div class="font-medium text-gray-900 text-sm">{{ item.product_name }}</div>
              <div v-if="item.product_sku" class="text-xs text-gray-400 mt-0.5">SKU: {{ item.product_sku }}</div>
            </div>
            <div class="text-right shrink-0">
              <div class="text-sm text-gray-500">{{ formatPrice(item.unit_price) }} × {{ item.quantity }}</div>
              <div class="font-semibold text-gray-900">{{ formatPrice(item.subtotal) }}</div>
            </div>
          </div>
        </div>
        <div class="px-6 py-4 border-t border-gray-100 space-y-2 text-sm">
          <div v-if="order.discount_amount > 0" class="flex justify-between text-green-600">
            <span>Discount</span>
            <span>−{{ formatPrice(order.discount_amount) }}</span>
          </div>
          <div v-if="order.shipping_amount > 0" class="flex justify-between text-gray-600">
            <span>Shipping</span>
            <span>{{ formatPrice(order.shipping_amount) }}</span>
          </div>
          <div v-if="order.tax_amount > 0" class="flex justify-between text-gray-600">
            <span>Tax</span>
            <span>{{ formatPrice(order.tax_amount) }}</span>
          </div>
          <div class="flex justify-between font-bold text-gray-900 text-base pt-2 border-t border-gray-100">
            <span>Total</span>
            <span>{{ formatPrice(order.total) }}</span>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
        <!-- Billing address -->
        <div v-if="order.billing_address" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
          <h2 class="font-semibold text-gray-900 mb-3">Billing Address</h2>
          <p class="text-sm text-gray-600">{{ order.billing_address.name }}</p>
          <p class="text-sm text-gray-600">{{ order.billing_address.line1 }}</p>
          <p class="text-sm text-gray-600">{{ order.billing_address.city }}, {{ order.billing_address.zip }}</p>
          <p class="text-sm text-gray-600">{{ order.billing_address.email }}</p>
          <p v-if="order.billing_address.phone" class="text-sm text-gray-600">{{ order.billing_address.phone }}</p>
        </div>

        <!-- Payment info -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
          <h2 class="font-semibold text-gray-900 mb-3">Payment</h2>
          <p class="text-sm text-gray-600 capitalize">{{ order.payment_method?.replace('_', ' ') }}</p>
          <span class="inline-block mt-2 text-xs px-2 py-1 rounded-full font-medium"
            :class="{
              'bg-yellow-100 text-yellow-800': order.payment_status === 'pending',
              'bg-green-100 text-green-800': order.payment_status === 'paid',
              'bg-red-100 text-red-800': order.payment_status === 'failed',
            }">
            {{ order.payment_status }}
          </span>
        </div>
      </div>
    </div>

    <!-- Return Request Modal -->
    <Teleport to="body">
      <div v-if="showReturnModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">Request a Return</h2>
          <form @submit.prevent="submitReturn">
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-1">Reason</label>
              <select v-model="returnForm.reason"
                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">Select a reason…</option>
                <option value="Damaged item">Damaged item</option>
                <option value="Wrong item received">Wrong item received</option>
                <option value="Item not as described">Item not as described</option>
                <option value="Changed my mind">Changed my mind</option>
                <option value="Other">Other</option>
              </select>
              <p v-if="returnForm.errors.reason" class="text-red-500 text-xs mt-1">{{ returnForm.errors.reason }}</p>
            </div>
            <div class="mb-6">
              <label class="block text-sm font-medium text-gray-700 mb-1">Details (optional)</label>
              <textarea v-model="returnForm.details" rows="3"
                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none"
                placeholder="Please describe the issue…" />
            </div>
            <div class="flex gap-3">
              <button type="button" @click="showReturnModal = false"
                class="flex-1 py-2 border border-gray-200 rounded-lg text-sm text-gray-600 hover:bg-gray-50 transition">
                Cancel
              </button>
              <button type="submit" :disabled="returnForm.processing"
                class="flex-1 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition disabled:opacity-60">
                Submit Request
              </button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>
  </Layout>
</template>

<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import Layout from '../../Layout.vue'

const props = defineProps({ order: Object })
const page = usePage()

const showReturnModal = ref(false)
const returnForm = useForm({ reason: '', details: '' })

const canRequestReturn = computed(() =>
  props.order.status === 'delivered' && props.order.payment_status === 'paid'
)

function submitReturn() {
  returnForm.post(route('account.orders.return', props.order.id), {
    onSuccess: () => { showReturnModal.value = false; returnForm.reset() },
  })
}

const timelineSteps = [
  { key: 'pending', label: 'Placed' },
  { key: 'processing', label: 'Processing' },
  { key: 'paid', label: 'Paid' },
  { key: 'shipped', label: 'Shipped' },
  { key: 'delivered', label: 'Delivered' },
]

const statusOrder = ['pending', 'processing', 'paid', 'shipped', 'delivered']

function stepDone(key) {
  const currentIdx = statusOrder.indexOf(props.order.status)
  const stepIdx = statusOrder.indexOf(key)
  return stepIdx <= currentIdx
}

function formatPrice(price) {
  return new Intl.NumberFormat('el-GR', {
    style: 'currency',
    currency: page.props.currency ?? 'EUR',
  }).format(price)
}

function formatDate(date) {
  return new Date(date).toLocaleDateString('el-GR', { day: 'numeric', month: 'long', year: 'numeric' })
}
</script>
