<template>
  <Layout>
    <Head title="Activities" />

    <div class="max-w-7xl mx-auto px-4 py-10">
      <div class="flex flex-col md:flex-row gap-8">

        <!-- Filters -->
        <aside class="w-full md:w-64 shrink-0">
          <div class="bg-white rounded-2xl border border-stone-100 shadow-sm p-6 space-y-6">
            <h2 class="font-bold text-gray-900">Filter Activities</h2>

            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Category</label>
              <div class="space-y-1">
                <label class="flex items-center gap-2 text-sm cursor-pointer">
                  <input type="radio" v-model="filters.category" value="" class="text-emerald-600" />
                  <span class="text-gray-600">All Categories</span>
                </label>
                <label v-for="cat in categories" :key="cat.id" class="flex items-center gap-2 text-sm cursor-pointer">
                  <input type="radio" v-model="filters.category" :value="cat.slug" class="text-emerald-600" />
                  <span class="text-gray-600">{{ cat.name }}</span>
                </label>
              </div>
            </div>

            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Date</label>
              <input
                type="date"
                v-model="filters.date"
                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
              />
            </div>

            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Max Price</label>
              <input
                type="range"
                v-model="filters.max_price"
                min="0"
                max="1000"
                step="10"
                class="w-full accent-emerald-600"
              />
              <div class="flex justify-between text-xs text-gray-500 mt-1">
                <span>€0</span>
                <span>€{{ filters.max_price }}</span>
              </div>
            </div>

            <button
              @click="applyFilters"
              class="w-full bg-emerald-600 text-white py-2 rounded-lg text-sm font-semibold hover:bg-emerald-700 transition-colors"
            >
              Apply Filters
            </button>

            <button
              v-if="hasActiveFilters"
              @click="clearFilters"
              class="w-full text-sm text-gray-500 hover:text-gray-700 underline"
            >
              Clear all filters
            </button>
          </div>
        </aside>

        <!-- Results -->
        <div class="flex-1">
          <div class="flex items-center justify-between mb-6">
            <div>
              <h1 class="text-2xl font-bold text-gray-900">All Activities</h1>
              <p class="text-sm text-gray-500 mt-1">{{ activities.total }} activities found</p>
            </div>
            <select
              v-model="filters.sort"
              @change="applyFilters"
              class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
            >
              <option value="">Sort: Featured</option>
              <option value="price_asc">Price: Low to High</option>
              <option value="price_desc">Price: High to Low</option>
              <option value="date_asc">Date: Soonest</option>
              <option value="name_asc">Name: A–Z</option>
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
                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <p class="font-medium">No activities found</p>
            <p class="text-sm mt-1">Try adjusting your filters</p>
          </div>

          <!-- Pagination -->
          <div v-if="activities.last_page > 1" class="mt-10 flex justify-center gap-2">
            <Link
              v-for="link in activities.links"
              :key="link.label"
              :href="link.url ?? '#'"
              :class="['px-3 py-2 rounded-lg text-sm border', link.active
                ? 'bg-emerald-600 text-white border-emerald-600'
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
