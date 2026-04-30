<template>
  <Layout>
    <Head title="Shop" />
    <div class="max-w-7xl mx-auto px-4 py-10">
      <div class="flex flex-col md:flex-row gap-8">

        <!-- Sidebar Filters -->
        <aside class="w-full md:w-64 shrink-0">
          <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100 space-y-6">
            <h3 class="font-semibold text-gray-900">{{ t('shop.categories') }}</h3>
            <div class="space-y-1">
              <label class="flex items-center gap-2 text-sm cursor-pointer">
                <input type="radio" v-model="filters.category" value="" class="text-indigo-600" @change="applyFilters" />
                <span class="text-gray-600">{{ t('shop.all_products') }}</span>
              </label>
              <label v-for="cat in categories" :key="cat.id" class="flex items-center gap-2 text-sm cursor-pointer">
                <input type="radio" v-model="filters.category" :value="cat.slug" class="text-indigo-600" @change="applyFilters" />
                <span class="text-gray-600">{{ cat.name }}</span>
              </label>
            </div>

            <div class="pt-2 border-t border-gray-100">
              <h3 class="font-semibold text-gray-900 mb-3">{{ t('shop.price_range') }}</h3>
              <div class="flex gap-2">
                <input type="number" placeholder="Min" v-model="filters.min_price"
                  class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm" />
                <input type="number" placeholder="Max" v-model="filters.max_price"
                  class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm" />
              </div>
              <button @click="applyFilters"
                class="mt-3 w-full bg-indigo-600 text-white rounded-lg py-2 text-sm font-medium hover:bg-indigo-700 transition-colors cursor-pointer">
                {{ t('shop.apply') }}
              </button>
            </div>

            <button v-if="hasActiveFilters" @click="clearFilters"
              class="w-full text-sm text-gray-500 hover:text-gray-700 underline cursor-pointer">
              {{ t('shop.clear_filters') }}
            </button>
          </div>
        </aside>

        <!-- Product Grid -->
        <div class="flex-1">
          <div class="flex items-center justify-between mb-4">
            <div>
              <h1 class="text-2xl font-bold text-gray-900">
                {{ filters.category ? currentCategoryName : t('shop.all_products') }}
              </h1>
              <p class="text-sm text-gray-500 mt-1">{{ products.total }} {{ t('shop.products_total') }}</p>
            </div>
            <select v-model="filters.sort" @change="applyFilters"
              class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
              <option value="">{{ t('shop.sort_featured') }}</option>
              <option value="price_asc">{{ t('shop.sort_price_asc') }}</option>
              <option value="price_desc">{{ t('shop.sort_price_desc') }}</option>
              <option value="name_asc">{{ t('shop.sort_name_asc') }}</option>
            </select>
          </div>

          <!-- Active filter chips -->
          <div v-if="hasActiveFilters" class="flex flex-wrap items-center gap-2 mb-4">
            <button v-if="filters.category" @click="removeFilter('category')"
              class="inline-flex items-center gap-1 bg-indigo-50 text-indigo-700 text-xs font-medium px-3 py-1 rounded-full hover:bg-indigo-100 transition-colors">
              {{ currentCategoryName }}
              <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
            <button v-if="filters.sort" @click="removeFilter('sort')"
              class="inline-flex items-center gap-1 bg-indigo-50 text-indigo-700 text-xs font-medium px-3 py-1 rounded-full hover:bg-indigo-100 transition-colors">
              {{ sortLabel }}
              <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
            <button v-if="filters.min_price" @click="removeFilter('min_price')"
              class="inline-flex items-center gap-1 bg-indigo-50 text-indigo-700 text-xs font-medium px-3 py-1 rounded-full hover:bg-indigo-100 transition-colors">
              Min €{{ filters.min_price }}
              <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
            <button v-if="filters.max_price" @click="removeFilter('max_price')"
              class="inline-flex items-center gap-1 bg-indigo-50 text-indigo-700 text-xs font-medium px-3 py-1 rounded-full hover:bg-indigo-100 transition-colors">
              Max €{{ filters.max_price }}
              <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
            <button @click="clearFilters" class="text-xs text-gray-400 hover:text-gray-600 underline ml-1">
              {{ t('shop.clear_filters') }}
            </button>
          </div>

          <div v-if="products.data.length" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <ProductCard v-for="product in products.data" :key="product.id" :product="product" />
          </div>

          <div v-else class="text-center py-16 text-gray-500">
            <p class="text-lg">{{ t('shop.no_products') }}</p>
            <p class="text-sm mt-1">{{ t('shop.try_filters') }}</p>
          </div>

          <!-- Pagination -->
          <div v-if="products.last_page > 1" class="mt-10 flex justify-center gap-2">
            <a v-for="link in products.links" :key="link.label" :href="link.url ?? '#'"
              :class="['px-3 py-2 rounded-lg text-sm border', link.active
                ? 'bg-indigo-600 text-white border-indigo-600'
                : 'bg-white text-gray-600 border-gray-200 hover:border-indigo-300']"
              v-html="link.label" />
          </div>
        </div>
      </div>

      <!-- Bundles Section -->
      <div v-if="bundles && bundles.length" class="mt-16">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Bundle Deals</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div v-for="bundle in bundles" :key="bundle.id"
            class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 flex flex-col gap-4">
            <div class="flex items-start justify-between gap-4">
              <div>
                <h3 class="font-semibold text-gray-900 text-lg">{{ bundle.name }}</h3>
                <p v-if="bundle.description" class="text-sm text-gray-500 mt-1">{{ bundle.description }}</p>
              </div>
              <span class="text-xl font-bold text-indigo-600 whitespace-nowrap">€{{ bundle.price }}</span>
            </div>
            <ul class="space-y-1">
              <li v-for="item in bundle.items" :key="item.id"
                class="flex items-center gap-2 text-sm text-gray-600">
                <svg class="w-4 h-4 text-indigo-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span>{{ item.product?.name && typeof item.product.name === 'object' ? item.product.name.en : item.product?.name }}</span>
                <span v-if="item.quantity > 1" class="text-gray-400">×{{ item.quantity }}</span>
              </li>
            </ul>
            <form :action="route('cart.add')" method="POST" class="mt-auto">
              <input type="hidden" name="_token" :value="csrfToken" />
              <input type="hidden" name="bundle_id" :value="bundle.id" />
              <button type="submit"
                class="w-full bg-indigo-600 text-white rounded-lg py-2.5 text-sm font-semibold hover:bg-indigo-700 transition-colors cursor-pointer">
                Add Bundle to Cart
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </Layout>
</template>

