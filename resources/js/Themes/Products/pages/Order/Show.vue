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
        <span class="text-sm px-3 py-1.5 rounded-full font-medium"
          :class="{
            'bg-yellow-100 text-yellow-800': order.status === 'pending',
            'bg-blue-100 text-blue-800': order.status === 'processing',
            'bg-green-100 text-green-800': order.status === 'delivered',
            'bg-red-100 text-red-800': order.status === 'cancelled',
          }">
          {{ order.status }}
        </span>
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
  </Layout>
</template>

<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3'
import Layout from '../../Layout.vue'

const props = defineProps({ order: Object })
const page = usePage()

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
