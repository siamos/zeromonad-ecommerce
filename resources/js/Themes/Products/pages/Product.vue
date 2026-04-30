<template>
  <Layout>
    <Head :title="product.name">
      <meta name="description" :content="product.short_description ?? product.description" />
      <meta property="og:type" content="product" />
      <meta property="og:title" :content="product.name" />
      <meta property="og:description" :content="product.short_description ?? product.description" />
      <meta v-if="product.image_url" property="og:image" :content="product.image_url" />
      <script v-if="schema" type="application/ld+json" v-html="JSON.stringify(schema)" />
      <script type="application/ld+json" v-html="JSON.stringify(breadcrumbSchema)" />
    </Head>
    <div class="max-w-7xl mx-auto px-4 py-10">
      <Breadcrumb :items="breadcrumbs" />
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">

        <!-- Gallery -->
        <div>
          <div class="relative rounded-2xl overflow-hidden cursor-zoom-in group" @click="lightboxOpen = true">
            <img :src="activeImage ?? product.images?.[0] ?? '/images/product-placeholder.svg'" :alt="product.name"
              class="w-full object-cover aspect-square transition-transform duration-300 group-hover:scale-105" />
            <div class="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition-colors flex items-center justify-center">
              <svg class="w-8 h-8 text-white opacity-0 group-hover:opacity-100 transition-opacity drop-shadow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7" />
              </svg>
            </div>
          </div>
          <div v-if="product.images?.length > 1" class="flex gap-3 mt-4">
            <img v-for="(img, i) in product.images" :key="i" :src="img" @click="activeImage = img"
              class="w-20 h-20 rounded-xl object-cover cursor-pointer border-2 transition-colors"
              :class="activeImage === img ? 'border-indigo-500' : 'border-transparent'" />
          </div>
        </div>

        <!-- Lightbox -->
        <Teleport to="body">
          <Transition name="lb">
            <div v-if="lightboxOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/85 p-4" @click.self="lightboxOpen = false">
              <button @click="lightboxOpen = false" class="absolute top-4 right-4 text-white hover:text-gray-300 transition-colors cursor-pointer">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
              <img :src="activeImage ?? product.images?.[0] ?? '/images/product-placeholder.svg'" :alt="product.name"
                class="max-h-[90vh] max-w-full rounded-xl object-contain shadow-2xl" />
              <template v-if="product.images?.length > 1">
                <button @click="prevImage" class="absolute left-4 top-1/2 -translate-y-1/2 text-white hover:text-gray-300 transition-colors cursor-pointer">
                  <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                  </svg>
                </button>
                <button @click="nextImage" class="absolute right-4 top-1/2 -translate-y-1/2 text-white hover:text-gray-300 transition-colors cursor-pointer">
                  <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                  </svg>
                </button>
              </template>
            </div>
          </Transition>
        </Teleport>

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
              <button @click="qty = Math.max(1, qty - 1)" class="px-4 py-3 hover:bg-gray-50 transition-colors cursor-pointer">−</button>
              <span class="px-4 py-3 border-x border-gray-200 min-w-[3rem] text-center">{{ qty }}</span>
              <button @click="qty++" class="px-4 py-3 hover:bg-gray-50 transition-colors cursor-pointer">+</button>
            </div>
            <button @click="addToCart" :disabled="!product.in_stock"
              class="flex-1 bg-indigo-600 text-white py-3 rounded-lg font-semibold hover:bg-indigo-700 disabled:bg-gray-300 disabled:cursor-not-allowed transition-colors cursor-pointer">
              {{ product.in_stock ? t('product.add_to_cart') : t('product.out_of_stock') }}
            </button>
          </div>

          <!-- Back-in-stock alert form -->
          <div v-if="!product.in_stock" class="mt-4 p-4 bg-gray-50 rounded-xl border border-gray-100">
            <p class="text-sm text-gray-600 mb-3">{{ t('booking.notify_me_desc') }}</p>
            <form @submit.prevent="submitAlert" class="flex gap-2">
              <input type="email" v-model="alertEmail" required :placeholder="t('booking.notify_email_placeholder')"
                class="flex-1 border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
              <button type="submit" :disabled="alertSubmitting || alertSent"
                class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-indigo-700 transition-colors disabled:opacity-60 cursor-pointer whitespace-nowrap">
                {{ alertSent ? t('booking.notify_subscribed') : t('booking.notify_me') }}
              </button>
            </form>
            <!-- Waitlist -->
            <div class="mt-3 border-t border-gray-200 pt-3">
              <p class="text-xs text-gray-500 mb-2">Or join the waitlist to be notified in order:</p>
              <form v-if="!waitlistJoined" @submit.prevent="joinWaitlist" class="flex gap-2">
                <input type="email" v-model="waitlistEmail" required placeholder="your@email.com"
                  class="flex-1 border border-gray-200 rounded-lg px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                <button type="submit" :disabled="waitlistSubmitting"
                  class="bg-gray-700 text-white px-3 py-2 rounded-lg text-xs font-semibold hover:bg-gray-800 transition-colors disabled:opacity-60 cursor-pointer whitespace-nowrap">
                  Join Waitlist
                </button>
              </form>
              <p v-else class="text-xs text-indigo-600 font-medium">You're on the waitlist!</p>
            </div>
          </div>

          <div v-if="product.sku" class="mt-6 text-sm text-gray-500">{{ t('product.sku') }}: {{ product.sku }}</div>
          <div v-if="product.tags?.length" class="flex flex-wrap gap-2 mt-4">
            <span v-for="tag in product.tags" :key="tag.id"
              class="text-xs bg-gray-100 text-gray-600 px-3 py-1 rounded-full">
              {{ tag.name.en ?? tag.name }}
            </span>
          </div>

          <!-- Trust badges -->
          <div class="mt-6 flex items-center gap-6 border-t border-gray-100 pt-5">
            <div class="flex items-center gap-2 text-xs text-gray-500">
              <svg class="w-5 h-5 text-indigo-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
              </svg>
              {{ t('product.trust_secure') }}
            </div>
            <div class="flex items-center gap-2 text-xs text-gray-500">
              <svg class="w-5 h-5 text-indigo-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
              </svg>
              {{ t('product.trust_shipping') }}
            </div>
            <div class="flex items-center gap-2 text-xs text-gray-500">
              <svg class="w-5 h-5 text-indigo-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
              </svg>
              {{ t('product.trust_returns') }}
            </div>
          </div>
        </div>
      </div>

      <!-- Recommendations -->
      <div v-if="recommended?.length" class="mt-16">
        <h2 class="text-xl font-bold text-gray-900 mb-6">{{ t('product.you_may_also_like') }}</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
          <ProductCard v-for="item in recommended" :key="item.id" :product="item" />
        </div>
      </div>

      <!-- Reviews -->
      <div class="mt-16 grid grid-cols-1 lg:grid-cols-2 gap-8">
        <div>
          <h2 class="text-xl font-bold text-gray-900 mb-6">
            {{ t('product.reviews') }} <span v-if="approvedReviews.length" class="text-base font-normal text-gray-400">({{ approvedReviews.length }})</span>
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
          <p v-else class="text-gray-400 text-sm">{{ t('product.no_reviews') }}</p>
        </div>

        <ReviewForm :product-id="product.id" />
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 pb-16">
      <RecentlyViewed accent-color="text-indigo-600" />
    </div>
  </Layout>
