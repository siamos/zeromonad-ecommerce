<template>
  <Layout>
    <Head :title="t('cart.title')" />
    <div class="max-w-4xl mx-auto px-4 py-10">
      <h1 class="text-3xl font-bold text-gray-900 mb-8">{{ t('cart.title') }}</h1>

      <div v-if="cart?.items?.length" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Items -->
        <div class="lg:col-span-2 space-y-4">
          <div v-for="item in cart.items" :key="item.id"
            class="bg-white rounded-xl p-4 flex gap-4 shadow-sm border border-gray-100">
            <img :src="item.product?.image_url ?? '/images/product-placeholder.svg'" :alt="item.product?.name"
              class="w-20 h-20 rounded-lg object-cover shrink-0" />
            <div class="flex-1">
              <h3 class="font-semibold text-gray-900">{{ item.product?.name }}</h3>
              <p class="text-sm text-gray-500 mt-1">{{ formatPrice(item.unit_price) }} {{ t('cart.each') }}</p>
            </div>
            <div class="flex flex-col items-end gap-2">
              <span class="font-bold text-gray-900">{{ formatPrice(item.unit_price * item.quantity) }}</span>
              <span class="text-sm text-gray-500">{{ t('cart.qty') }} {{ item.quantity }}</span>
              <Link :href="route('cart.remove', item.id)" method="delete" as="button"
                class="text-xs text-red-500 hover:text-red-700 transition-colors cursor-pointer">
                {{ t('cart.remove') }}
              </Link>
            </div>
          </div>
        </div>

        <!-- Summary -->
        <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100 h-fit">
          <h2 class="text-xl font-bold text-gray-900 mb-6">{{ t('cart.order_summary') }}</h2>

          <div class="space-y-3 text-sm">
            <div class="flex justify-between text-gray-600">
              <span>{{ t('cart.subtotal') }}</span>
              <span>{{ formatPrice(cart.subtotal) }}</span>
            </div>
            <div v-if="cart.discount > 0" class="flex justify-between text-green-600">
              <span>{{ t('cart.discount') }}</span>
              <span>-{{ formatPrice(cart.discount) }}</span>
            </div>
            <div class="border-t border-gray-100 pt-3 flex justify-between font-bold text-gray-900 text-base">
              <span>{{ t('cart.total') }}</span>
              <span>{{ formatPrice(cart.total) }}</span>
            </div>
          </div>

          <!-- Coupon -->
          <form @submit.prevent="applyCoupon" class="mt-6">
            <div class="flex gap-2">
              <input v-model="couponCode" type="text" :placeholder="t('cart.coupon_placeholder')"
                class="flex-1 border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400" />
              <button type="submit"
                class="bg-gray-900 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-700 transition-colors cursor-pointer">
                {{ t('cart.coupon_apply') }}
              </button>
            </div>
          </form>

          <Link :href="route('checkout.index')"
            class="mt-6 block w-full bg-indigo-600 text-white text-center py-3 rounded-xl font-semibold hover:bg-indigo-700 transition-colors">
            {{ t('cart.checkout') }}
          </Link>
        </div>
      </div>

      <div v-else class="text-center py-20">
        <p class="text-xl text-gray-400 mb-6">{{ t('cart.empty_subtitle_products') }}</p>
        <Link :href="route('shop')" class="text-indigo-600 font-semibold hover:underline">{{ t('cart.start_shopping') }}</Link>
      </div>
    </div>
  </Layout>
</template>

<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { ref } from 'vue'
import Layout from '../Layout.vue'
import { useI18n } from '@/composables/useI18n'

const { t } = useI18n()
const route = window.route
const props = defineProps({ cart: Object })
const page = usePage()
const couponCode = ref('')

function formatPrice(price) {
  return new Intl.NumberFormat('el-GR', {
    style: 'currency',
    currency: page.props.currency ?? 'EUR',
  }).format(price)
}

function applyCoupon() {
  if (couponCode.value) {
    router.post(route('cart.coupon'), { code: couponCode.value })
  }
}
</script>
