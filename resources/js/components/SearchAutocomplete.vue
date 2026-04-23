<template>
  <div class="relative hidden md:block" ref="container">
    <div class="relative">
      <input
        v-model="query"
        type="text"
        :placeholder="t('nav.search_placeholder')"
        :class="inputClass"
        @input="onInput"
        @keydown.enter.prevent="goToShop"
        @keydown.escape="close"
        @focus="(results.products.length || results.posts.length) && (open = true)"
        autocomplete="off"
      />
      <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
      </svg>
    </div>

    <!-- Dropdown -->
    <div
      v-if="open && (results.products.length || results.posts.length)"
      class="absolute top-full mt-1 left-0 w-72 bg-white border border-gray-200 rounded-xl shadow-lg z-50 overflow-hidden"
    >
      <!-- Products -->
      <div v-if="results.products.length">
        <div class="px-3 pt-3 pb-1 text-xs font-semibold text-gray-400 uppercase tracking-wide">{{ t('nav.search_products') }}</div>
        <a
          v-for="product in results.products"
          :key="product.id"
          :href="route('product.show', product.slug)"
          class="flex items-center gap-3 px-3 py-2 hover:bg-gray-50 transition-colors"
          @click="close"
        >
          <img :src="product.image_url" :alt="product.name" class="w-10 h-10 rounded-lg object-cover shrink-0" />
          <div class="min-w-0 flex-1">
            <div class="text-sm font-medium text-gray-900 truncate">{{ product.name }}</div>
            <div :class="priceClass" class="text-xs font-semibold">{{ formatPrice(product.price) }}</div>
          </div>
        </a>
      </div>

      <!-- Blog posts -->
      <div v-if="results.posts.length">
        <div class="px-3 pt-3 pb-1 text-xs font-semibold text-gray-400 uppercase tracking-wide border-t border-gray-100">{{ t('nav.search_blog') }}</div>
        <a
          v-for="post in results.posts"
          :key="post.slug"
          :href="route('blog.show', post.slug)"
          class="flex items-center gap-3 px-3 py-2 hover:bg-gray-50 transition-colors"
          @click="close"
        >
          <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center shrink-0">
            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 12h6m-6-4h6" />
            </svg>
          </div>
          <div class="min-w-0 flex-1">
            <div class="text-sm font-medium text-gray-900 truncate">{{ post.title }}</div>
          </div>
        </a>
      </div>

      <!-- See all results -->
      <div class="px-3 py-2 border-t border-gray-100">
        <button
          @click="goToShop"
          :class="seeAllClass"
          class="text-xs font-medium w-full text-left"
        >
          {{ t('nav.search_see_all') }} "{{ query }}" →
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import { useI18n } from '@/composables/useI18n'

const props = defineProps({
  inputClass: { type: String, required: true },
  priceClass: { type: String, default: 'text-gray-500' },
  seeAllClass: { type: String, default: 'text-gray-500 hover:text-gray-700' },
})

const { t } = useI18n()
const route = window.route
const page = usePage()

const query = ref('')
const open = ref(false)
const results = ref({ products: [], posts: [] })
const container = ref(null)
let debounceTimer = null

function onInput() {
  clearTimeout(debounceTimer)
  if (query.value.trim().length < 2) {
    results.value = { products: [], posts: [] }
    open.value = false
    return
  }
  debounceTimer = setTimeout(fetchResults, 300)
}

async function fetchResults() {
  try {
    const res = await fetch(`${route('search')}?q=${encodeURIComponent(query.value.trim())}`, {
      headers: { Accept: 'application/json' },
    })
    results.value = await res.json()
    open.value = results.value.products.length > 0 || results.value.posts.length > 0
  } catch {
    results.value = { products: [], posts: [] }
  }
}

function goToShop() {
  if (!query.value.trim()) return
  close()
  router.get(route('shop'), { search: query.value.trim() }, { preserveState: false })
}

function close() {
  open.value = false
}

function onClickOutside(e) {
  if (container.value && !container.value.contains(e.target)) {
    close()
  }
}

function formatPrice(price) {
  return new Intl.NumberFormat('el-GR', {
    style: 'currency',
    currency: page.props.currency ?? 'EUR',
  }).format(price)
}

onMounted(() => document.addEventListener('click', onClickOutside))
onUnmounted(() => document.removeEventListener('click', onClickOutside))
</script>
