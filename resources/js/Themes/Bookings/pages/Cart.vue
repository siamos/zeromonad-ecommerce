<template>
  <Layout>
    <Head :title="t('cart.title')" />

    <div class="max-w-5xl mx-auto px-4 py-10">
      <Breadcrumb :items="[{ label: t('nav.home'), href: route('home') }, { label: t('nav.browse'), href: route('shop') }, { label: t('cart.title') }]" />
      <h1 class="text-3xl font-bold text-gray-900 mb-8">{{ t('cart.title') }}</h1>

      <div v-if="cart && cart.items?.length" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Items -->
        <div class="lg:col-span-2 space-y-4">
          <div
            v-for="item in cart.items"
            :key="item.id"
            class="bg-white rounded-2xl border border-stone-100 shadow-sm p-5 flex gap-4"
          >
            <img
              :src="item.product.image_url ?? '/images/product-placeholder.svg'"
              :alt="item.product.name"
              class="w-20 h-20 rounded-xl object-cover shrink-0"
            />
            <div class="flex-1 min-w-0">
              <h3 class="font-semibold text-gray-900 truncate">{{ item.product.name }}</h3>
              <div v-if="item.product.activity_detail?.location" class="text-xs text-gray-500 mt-0.5 flex items-center gap-1">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                </svg>
                {{ item.product.activity_detail.location }}
              </div>
              <div v-if="item.options?.check_in && item.options?.check_out" class="text-sm text-amber-600 font-medium mt-0.5">
                {{ formatDate(item.options.check_in) }} → {{ formatDate(item.options.check_out) }}
                ({{ nightsCount(item.options.check_in, item.options.check_out) }} {{ t('booking.nights') }})
              </div>
              <div v-else-if="item.options?.booking_date" class="text-sm text-amber-600 font-medium mt-0.5">
                {{ formatDate(item.options.booking_date) }}
              </div>
              <div class="flex items-center justify-between mt-3">
                <div class="flex items-center gap-2">
                  <button
                    @click="updateQty(item, item.quantity - 1)"
                    class="w-7 h-7 rounded-full border border-gray-200 flex items-center justify-center text-gray-600 hover:border-amber-500 text-sm cursor-pointer"
                  >−</button>
                  <span class="text-sm font-medium w-4 text-center">{{ item.quantity }}</span>
                  <button
                    @click="updateQty(item, item.quantity + 1)"
                    class="w-7 h-7 rounded-full border border-gray-200 flex items-center justify-center text-gray-600 hover:border-amber-500 text-sm cursor-pointer"
                  >+</button>
                </div>
                <div class="flex items-center gap-4">
                  <span class="font-bold text-gray-900">{{ formatPrice(item.product.price * item.quantity) }}</span>
                  <Link v-if="page.props.auth?.user" :href="route('saved-items.store')" method="post"
                    :data="{ saveable_type: 'accommodation', saveable_id: item.product_id }" as="button"
                    class="text-xs text-amber-500 hover:text-amber-700 transition-colors cursor-pointer whitespace-nowrap">
                    Save
                  </Link>
                  <Link :href="route('cart.remove', item.id)" method="delete" as="button"
                    class="text-gray-400 hover:text-red-500 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                  </Link>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Summary -->
        <div class="lg:col-span-1">
          <div class="bg-white rounded-2xl border border-stone-100 shadow-sm p-6 sticky top-6 space-y-4">
            <h2 class="font-bold text-gray-900 text-lg">{{ t('cart.order_summary') }}</h2>

            <!-- Free shipping bar -->
            <div>
              <div v-if="freeShippingRemaining <= 0" class="text-xs text-amber-600 font-semibold mb-1">{{ t('cart.free_shipping_unlocked') }}</div>
              <div v-else class="text-xs text-gray-500 mb-1">{{ t('cart.free_shipping_remaining').replace('{amount}', formatPrice(freeShippingRemaining)) }}</div>
              <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden">
                <div class="h-full bg-amber-500 rounded-full transition-all duration-500" :style="{ width: freeShippingPercent + '%' }" />
              </div>
            </div>

            <!-- Coupon -->
            <form @submit.prevent="applyCoupon" class="flex flex-col gap-2 sm:flex-row">
              <input
                v-model="couponCode"
                type="text"
                :placeholder="t('cart.coupon_placeholder')"
                class="flex-1 min-w-0 border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500"
              />
              <button type="submit" class="w-full sm:w-auto whitespace-nowrap bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-2 rounded-lg text-sm font-medium transition-colors cursor-pointer">
                {{ t('cart.coupon_apply') }}
              </button>
            </form>

            <!-- Gift Card -->
            <form @submit.prevent="applyGiftCard" class="flex flex-col gap-2 sm:flex-row">
              <input
                v-model="giftCardCode"
                type="text"
                placeholder="Gift card code (GC-XXXX-XXXX)"
                class="flex-1 min-w-0 border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500"
              />
              <button type="submit" class="w-full sm:w-auto whitespace-nowrap bg-amber-50 text-amber-700 border border-amber-200 px-3 py-2 rounded-lg text-sm font-medium hover:bg-amber-100 transition-colors cursor-pointer">
                Apply Gift Card
              </button>
            </form>

            <div v-if="cart.coupon" class="flex items-center justify-between text-sm text-amber-700 bg-amber-50 rounded-lg px-3 py-2">
              <span>{{ t('cart.coupon_label') }} {{ cart.coupon.code }}</span>
              <span>−{{ formatPrice(cart.coupon.discount_value) }}</span>
            </div>

            <div class="space-y-2 text-sm border-t border-gray-100 pt-4">
              <div class="flex justify-between text-gray-600">
                <span>{{ t('cart.subtotal') }}</span>
                <span>{{ formatPrice(cart.subtotal) }}</span>
              </div>
              <div v-if="cart.discount_amount > 0" class="flex justify-between text-amber-600">
                <span>{{ t('cart.discount') }}</span>
                <span>−{{ formatPrice(cart.discount_amount) }}</span>
              </div>
              <div class="flex justify-between font-bold text-gray-900 text-base pt-2 border-t border-gray-100">
                <span>{{ t('cart.total') }}</span>
                <span>{{ formatPrice(cart.total) }}</span>
              </div>
            </div>

            <Link
              :href="route('checkout.index')"
              class="block w-full text-center bg-amber-600 text-white py-3 rounded-xl font-semibold hover:bg-amber-700 transition-colors"
            >
              {{ t('cart.checkout') }}
            </Link>

            <Link :href="route('shop')" class="block text-center text-sm text-gray-500 hover:text-amber-600">
              {{ t('cart.continue_browsing') }}
            </Link>
          </div>
        </div>
      </div>

      <!-- Empty cart -->
      <div v-else class="text-center py-20">
        <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
        </svg>
        <h2 class="text-xl font-semibold text-gray-700 mb-2">{{ t('cart.empty_title') }}</h2>
        <p class="text-gray-500 mb-6">{{ t('cart.empty_subtitle_bookings') }}</p>
        <Link :href="route('shop')"
          class="bg-amber-600 text-white px-6 py-3 rounded-xl font-semibold hover:bg-amber-700 transition-colors">
          {{ t('cart.empty_browse_bookings') }}
        </Link>
      </div>
    </div>
  </Layout>
