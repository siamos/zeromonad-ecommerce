<template>
  <div class="group bg-white rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-all border border-gray-100">
    <div class="relative overflow-hidden">
      <Link :href="route('product.show', product.slug)">
        <img :src="product.image_url ?? '/images/product-placeholder.svg'" :alt="product.name"
          class="w-full h-48 object-cover group-hover:scale-105 transition-transform duration-300" />
      </Link>
      <!-- Status badges (top-left) -->
      <div class="absolute top-2 left-2 flex flex-col gap-1">
        <span v-if="isOnSale" class="bg-red-500 text-white text-xs font-bold px-2 py-0.5 rounded-full">{{ t('card.sale') }}</span>
        <span v-else-if="isLowStock" class="bg-amber-500 text-white text-xs font-bold px-2 py-0.5 rounded-full">{{ t('card.low_stock') }}</span>
        <span v-else-if="isNew" class="bg-indigo-600 text-white text-xs font-bold px-2 py-0.5 rounded-full">{{ t('card.new') }}</span>
      </div>
      <!-- Wishlist (top-right) -->
      <button @click="toggle" :disabled="loading" aria-label="Toggle wishlist"
        class="absolute top-2 right-2 w-8 h-8 rounded-full bg-white/90 backdrop-blur-sm flex items-center justify-center shadow hover:scale-110 transition-transform cursor-pointer">
        <svg class="w-4 h-4 transition-colors" :class="isWishlisted ? 'text-red-500 fill-red-500' : 'text-gray-400'" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
        </svg>
      </button>
    </div>
    <div class="p-4">
      <Link :href="route('product.show', product.slug)">
        <h3 class="font-semibold text-gray-900 hover:text-indigo-600 transition-colors line-clamp-2">
          {{ product.name }}
        </h3>
      </Link>
      <!-- Star ratings -->
      <div v-if="product.reviews_count > 0" class="flex items-center gap-1 mt-1">
        <div class="flex">
          <svg v-for="i in 5" :key="i" class="w-3.5 h-3.5"
            :class="i <= Math.round(product.reviews_avg_rating ?? 0) ? 'text-yellow-400 fill-yellow-400' : 'text-gray-200 fill-gray-200'"
            viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
          </svg>
        </div>
        <span class="text-xs text-gray-500">({{ product.reviews_count }})</span>
      </div>
      <p class="text-sm text-gray-500 mt-1 line-clamp-2">{{ product.short_description }}</p>
      <div class="flex items-center justify-between mt-4">
        <div>
          <span class="text-xl font-bold text-gray-900">{{ formatPrice(product.price) }}</span>
          <span v-if="product.compare_price" class="ml-2 text-sm text-gray-400 line-through">
            {{ formatPrice(product.compare_price) }}
          </span>
        </div>
        <button @click="addToCart(product.id)" :disabled="!product.in_stock"
          class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 disabled:bg-gray-300 disabled:cursor-not-allowed transition-colors cursor-pointer">
          {{ product.in_stock ? t('card.add_to_cart') : t('card.out_of_stock') }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import { useI18n } from '@/composables/useI18n'
import { useCartModal } from '@/composables/useCartModal'
import { useWishlist } from '@/composables/useWishlist'

const { t } = useI18n()
const { openModal } = useCartModal()
const route = window.route
const page = usePage()

const props = defineProps({ product: Object })
const { isWishlisted, loading, toggle } = useWishlist(props.product.id)

const isOnSale = computed(() =>
  props.product.compare_price && parseFloat(props.product.compare_price) > parseFloat(props.product.price)
)

const isLowStock = computed(() =>
  props.product.in_stock &&
  props.product.stock <= (page.props.low_stock_threshold ?? 5)
)

const isNew = computed(() => {
  if (!props.product.created_at) { return false }
  return new Date(props.product.created_at) > new Date(Date.now() - 30 * 24 * 60 * 60 * 1000)
})

function formatPrice(price) {
  return new Intl.NumberFormat('el-GR', {
    style: 'currency',
    currency: page.props.currency ?? 'EUR',
  }).format(price)
}

function addToCart(productId) {
  router.post(route('cart.add'), { product_id: productId, quantity: 1 }, {
    preserveScroll: true,
    onSuccess: () => openModal(props.product.name),
  })
}
</script>
