<template>
  <Layout>
    <Head title="Checkout" />
    <div class="max-w-4xl mx-auto px-4 py-10">
      <h1 class="text-3xl font-bold text-gray-900 mb-8">Checkout</h1>

      <form @submit.prevent="submitOrder" class="grid grid-cols-1 lg:grid-cols-5 gap-8">
        <!-- Billing Details -->
        <div class="lg:col-span-3 space-y-6">
          <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Billing Details</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                <input v-model="form.billing_address.name" type="text" required
                  class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-400 focus:outline-none" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input v-model="form.billing_address.email" type="email" required
                  class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-400 focus:outline-none" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                <input v-model="form.billing_address.phone" type="tel" required
                  class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-400 focus:outline-none" />
              </div>
              <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                <input v-model="form.billing_address.line1" type="text" required
                  class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-400 focus:outline-none" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">City</label>
                <input v-model="form.billing_address.city" type="text" required
                  class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-400 focus:outline-none" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Postal Code</label>
                <input v-model="form.billing_address.zip" type="text" required
                  class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-400 focus:outline-none" />
              </div>
            </div>
          </div>

          <!-- Payment Method -->
          <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Payment Method</h2>
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
              <p class="font-semibold text-blue-900 mb-3">Transfer to one of these accounts:</p>
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
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Order Summary</h2>
            <div class="space-y-3 text-sm">
              <div v-for="item in cart.items" :key="item.id" class="flex justify-between text-gray-600">
                <span>{{ item.product?.name }} × {{ item.quantity }}</span>
                <span>{{ formatPrice(item.unit_price * item.quantity) }}</span>
              </div>
              <div class="border-t border-gray-100 pt-3 flex justify-between font-bold text-gray-900">
                <span>Total</span>
                <span>{{ formatPrice(cart.total) }}</span>
              </div>
            </div>
            <button type="submit" :disabled="!form.payment_method"
              class="mt-6 w-full bg-indigo-600 text-white py-3 rounded-xl font-semibold hover:bg-indigo-700 disabled:bg-gray-300 disabled:cursor-not-allowed transition-colors">
              Place Order
            </button>
          </div>
        </div>
      </form>
    </div>
  </Layout>
</template>

<script setup>
import { Head, router, usePage } from '@inertiajs/vue3'
import { reactive } from 'vue'
import Layout from '../Layout.vue'

const props = defineProps({
  cart: Object,
  paymentMethods: Array,
  bankAccounts: Array,
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
})

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