</template>

<script setup>
import { Head, router, usePage } from '@inertiajs/vue3'
import { ref, computed, onMounted } from 'vue'
import Layout from '../Layout.vue'
import ReviewForm from '../components/ReviewForm.vue'
import ProductCard from '../components/ProductCard.vue'
import RecentlyViewed from '@/components/RecentlyViewed.vue'
import { useI18n } from '@/composables/useI18n'
import { useCartModal } from '@/composables/useCartModal'
import { useRecentlyViewed } from '@/composables/useRecentlyViewed'
import Breadcrumb from '@/components/Breadcrumb.vue'

const { t } = useI18n()
const { push: pushRecentlyViewed } = useRecentlyViewed()
const { openModal } = useCartModal()
const route = window.route
const props = defineProps({ product: Object, recommended: Array, schema: { type: Object, default: null } })
const approvedReviews = computed(() => props.product.reviews?.filter(r => r.status === 'approved') ?? [])

const breadcrumbs = computed(() => {
  const items = [
    { label: t('nav.home'), href: route('home') },
    { label: t('shop.all_products'), href: route('shop') },
  ]
  if (props.product.category) {
    items.push({ label: props.product.category.name, href: route('shop') + '?category=' + props.product.category.slug })
  }
  items.push({ label: props.product.name })
  return items
})

const breadcrumbSchema = computed(() => ({
  '@context': 'https://schema.org',
  '@type': 'BreadcrumbList',
  itemListElement: breadcrumbs.value
    .filter(b => b.href)
    .map((b, i) => ({
      '@type': 'ListItem',
      position: i + 1,
      name: b.label,
      item: b.href,
    })),
}))
const page = usePage()
const qty = ref(1)

onMounted(() => {
  pushRecentlyViewed(props.product)
})
const activeImage = ref(props.product.images?.[0] ?? null)
const lightboxOpen = ref(false)
const alertEmail = ref('')
const alertSubmitting = ref(false)
const alertSent = ref(false)

const waitlistEmail = ref('')
const waitlistSubmitting = ref(false)
const waitlistJoined = ref(false)

function prevImage() {
  const imgs = props.product.images ?? []
  const idx = imgs.indexOf(activeImage.value)
  activeImage.value = imgs[(idx - 1 + imgs.length) % imgs.length]
}

function nextImage() {
  const imgs = props.product.images ?? []
  const idx = imgs.indexOf(activeImage.value)
  activeImage.value = imgs[(idx + 1) % imgs.length]
}

function formatPrice(price) {
  return new Intl.NumberFormat('el-GR', {
    style: 'currency',
    currency: page.props.currency ?? 'EUR',
  }).format(price)
}

function addToCart() {
  router.post(route('cart.add'), { product_id: props.product.id, quantity: qty.value }, {
    preserveScroll: true,
    onSuccess: () => openModal(props.product.name),
  })
}

async function submitAlert() {
  alertSubmitting.value = true
  try {
    await fetch(route('stock-alerts.store'), {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
      },
      body: JSON.stringify({ product_id: props.product.id, email: alertEmail.value }),
    })
    alertSent.value = true
  } finally {
    alertSubmitting.value = false
  }
}

async function joinWaitlist() {
  waitlistSubmitting.value = true
  try {
    await fetch(route('waitlist.join'), {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
      },
      body: JSON.stringify({
        waitlistable_type: 'product',
        waitlistable_id: props.product.id,
        email: waitlistEmail.value,
      }),
    })
    waitlistJoined.value = true
  } finally {
    waitlistSubmitting.value = false
  }
}
</script>

<style scoped>
.lb-enter-active, .lb-leave-active { transition: opacity 0.2s ease; }
.lb-enter-from, .lb-leave-to { opacity: 0; }
</style>
