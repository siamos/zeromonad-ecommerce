<template>
  <Layout>
    <Head :title="t('checkout.title')" />

    <div class="max-w-5xl mx-auto px-4 py-10">
      <Breadcrumb :items="[{ label: t('nav.home'), href: route('home') }, { label: t('nav.browse'), href: route('shop') }, { label: t('cart.title'), href: route('cart.index') }, { label: t('checkout.title') }]" />

      <!-- Step indicator -->
      <div class="flex items-center gap-0 mb-8 mt-2">
        <div class="flex items-center gap-2">
          <div class="w-7 h-7 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-xs font-bold">1</div>
          <span class="text-sm text-emerald-600 font-semibold">{{ t('checkout.step_cart') }}</span>
        </div>
        <div class="flex-1 h-px bg-emerald-200 mx-3" />
        <div class="flex items-center gap-2">
          <div class="w-7 h-7 rounded-full bg-emerald-600 text-white flex items-center justify-center text-xs font-bold">2</div>
          <span class="text-sm text-emerald-700 font-semibold">{{ t('checkout.step_details') }}</span>
        </div>
        <div class="flex-1 h-px bg-gray-200 mx-3" />
        <div class="flex items-center gap-2">
          <div class="w-7 h-7 rounded-full bg-gray-100 text-gray-400 flex items-center justify-center text-xs font-bold">3</div>
          <span class="text-sm text-gray-400">{{ t('checkout.step_payment') }}</span>
        </div>
      </div>

      <h1 class="text-3xl font-bold text-gray-900 mb-8">{{ t('checkout.title') }}</h1>

      <form @submit.prevent="submit">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
          <!-- Left: billing + payment -->
          <div class="lg:col-span-2 space-y-6">

            <!-- Billing details -->
            <div class="bg-white rounded-2xl border border-stone-100 shadow-sm p-6">
              <h2 class="font-bold text-gray-900 mb-5">{{ t('checkout.billing_details') }}</h2>
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('checkout.first_name') }}</label>
                  <input v-model="form.first_name" type="text" required
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500" />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('checkout.last_name') }}</label>
                  <input v-model="form.last_name" type="text" required
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500" />
                </div>
                <div class="sm:col-span-2">
                  <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('checkout.email') }}</label>
                  <input v-model="form.email" type="email" required
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500" />
                </div>
                <div class="sm:col-span-2">
                  <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('checkout.phone') }}</label>
                  <input v-model="form.phone" type="tel"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500" />
                </div>
                <div class="sm:col-span-2">
                  <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('checkout.address') }}</label>
                  <input v-model="form.address" type="text"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500" />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('checkout.city') }}</label>
                  <input v-model="form.city" type="text"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500" />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('checkout.postcode') }}</label>
                  <input v-model="form.postcode" type="text"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500" />
                </div>
              </div>
            </div>

            <!-- Payment method -->
            <div class="bg-white rounded-2xl border border-stone-100 shadow-sm p-6">
              <h2 class="font-bold text-gray-900 mb-5">{{ t('checkout.payment_method') }}</h2>
              <div class="space-y-3">
                <label
                  v-for="method in paymentMethods"
                  :key="method.key"
                  :class="['flex items-start gap-3 p-4 rounded-xl border cursor-pointer transition-colors',
                    form.payment_method === method.key
                      ? 'border-emerald-500 bg-emerald-50'
                      : 'border-gray-200 hover:border-gray-300']"
                >
                  <input type="radio" :value="method.key" v-model="form.payment_method" class="mt-0.5 text-emerald-600" />
                  <div>
                    <div class="font-medium text-gray-900">{{ method.label }}</div>
                    <div v-if="method.description" class="text-sm text-gray-500 mt-0.5">{{ method.description }}</div>
                  </div>
                </label>
              </div>

              <!-- Bank transfer IBAN details -->
              <div v-if="form.payment_method === 'bank_transfer' && bankAccounts.length" class="mt-5 space-y-3">
                <p class="text-sm text-gray-600 font-medium">{{ t('checkout.transfer_accounts') }}</p>
                <div v-for="bank in bankAccounts" :key="bank.bank" class="bg-stone-50 border border-stone-200 rounded-xl p-4 text-sm">
                  <div class="font-semibold text-gray-800 mb-1">{{ bank.bank }} {{ t('checkout.bank_label') }}</div>
                  <div class="text-gray-600">{{ bank.account_name }}</div>
                  <div class="font-mono text-gray-900 mt-1">{{ bank.iban }}</div>
                  <div v-if="bank.swift" class="text-gray-500 text-xs mt-0.5">SWIFT: {{ bank.swift }}</div>
                </div>
                <p class="text-xs text-gray-500">{{ t('checkout.transfer_ref') }}</p>
              </div>
            </div>
          </div>

          <!-- Right: order summary -->
          <div class="lg:col-span-1">
            <div class="bg-white rounded-2xl border border-stone-100 shadow-sm p-6 sticky top-6">
              <h2 class="font-bold text-gray-900 mb-4">{{ t('checkout.booking_summary') }}</h2>

              <div class="space-y-3 mb-4">
                <div v-for="item in cart.items" :key="item.id" class="flex gap-3">
                  <img :src="item.product.image_url ?? '/images/product-placeholder.svg'" :alt="item.product.name"
                    class="w-14 h-14 rounded-lg object-cover shrink-0" />
                  <div class="flex-1 min-w-0">
                    <div class="text-sm font-medium text-gray-900 truncate">{{ item.product.name }}</div>
                    <div v-if="item.product.activity_detail?.event_date" class="text-xs text-emerald-600">
                      {{ formatDate(item.product.activity_detail.event_date) }}
                    </div>
                    <div class="text-xs text-gray-500">{{ item.quantity }} × {{ formatPrice(item.product.price) }}</div>
                  </div>
                </div>
              </div>

              <div class="border-t border-gray-100 pt-4 space-y-2 text-sm">
                <div class="flex justify-between text-gray-600">
                  <span>{{ t('checkout.subtotal') }}</span>
                  <span>{{ formatPrice(cart.subtotal) }}</span>
                </div>
                <div v-if="cart.discount_amount > 0" class="flex justify-between text-emerald-600">
                  <span>{{ t('checkout.discount') }}</span>
                  <span>−{{ formatPrice(cart.discount_amount) }}</span>
                </div>
                <div v-if="pointsDiscount > 0" class="flex justify-between text-emerald-600 text-sm">
                  <span>{{ t('checkout.points_discount') }}</span>
                  <span>-{{ formatPrice(pointsDiscount) }}</span>
                </div>
                <div class="flex justify-between font-bold text-gray-900 text-base pt-2 border-t border-gray-100">
                  <span>{{ t('checkout.total') }}</span>
                  <span>{{ formatPrice(Math.max(0, cart.total - pointsDiscount)) }}</span>
                </div>
              </div>

              <!-- Loyalty points redemption -->
              <div v-if="pointsBalance > 0" class="mt-4 p-3 bg-emerald-50 rounded-xl border border-emerald-100">
                <div class="flex items-center justify-between mb-2">
                  <span class="text-sm font-medium text-emerald-900">{{ t('checkout.use_points') }}</span>
                  <span class="text-xs text-emerald-600">{{ pointsBalance }} {{ t('checkout.points_available') }}</span>
                </div>
                <div class="flex items-center gap-2">
                  <input type="number" v-model.number="form.use_points" min="0" :max="maxRedeemable" step="100"
                    class="w-24 border border-emerald-200 rounded-lg px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-400" />
                  <span class="text-xs text-gray-500">{{ t('checkout.points_100_1_euro') }}</span>
                </div>
              </div>

              <button type="submit" :disabled="submitting || !form.payment_method"
                class="w-full mt-5 bg-emerald-600 text-white py-3 rounded-xl font-semibold hover:bg-emerald-700 transition-colors disabled:opacity-60 cursor-pointer">
                {{ submitting ? t('checkout.processing') : t('checkout.confirm_booking') }}
              </button>
              <div class="flex items-center justify-center gap-2 mt-3 text-xs text-gray-400">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
                {{ t('checkout.secure_badge') }}
              </div>
            </div>
          </div>
        </div>
      </form>
    </div>
  </Layout>
