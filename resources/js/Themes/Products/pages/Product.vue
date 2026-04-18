<template>
  <Layout>
    <Head :title="product.name" />
    <div class="max-w-7xl mx-auto px-4 py-10">
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">

        <!-- Gallery -->
        <div>
          <img
            :src="activeImage ?? product.images?.[0] ?? '/images/product-placeholder.png'"
            :alt="product.name"
            class="w-full rounded-2xl object-cover aspect-square"
          />
          <div v-if="product.images?.length > 1" class="flex gap-3 mt-4">
            <img
              v-for="(img, i) in product.images"
              :key="i"
              :src="img"
              @click="activeImage = img"
              class="w-20 h-20 rounded-xl object-cover cursor-pointer border-2 transition-colors"
              :class="activeImage === img ? 'border-indigo-500' : 'border-transparent'"
            />
          </div>
        </div>

        <!-- Product Info -->
        <div>
          <div v-if="product.category" class="text-sm text-indigo-600 font-medium mb-2">
            {{ product.category.name }}
          </div>
          <h1 class="text-3xl font-bold text-gray-900 mb-4">{{ product.name }}</h1>

          <div class="flex items-baseline gap-3 mb-6">
            <span class="text-3xl font-bold text-gray-900">{{ formatPrice(product.price) }}</span>
            <span v-if="product.compare_price" class="text-lg text-gray-400 line-through">
              {{ formatPrice(product.compare_price) }}
            </span>
          </div>

          <p class="text-gray-600 leading-relaxed mb-6">{{ product.description }}</p>

          <div class="flex items-center gap-4">
            <div class="flex items-center border border-gray-200 rounded-lg overflow-hidden">
              <button @click="qty = Math.max(1, qty - 1)" class="px-4 py-3 hover:bg-gray-50 transition-colors">−</button>
              <span class="px-4 py-3 border-x border-gray-200 min-w-[3rem] text-center">{{ qty }}</span>
              <button @click="qty++" class="px-4 py-3 hover:bg-gray-50 transition-colors">+</button>
            </div>
            <button
              @click="addToCart"
              :disabled="!product.in_stock"
              class="flex-1 bg-indigo-600 text-white py-3 rounded-lg font-semibold hover:bg-indigo-700 disabled:bg-gray-300 disabled:cursor-not-allowed transition-colors"
            >
              {{ product.in_stock ? 'Add to Cart' : 'Out of Stock' }}
            </button>
          </div>

          <div v-if="product.sku" class="mt-6 text-sm text-gray-500">SKU: {{ product.sku }}</div>
          <div v-if="product.tags?.length" class="flex flex-wrap gap-2 mt-4">
            <span v-for="tag in product.tags" :key="tag.id"
              class="text-xs bg-gray-100 text-gray-600 px-3 py-1 rounded-full">
              {{ tag.name.en ?? tag.name }}
            </span>
          </div>
        </div>
      </div>

      <!-- Reviews -->
      <div class="mt-16 grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Existing reviews -->
        <div>
          <h2 class="text-xl font-bold text-gray-900 mb-6">
            Reviews <span v-if="approvedReviews.length" class="text-base font-normal text-gray-400">({{ approvedReviews.length }})</span>
          </h2>
          <div v-if="approvedReviews.length" class="space-y-4">
            <div v-for="review in approvedReviews" :key="review.id"
              class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
              <div class="flex items-center justify-between mb-2">
                <span class="font-medium text-gray-900 text-sm">{{ review.user?.name }}</span>
                <span class="text-yellow-400 text-sm">{{ '★'.repeat(review.rating) }}<span class="text-gray-200">{{ '★'.repeat(5 - review.rating) }}</span></span>
              </div>
              <p v-if="review.title" class="font-semibold text-gray-800 text-sm mb-1">{{ review.title }}</p>
              <p class="text-gray-600 text-sm leading-relaxed">{{ review.body }}</p>
            </div>
          </div>
          <p v-else class="text-gray-400 text-sm">No reviews yet. Be the first!</p>
        </div>

        <!-- Review form -->
        <ReviewForm :product-id="product.id" />
      </div>
    </div>
  </Layout>
</template>

<script setup>
import { Head, router, usePage } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import Layout from '../Layout.vue'
import ReviewForm from '../components/ReviewForm.vue'

const props = defineProps({ product: Object })
const approvedReviews = computed(() => props.product.reviews?.filter(r => r.status === 'approved') ?? [])
const page = usePage()
const qty = ref(1)
const activeImage = ref(props.product.images?.[0] ?? null)

function formatPrice(price) {
  return new Intl.NumberFormat('el-GR', {
    style: 'currency',
    currency: page.props.currency ?? 'EUR',
  }).format(price)
}

function addToCart() {
  router.post(route('cart.add'), { product_id: props.product.id, quantity: qty.value })
}
</script>
