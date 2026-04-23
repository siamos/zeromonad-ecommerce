<template>
  <Layout>
    <Head :title="t('checkout.title')" />
    <div class="max-w-4xl mx-auto px-4 py-10">
      <Breadcrumb :items="[{ label: t('nav.home'), href: route('home') }, { label: t('shop.all_products'), href: route('shop') }, { label: t('cart.title'), href: route('cart.index') }, { label: t('checkout.title') }]" />

      <!-- Step indicator -->
      <div class="flex items-center gap-0 mb-8 mt-2">
        <div class="flex items-center gap-2">
          <div class="w-7 h-7 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center text-xs font-bold">1</div>
          <span class="text-sm text-indigo-600 font-semibold">{{ t('checkout.step_cart') }}</span>
        </div>
        <div class="flex-1 h-px bg-indigo-200 mx-3" />
        <div class="flex items-center gap-2">
          <div class="w-7 h-7 rounded-full bg-indigo-600 text-white flex items-center justify-center text-xs font-bold">2</div>
          <span class="text-sm text-indigo-700 font-semibold">{{ t('checkout.step_details') }}</span>
        </div>
        <div class="flex-1 h-px bg-gray-200 mx-3" />
        <div class="flex items-center gap-2">
          <div class="w-7 h-7 rounded-full bg-gray-100 text-gray-400 flex items-center justify-center text-xs font-bold">3</div>
          <span class="text-sm text-gray-400">{{ t('checkout.step_payment') }}</span>
        </div>
      </div>

      <h1 class="text-3xl font-bold text-gray-900 mb-8">{{ t('checkout.title') }}</h1>

      <form @submit.prevent="submitOrder" class="grid grid-cols-1 lg:grid-cols-5 gap-8">
        <!-- Billing Details -->
        <div class="lg:col-span-3 space-y-6">
          <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ t('checkout.billing_details') }}</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('checkout.full_name') }}</label>
                <input v-model="form.billing_address.name" type="text" required
                  class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-400 focus:outline-none" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('checkout.email') }}</label>
                <input v-model="form.billing_address.email" type="email" required
                  class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-400 focus:outline-none" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('checkout.phone') }}</label>
                <input v-model="form.billing_address.phone" type="tel" required
                  class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-400 focus:outline-none" />
              </div>
              <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('checkout.address') }}</label>
                <input v-model="form.billing_address.line1" type="text" required
                  class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-400 focus:outline-none" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('checkout.city') }}</label>
                <input v-model="form.billing_address.city" type="text" required
                  class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-400 focus:outline-none" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('checkout.postal_code') }}</label>
                <input v-model="form.billing_address.zip" type="text" required
                  class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-400 focus:outline-none" />
              </div>
            </div>
          </div>

          <!-- Payment Method -->
          <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ t('checkout.payment_method') }}</h2>
            <div class="space-y-3">
              <label
                v-for="method in paymentMethods"
                :key="method.key"
                class="flex items-center gap-3 p-4 border rounded-xl cursor-pointer transition-colors"
                :class="form.payment_method === method.key ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200 hover:border-gray-300'"
              >
                <input type="radio" :value="method.key" v-model="form.payment_method" class="text-indigo-600" />
                <span class="font-medium text-gray-900">{{ method.label }}</span>
              </label>
            </div>

            <!-- Bank transfer details -->
            <div v-if="form.payment_method === 'bank_transfer' && bankAccounts?.length"
              class="mt-4 bg-blue-50 rounded-xl p-4 text-sm">
              <p class="font-semibold text-blue-900 mb-3">{{ t('checkout.transfer_accounts') }}</p>
              <div v-for="account in bankAccounts" :key="account.iban" class="mb-3 last:mb-0">
                <div class="font-medium text-blue-800">{{ account.bank }}</div>
                <div class="text-blue-700">{{ account.account_name }}</div>
                <div class="font-mono text-blue-900">{{ account.iban }}</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Order Summary -->
        <div class="lg:col-span-2">
          <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100 sticky top-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ t('checkout.order_summary_label') }}</h2>
            <div class="space-y-3 text-sm">
              <div v-for="item in cart.items" :key="item.id" class="flex justify-between text-gray-600">
                <span>{{ item.product?.name }} × {{ item.quantity }}</span>
                <span>{{ formatPrice(item.unit_price * item.quantity) }}</span>
              </div>
              <div v-if="cart.discount_amount > 0" class="flex justify-between text-green-600">
                <span>{{ t('checkout.discount') }}</span>
                <span>-{{ formatPrice(cart.discount_amount) }}</span>
              </div>
              <div v-if="pointsDiscount > 0" class="flex justify-between text-indigo-600 text-xs">
                <span>{{ t('checkout.points_discount') }}</span>
                <span>-{{ formatPrice(pointsDiscount) }}</span>
              </div>
              <div class="border-t border-gray-100 pt-3 flex justify-between font-bold text-gray-900">
                <span>{{ t('checkout.total') }}</span>
                <span>{{ formatPrice(Math.max(0, cart.total - pointsDiscount)) }}</span>
              </div>
            </div>

            <!-- Loyalty points redemption -->
            <div v-if="pointsBalance > 0" class="mt-4 p-3 bg-indigo-50 rounded-xl border border-indigo-100">
              <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-indigo-900">{{ t('checkout.use_points') }}</span>
                <span class="text-xs text-indigo-600">{{ pointsBalance }} {{ t('checkout.points_available') }}</span>
              </div>
              <div class="flex items-center gap-2">
                <input type="number" v-model.number="form.use_points" min="0" :max="maxRedeemable" step="100"
                  class="w-24 border border-indigo-200 rounded-lg px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400" />
                <span class="text-xs text-gray-500">{{ t('checkout.points_100_1_euro') }}</span>
              </div>
            </div>

            <button type="submit" :disabled="!form.payment_method"
              class="mt-6 w-full bg-indigo-600 text-white py-3 rounded-xl font-semibold hover:bg-indigo-700 disabled:bg-gray-300 disabled:cursor-not-allowed transition-colors cursor-pointer">
              {{ t('checkout.place_order') }}
            </button>
            <div class="flex items-center justify-center gap-2 mt-3 text-xs text-gray-400">
              <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
              </svg>
              {{ t('checkout.secure_badge') }}
            </div>
          </div>
        </div>
      </form>
    </div>
  </Layout>
</template>

<script setup>
import { Head, router, usePage } from '@inertiajs/vue3'
import { reactive, computed } from 'vue'
import Layout from '../Layout.vue'
import { useI18n } from '@/composables/useI18n'
import Breadcrumb from '@/components/Breadcrumb.vue'

const { t } = useI18n()
const route = window.route

const props = defineProps({
  cart: Object,
  paymentMethods: Array,
  bankAccounts: Array,
  pointsBalance: { type: Number, default: 0 },
})

const page = usePage()

const form = reactive({
  payment_method: props.paymentMethods?.[0]?.key ?? '',
  billing_address: {
    name: page.props.auth.user?.name ?? '',
    email: page.props.auth.user?.email ?? '',
    phone: '',
    line1: '',
    city: '',
    zip: '',
  },
  use_points: 0,
})

const maxRedeemable = computed(() => Math.min(props.pointsBalance, Math.floor(props.cart.total * 100)))
const pointsDiscount = computed(() => Math.min(form.use_points, props.pointsBalance) / 100)

function formatPrice(price) {
  return new Intl.NumberFormat('el-GR', {
    style: 'currency',
    currency: page.props.currency ?? 'EUR',
  }).format(price)
}

function submitOrder() {
  router.post(route('checkout.process'), form)
}
</script>
