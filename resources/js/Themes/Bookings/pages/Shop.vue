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
              <p class="text-sm text-gray-500 mt-1">{{ activities.total }} {{ t('shop.listings_found') }}</p>
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

          <div v-if="activities.data.length" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <ActivityCard
              v-for="activity in activities.data"
              :key="activity.id"
              :activity="activity"
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
          <div v-if="activities.last_page > 1" class="mt-10 flex justify-center gap-2">
            <a
              v-for="link in activities.links"
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
  activities: Object,
  categories: Array,
  filters: Object,
})

const filters = reactive({
  category:  props.filters?.category  ?? '',
  date:      props.filters?.date      ?? '',
  max_price: props.filters?.max_price ?? 1000,
  sort:      props.filters?.sort      ?? '',
})

const hasActiveFilters = computed(() =>
  filters.category || filters.date || filters.max_price < 1000 || filters.sort
)

function applyFilters() {
  router.get(route('shop'), filters, { preserveState: true, replace: true })
}

function clearFilters() {
  Object.assign(filters, { category: '', date: '', max_price: 1000, sort: '' })
  applyFilters()
}
</script>