<script setup>
import { Head, router } from '@inertiajs/vue3'
import { reactive, computed } from 'vue'
import Layout from '../Layout.vue'
import ProductCard from '../components/ProductCard.vue'
import { useI18n } from '@/composables/useI18n'

const { t } = useI18n()
const route = window.route

const props = defineProps({
  products: Object,
  categories: Array,
  filters: Object,
  bundles: { type: Array, default: () => [] },
})

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? ''

const filters = reactive({
  category: props.filters?.category ?? '',
  min_price: props.filters?.min_price ?? '',
  max_price: props.filters?.max_price ?? '',
  sort: props.filters?.sort ?? '',
})

const currentCategoryName = computed(() =>
  props.categories?.find(c => c.slug === filters.category)?.name ?? ''
)

const hasActiveFilters = computed(() =>
  filters.category || filters.sort || filters.min_price || filters.max_price
)

const sortLabel = computed(() => ({
  price_asc: t('shop.sort_price_asc'),
  price_desc: t('shop.sort_price_desc'),
  date_asc: t('shop.sort_date_asc'),
  name_asc: t('shop.sort_name_asc'),
})[filters.sort] ?? '')

function applyFilters() {
  router.get(route('shop'), filters, { preserveState: true, replace: true })
}

function clearFilters() {
  Object.assign(filters, { category: '', min_price: '', max_price: '', sort: '' })
  applyFilters()
}

function removeFilter(key) {
  filters[key] = ''
  applyFilters()
}
</script>
