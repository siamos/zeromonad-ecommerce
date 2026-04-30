<template>
  <Layout>
    <Head title="Browse Stays" />

    <div class="max-w-7xl mx-auto px-4 py-10">
      <div class="flex flex-col md:flex-row gap-8">

        <!-- Filters -->
        <aside class="w-full md:w-64 shrink-0">
          <div class="bg-white rounded-2xl border border-stone-100 shadow-sm p-6 space-y-6">
            <h2 class="font-bold text-gray-900">{{ t('shop.filter_listings') }}</h2>

            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">{{ t('shop.category') }}</label>
              <div class="space-y-1">
                <label class="flex items-center gap-2 text-sm cursor-pointer">
                  <input type="radio" v-model="filters.category" value="" class="text-amber-600" />
                  <span class="text-gray-600">{{ t('shop.all_categories') }}</span>
                </label>
                <label v-for="cat in categories" :key="cat.id" class="flex items-center gap-2 text-sm cursor-pointer">
                  <input type="radio" v-model="filters.category" :value="cat.slug" class="text-amber-600" />
                  <span class="text-gray-600">{{ cat.name }}</span>
                </label>
              </div>
            </div>

            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">{{ t('booking.check_in') }}</label>
              <input
                type="date"
                v-model="filters.date"
                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500"
              />
            </div>

            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">{{ t('shop.max_price') }}</label>
              <input
                type="range"
                v-model="filters.max_price"
                min="0"
                max="1000"
                step="10"
                class="w-full accent-amber-600"
              />
              <div class="flex justify-between text-xs text-gray-500 mt-1">
                <span>€0</span>
                <span>€{{ filters.max_price }}</span>
              </div>
            </div>

            <button
              @click="applyFilters"
              class="w-full bg-amber-600 text-white py-2 rounded-lg text-sm font-semibold hover:bg-amber-700 transition-colors cursor-pointer"
            >
              {{ t('shop.apply_filters') }}
            </button>

            <button
              v-if="hasActiveFilters"
              @click="clearFilters"
              class="w-full text-sm text-gray-500 hover:text-gray-700 underline cursor-pointer"
            >
              {{ t('shop.clear_filters') }}
            </button>
          </div>
        </aside>

        <!-- Results -->
        <div class="flex-1">
          <div class="flex items-center justify-between mb-6">
            <div>
              <h1 class="text-2xl font-bold text-gray-900">{{ t('shop.all_listings') }}</h1>
              <p class="text-sm text-gray-500 mt-1">{{ accommodations.total }} {{ t('shop.listings_found') }}</p>
            </div>
            <select
              v-model="filters.sort"
              @change="applyFilters"
              class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500"
            >
              <option value="">{{ t('shop.sort_featured') }}</option>
              <option value="price_asc">{{ t('shop.sort_price_asc') }}</option>
              <option value="price_desc">{{ t('shop.sort_price_desc') }}</option>
              <option value="date_asc">{{ t('shop.sort_date_asc') }}</option>
              <option value="name_asc">{{ t('shop.sort_name_asc') }}</option>
            </select>
          </div>

          <!-- Active filter chips -->
          <div v-if="hasActiveFilters" class="flex flex-wrap items-center gap-2 mb-4">
            <button v-if="filters.category" @click="removeFilter('category')"
              class="inline-flex items-center gap-1 bg-amber-50 text-amber-700 text-xs font-medium px-3 py-1 rounded-full hover:bg-amber-100 transition-colors">
              {{ categoryName }}
              <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
            <button v-if="filters.sort" @click="removeFilter('sort')"
              class="inline-flex items-center gap-1 bg-amber-50 text-amber-700 text-xs font-medium px-3 py-1 rounded-full hover:bg-amber-100 transition-colors">
              {{ sortLabel }}
              <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
            <button v-if="filters.date" @click="removeFilter('date')"
              class="inline-flex items-center gap-1 bg-amber-50 text-amber-700 text-xs font-medium px-3 py-1 rounded-full hover:bg-amber-100 transition-colors">
              {{ filters.date }}
              <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
            <button v-if="filters.max_price < 1000" @click="removeFilter('max_price')"
              class="inline-flex items-center gap-1 bg-amber-50 text-amber-700 text-xs font-medium px-3 py-1 rounded-full hover:bg-amber-100 transition-colors">
              Max €{{ filters.max_price }}
              <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
            <button @click="clearFilters" class="text-xs text-gray-400 hover:text-gray-600 underline ml-1">
              {{ t('shop.clear_filters') }}
            </button>
          </div>

          <div v-if="accommodations.data.length" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <ActivityCard
              v-for="accommodation in accommodations.data"
              :key="accommodation.id"
              :activity="accommodation"
            />
          </div>

          <div v-else class="text-center py-20 text-gray-400">
            <svg class="w-12 h-12 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            <p class="font-medium">{{ t('shop.no_listings') }}</p>
            <p class="text-sm mt-1">{{ t('shop.try_filters') }}</p>
          </div>

          <!-- Pagination -->
          <div v-if="accommodations.last_page > 1" class="mt-10 flex justify-center gap-2">
            <a
              v-for="link in accommodations.links"
              :key="link.label"
              :href="link.url ?? '#'"
              :class="['px-3 py-2 rounded-lg text-sm border', link.active
                ? 'bg-amber-600 text-white border-amber-600'
                : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50']"
              v-html="link.label"
            />
          </div>
        </div>
      </div>

      <!-- Bundles Section -->
      <div v-if="bundles && bundles.length" class="mt-16">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Bundle Deals</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div v-for="bundle in bundles" :key="bundle.id"
            class="bg-white rounded-xl border border-amber-100 shadow-sm p-6 flex flex-col gap-4">
            <div class="flex items-start justify-between gap-4">
              <div>
                <h3 class="font-semibold text-gray-900 text-lg">{{ bundle.name }}</h3>
                <p v-if="bundle.description" class="text-sm text-gray-500 mt-1">{{ bundle.description }}</p>
              </div>
              <span class="text-xl font-bold text-amber-600 whitespace-nowrap">€{{ bundle.price }}</span>
            </div>
            <ul class="space-y-1">
              <li v-for="item in bundle.items" :key="item.id"
                class="flex items-center gap-2 text-sm text-gray-600">
                <svg class="w-4 h-4 text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                class="w-full bg-amber-600 text-white rounded-lg py-2.5 text-sm font-semibold hover:bg-amber-700 transition-colors cursor-pointer">
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
import { Head, Link, router } from '@inertiajs/vue3'
import { reactive, computed } from 'vue'
import Layout from '../Layout.vue'
import ActivityCard from '../components/ActivityCard.vue'
import { useI18n } from '@/composables/useI18n'

const { t } = useI18n()
const route = window.route

const props = defineProps({
  accommodations: Object,
  categories: Array,
  filters: Object,
  bundles: { type: Array, default: () => [] },
})

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? ''

const filters = reactive({
  category:  props.filters?.category  ?? '',
  date:      props.filters?.date      ?? '',
  max_price: props.filters?.max_price ?? 1000,
  sort:      props.filters?.sort      ?? '',
})

const hasActiveFilters = computed(() =>
  filters.category || filters.date || filters.max_price < 1000 || filters.sort
)

const categoryName = computed(() =>
  props.categories?.find(c => c.slug === filters.category)?.name ?? ''
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
  Object.assign(filters, { category: '', date: '', max_price: 1000, sort: '' })
  applyFilters()
}

function removeFilter(key) {
  if (key === 'max_price') { filters.max_price = 1000 } else { filters[key] = '' }
  applyFilters()
}
</script>
