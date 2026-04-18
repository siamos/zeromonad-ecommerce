<template>
  <div class="bg-white rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-shadow border border-gray-100">
    <Link :href="route('product.show', product.slug)">
      <img
        :src="product.image_url ?? '/images/product-placeholder.png'"
        :alt="product.name"
        class="w-full h-48 object-cover"
      />
    </Link>
    <div class="p-4">
      <Link :href="route('product.show', product.slug)">
        <h3 class="font-semibold text-gray-900 hover:text-indigo-600 transition-colors line-clamp-2">
          {{ product.name }}
        </h3>
      </Link>
      <p class="text-sm text-gray-500 mt-1 line-clamp-2">{{ product.short_description }}</p>
      <div class="flex items-center justify-between mt-4">
        <div>
          <span class="text-xl font-bold text-gray-900">{{ formatPrice(product.price) }}</span>
          <span v-if="product.compare_price" class="ml-2 text-sm text-gray-400 line-through">
            {{ formatPrice(product.compare_price) }}
          </span>
        </div>
        <button
          @click="addToCart(product.id)"
          :disabled="!product.in_stock"
          class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 disabled:bg-gray-300 disabled:cursor-not-allowed transition-colors"
        >
          {{ product.in_stock ? 'Add to Cart' : 'Out of Stock' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Link, router, usePage } from '@inertiajs/vue3'

const props = defineProps({
  product: Object,
})

const page = usePage()

function formatPrice(price) {
  return new Intl.NumberFormat('el-GR', {
    style: 'currency',
    currency: page.props.currency ?? 'EUR',
  }).format(price)
}

function addToCart(productId) {
  router.post(route('cart.add'), { product_id: productId, quantity: 1 })
}
</script>
