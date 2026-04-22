<template>
  <Layout>
    <Head title="Stay Confirmed" />
    <div class="max-w-2xl mx-auto px-4 py-16 text-center">

      <div class="flex items-center justify-center w-20 h-20 mx-auto mb-6 rounded-full bg-amber-100">
        <svg class="w-10 h-10 text-amber-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
        </svg>
      </div>

      <h1 class="text-3xl font-bold text-gray-900 mb-2">Stay Confirmed!</h1>
      <p class="text-gray-500 mb-2">Thank you for your booking.</p>
      <p class="text-sm font-mono text-amber-600 mb-10">{{ order.order_number }}</p>

      <div class="bg-white rounded-2xl shadow-sm border border-stone-100 text-left mb-6">
        <div class="p-6 border-b border-stone-100">
          <h2 class="font-semibold text-gray-900">Booking Summary</h2>
        </div>
        <div class="divide-y divide-stone-100">
          <div v-for="item in order.items" :key="item.id" class="px-6 py-4 flex justify-between text-sm">
            <span class="text-gray-700">{{ item.product_name }} <span class="text-gray-400">× {{ item.quantity }} nights</span></span>
            <span class="font-medium text-gray-900">{{ formatPrice(item.unit_price * item.quantity) }}</span>
          </div>
        </div>
        <div class="px-6 py-4 border-t border-stone-100 flex justify-between font-bold text-gray-900">
          <span>Total</span>
          <span>{{ formatPrice(order.total) }}</span>
        </div>
      </div>

      <div v-if="order.billing_address" class="bg-white rounded-2xl shadow-sm border border-stone-100 text-left mb-10 p-6">
        <h2 class="font-semibold text-gray-900 mb-3">Billing Details</h2>
        <p class="text-sm text-gray-600">{{ order.billing_address.name }}</p>
        <p class="text-sm text-gray-600">{{ order.billing_address.line1 }}</p>
        <p class="text-sm text-gray-600">{{ order.billing_address.city }}, {{ order.billing_address.zip }}</p>
        <p class="text-sm text-gray-600">{{ order.billing_address.email }}</p>
      </div>

      <div v-if="order.payment_method === 'bank_transfer'"
        class="bg-amber-50 border border-amber-200 rounded-2xl p-4 text-sm text-amber-800 mb-8 text-left">
        <p class="font-semibold mb-1">Awaiting Payment</p>
        <p>Please complete your bank transfer using <span class="font-mono font-semibold">{{ order.order_number }}</span> as the reference. Your stay will be confirmed once payment is verified.</p>
      </div>

      <div class="flex flex-col sm:flex-row gap-3 justify-center">
        <Link :href="route('account.orders')"
          class="px-6 py-3 bg-amber-600 text-white rounded-xl font-semibold hover:bg-amber-700 transition-colors">
          View My Stays
        </Link>
        <Link :href="route('shop')"
          class="px-6 py-3 bg-stone-100 text-gray-700 rounded-xl font-semibold hover:bg-stone-200 transition-colors">
          Browse More Stays
        </Link>
      </div>
    </div>
  </Layout>
</template>

<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3'
import Layout from '../Layout.vue'

const props = defineProps({ order: Object })
const page = usePage()

function formatPrice(price) {
  return new Intl.NumberFormat('el-GR', {
    style: 'currency',
    currency: page.props.currency ?? 'EUR',
  }).format(price)
}
</script>