</template>

<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import Layout from '../Layout.vue'
import { useI18n } from '@/composables/useI18n'
import Breadcrumb from '@/components/Breadcrumb.vue'

const { t } = useI18n()
const route = window.route
const props = defineProps({ cart: Object })
const page = usePage()
const couponCode = ref('')
const giftCardCode = ref('')

const freeShippingThreshold = computed(() => page.props.free_shipping_threshold ?? 50)
const freeShippingRemaining = computed(() => Math.max(0, freeShippingThreshold.value - (props.cart?.subtotal ?? 0)))
const freeShippingPercent = computed(() => Math.min(100, ((props.cart?.subtotal ?? 0) / freeShippingThreshold.value) * 100))

function formatPrice(price) {
  return new Intl.NumberFormat('el-GR', {
    style: 'currency',
    currency: page.props.currency ?? 'EUR',
  }).format(price)
}

function formatDate(date) {
  return new Date(date).toLocaleDateString('el-GR', { day: 'numeric', month: 'short', year: 'numeric' })
}

function nightsCount(checkIn, checkOut) {
  return Math.max(0, Math.floor((new Date(checkOut) - new Date(checkIn)) / (1000 * 60 * 60 * 24)))
}

function updateQty(item, qty) {
  if (qty < 1) {
    router.delete(route('cart.remove', item.id))
  } else {
    router.patch(route('cart.update', item.id), { quantity: qty }, { preserveScroll: true })
  }
}

function applyCoupon() {
  router.post(route('cart.coupon'), { code: couponCode.value }, { preserveScroll: true })
}

function applyGiftCard() {
  if (giftCardCode.value) {
    router.post(route('cart.gift-card'), { gift_card_code: giftCardCode.value }, { preserveScroll: true })
  }
}
</script>
