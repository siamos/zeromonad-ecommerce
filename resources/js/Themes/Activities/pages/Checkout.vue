<template>
  <Layout>
    <Head title="Checkout" />

    <div class="max-w-5xl mx-auto px-4 py-10">
      <h1 class="text-3xl font-bold text-gray-900 mb-8">Checkout</h1>

      <form @submit.prevent="submit">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
          <!-- Left: billing + payment -->
          <div class="lg:col-span-2 space-y-6">

            <!-- Billing details -->
            <div class="bg-white rounded-2xl border border-stone-100 shadow-sm p-6">
              <h2 class="font-bold text-gray-900 mb-5">Billing Details</h2>
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">First name</label>
                  <input v-model="form.first_name" type="text" required
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500" />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Last name</label>
                  <input v-model="form.last_name" type="text" required
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500" />
                </div>
                <div class="sm:col-span-2">
                  <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                  <input v-model="form.email" type="email" required
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500" />
                </div>
                <div class="sm:col-span-2">
                  <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                  <input v-model="form.phone" type="tel"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500" />
                </div>
                <div class="sm:col-span-2">
                  <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                  <input v-model="form.address" type="text"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500" />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">City</label>
                  <input v-model="form.city" type="text"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500" />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Postcode</label>
                  <input v-model="form.postcode" type="text"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500" />
                </div>
              </div>
            </div>

            <!-- Payment method -->
            <div class="bg-white rounded-2xl border border-stone-100 shadow-sm p-6">
              <h2 class="font-bold text-gray-900 mb-5">Payment Method</h2>
              <div class="space-y-3">
                <label
                  v-for="method in paymentMethods"
                  :key="method.key"
                  :class="['flex items-start gap-3 p-4 rounded-xl border cursor-pointer transition-colors',
                    form.payment_method === method.key
                      ? 'border-emerald-500 bg-emerald-50'
                      : 'border-gray-200 hover:border-gray-300']"
                >
                  <input
                    type="radio"
                    :value="method.key"
                    v-model="form.payment_method"
                    class="mt-0.5 text-emerald-600"
                  />
                  <div>
                    <div class="font-medium text-gray-900">{{ method.label }}</div>
                    <div v-if="method.description" class="text-sm text-gray-500 mt-0.5">{{ method.description }}</div>
                  </div>
                </label>
              </div>

              <!-- Bank transfer IBAN details -->
              <div v-if="form.payment_method === 'bank_transfer' && bankAccounts.length" class="mt-5 space-y-3">
                <p class="text-sm text-gray-600 font-medium">Transfer to any of these accounts:</p>
                <div
                  v-for="bank in bankAccounts"
                  :key="bank.bank"
                  class="bg-stone-50 border border-stone-200 rounded-xl p-4 text-sm"
                >
                  <div class="font-semibold text-gray-800 mb-1">{{ bank.bank }} Bank</div>
                  <div class="text-gray-600">{{ bank.account_name }}</div>
                  <div class="font-mono text-gray-900 mt-1">{{ bank.iban }}</div>
                  <div v-if="bank.swift" class="text-gray-500 text-xs mt-0.5">SWIFT: {{ bank.swift }}</div>
                </div>
                <p class="text-xs text-gray-500">Use your order number as the payment reference. Your booking will be confirmed once payment is verified.</p>
              </div>
            </div>

          </div>

          <!-- Right: order summary -->
          <div class="lg:col-span-1">
            <div class="bg-white rounded-2xl border border-stone-100 shadow-sm p-6 sticky top-6">
              <h2 class="font-bold text-gray-900 mb-4">Booking Summary</h2>

              <div class="space-y-3 mb-4">
                <div v-for="item in cart.items" :key="item.id" class="flex gap-3">
                  <img
                    :src="item.product.image_url ?? '/images/activity-placeholder.png'"
                    :alt="item.product.name"
                    class="w-14 h-14 rounded-lg object-cover shrink-0"
                  />
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
                  <span>Subtotal</span>
                  <span>{{ formatPrice(cart.subtotal) }}</span>
                </div>
                <div v-if="cart.discount_amount > 0" class="flex justify-between text-emerald-600">
                  <span>Discount</span>
                  <span>−{{ formatPrice(cart.discount_amount) }}</span>
                </div>
                <div class="flex justify-between font-bold text-gray-900 text-base pt-2 border-t border-gray-100">
                  <span>Total</span>
                  <span>{{ formatPrice(cart.total) }}</span>
                </div>
              </div>

              <button
                type="submit"
                :disabled="submitting || !form.payment_method"
                class="w-full mt-5 bg-emerald-600 text-white py-3 rounded-xl font-semibold hover:bg-emerald-700 transition-colors disabled:opacity-60"
              >
                {{ submitting ? 'Processing…' : 'Confirm Booking' }}
              </button>
            </div>
          </div>
        </div>
      </form>
    </div>
  </Layout>
</template>

<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { reactive, ref } from 'vue'
import Layout from '../Layout.vue'

const props = defineProps({
  cart:           Object,
  paymentMethods: Array,
  bankAccounts:   Array,
  user:           Object,
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
})

const submitting = ref(false)

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
