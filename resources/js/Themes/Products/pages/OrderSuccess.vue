<template>
  <Layout>
    <Head title="Order Confirmed" />
    <OrderSuccessModal
      :order="order"
      :is-guest="isGuest"
      title="Order Confirmed!"
      subtitle="Thank you for your purchase."
      view-orders-label="View My Orders"
      shop-label="Continue Shopping"
    />
    <div class="max-w-2xl mx-auto px-4 py-16 text-center">

      <!-- Success icon -->
      <div class="flex items-center justify-center w-20 h-20 mx-auto mb-6 rounded-full bg-green-100">
        <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
        </svg>
      </div>

      <h1 class="text-3xl font-bold text-gray-900 mb-2">Order Confirmed!</h1>
      <p class="text-gray-500 mb-2">Thank you for your purchase.</p>
      <p class="text-sm font-mono text-indigo-600 mb-10">{{ order.order_number }}</p>

      <!-- Order items -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-100 text-left mb-6">
        <div class="p-6 border-b border-gray-100">
          <h2 class="font-semibold text-gray-900">Order Summary</h2>
        </div>
        <div class="divide-y divide-gray-100">
          <div v-for="item in order.items" :key="item.id" class="px-6 py-4 flex justify-between text-sm">
            <span class="text-gray-700">{{ item.product_name }} <span class="text-gray-400">× {{ item.quantity }}</span></span>
            <span class="font-medium text-gray-900">{{ formatPrice(item.unit_price * item.quantity) }}</span>
          </div>
        </div>
        <div class="px-6 py-4 border-t border-gray-100 flex justify-between font-bold text-gray-900">
          <span>Total</span>
          <span>{{ formatPrice(order.total) }}</span>
        </div>
      </div>

      <!-- Billing address -->
      <div v-if="order.billing_address" class="bg-white rounded-xl shadow-sm border border-gray-100 text-left mb-10 p-6">
        <h2 class="font-semibold text-gray-900 mb-3">Billing Address</h2>
        <p class="text-sm text-gray-600">{{ order.billing_address.name }}</p>
        <p class="text-sm text-gray-600">{{ order.billing_address.line1 }}</p>
        <p class="text-sm text-gray-600">{{ order.billing_address.city }}, {{ order.billing_address.zip }}</p>
        <p class="text-sm text-gray-600">{{ order.billing_address.email }}</p>
      </div>

      <!-- Payment notice for bank transfer -->
      <div v-if="order.payment_method === 'bank_transfer'"
        class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 text-sm text-yellow-800 mb-8 text-left">
        <p class="font-semibold mb-1">Awaiting Payment</p>
        <p>Please complete your bank transfer using <span class="font-mono font-semibold">{{ order.order_number }}</span> as the reference. Your order will be confirmed once payment is verified.</p>
      </div>

      <!-- Guest upsell -->
      <div v-if="isGuest" class="bg-indigo-50 border border-indigo-100 rounded-xl p-6 mb-8 text-left">
        <h3 class="font-semibold text-indigo-900 mb-1">{{ t('checkout.guest_upsell_title') }}</h3>
        <p class="text-sm text-indigo-700 mb-4">{{ t('checkout.guest_upsell_desc') }}</p>
        <Link :href="route('register')"
          class="inline-block px-5 py-2 bg-indigo-600 text-white rounded-lg text-sm font-semibold hover:bg-indigo-700 transition-colors">
          {{ t('checkout.guest_upsell_cta') }}
        </Link>
      </div>

      <div class="flex flex-col sm:flex-row gap-3 justify-center">
        <Link v-if="!isGuest" :href="route('account.orders')"
          class="px-6 py-3 bg-indigo-600 text-white rounded-xl font-semibold hover:bg-indigo-700 transition-colors">
          View My Orders
        </Link>
        <Link :href="route('shop')"
          class="px-6 py-3 bg-gray-100 text-gray-700 rounded-xl font-semibold hover:bg-gray-200 transition-colors">
          Continue Shopping
        </Link>
      </div>
    </div>
  </Layout>
</template>

<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3'
import Layout from '../Layout.vue'
import OrderSuccessModal from '@/components/OrderSuccessModal.vue'
import { useI18n } from '@/composables/useI18n'

const { t } = useI18n()
const props = defineProps({ order: Object, isGuest: Boolean })
const page = usePage()

function formatPrice(price) {
  return new Intl.NumberFormat('el-GR', {
    style: 'currency',
    currency: page.props.currency ?? 'EUR',
  }).format(price)
}
</script>