</template>

<script setup>
import { Head, router, usePage } from '@inertiajs/vue3'
import { reactive, ref, computed } from 'vue'
import Layout from '../Layout.vue'
import { useI18n } from '@/composables/useI18n'
import Breadcrumb from '@/components/Breadcrumb.vue'

const { t } = useI18n()
const route = window.route

const props = defineProps({
  cart:           Object,
  paymentMethods: Array,
  bankAccounts:   Array,
  user:           Object,
  pointsBalance:  { type: Number, default: 0 },
})

const page = usePage()

const form = reactive({
  first_name:     props.user?.name?.split(' ')[0] ?? '',
  last_name:      props.user?.name?.split(' ').slice(1).join(' ') ?? '',
  email:          props.user?.email ?? '',
  phone:          '',
  address:        '',
  city:           '',
  postcode:       '',
  payment_method: props.paymentMethods?.[0]?.key ?? '',
  use_points:     0,
})

const submitting = ref(false)
const maxRedeemable = computed(() => Math.min(props.pointsBalance, Math.floor(props.cart.total * 100)))
const pointsDiscount = computed(() => Math.min(form.use_points, props.pointsBalance) / 100)

function formatPrice(price) {
  return new Intl.NumberFormat('el-GR', {
    style: 'currency',
    currency: page.props.currency ?? 'EUR',
  }).format(price)
}

function formatDate(date) {
  return new Date(date).toLocaleDateString('el-GR', { day: 'numeric', month: 'short' })
}

function submit() {
  submitting.value = true
  router.post(route('checkout.process'), form, {
    onFinish: () => { submitting.value = false },
  })
}
</script>
