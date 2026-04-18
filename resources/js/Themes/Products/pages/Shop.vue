<template>
  <Layout>
    <Head title="Shop" />
    <div class="max-w-7xl mx-auto px-4 py-10">
      <div class="flex flex-col md:flex-row gap-8">

        <!-- Sidebar Filters -->
        <aside class="w-full md:w-64 shrink-0">
          <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
            <h3 class="font-semibold text-gray-900 mb-4">Categories</h3>
            <ul class="space-y-2">
              <li>
                <Link :href="route('shop')"
                  class="text-sm hover:text-indigo-600 transition-colors"
                  :class="!activeCategory ? 'text-indigo-600 font-medium' : 'text-gray-600'">
                  All Products
                </Link>
              </li>
              <li v-for="cat in categories" :key="cat.id">
                <Link :href="route('shop') + '?category=' + cat.slug"
                  class="text-sm hover:text-indigo-600 transition-colors"
                  :class="activeCategory === cat.slug ? 'text-indigo-600 font-medium' : 'text-gray-600'">
                  {{ cat.name }}
                </Link>
              </li>
            </ul>

            <div class="mt-6 pt-6 border-t border-gray-100">
              <h3 class="font-semibold text-gray-900 mb-4">Price Range</h3>
              <div class="flex gap-2">
                <input type="number" placeholder="Min" v-model="filters.min_price"
                  class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm" />
                <input type="number" placeholder="Max" v-model="filters.max_price"
                  class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm" />
              </div>
              <button @click="applyFilters"
                class="mt-3 w-full bg-indigo-600 text-white rounded-lg py-2 text-sm font-medium hover:bg-indigo-700 transition-colors">
                Apply
              </button>
            </div>
          </div>
        </aside>

        <!-- Product Grid -->
        <div class="flex-1">
          <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-900">
              {{ activeCategory ? currentCategoryName : 'All Products' }}
            </h1>
            <span class="text-sm text-gray-500">{{ products.total }} products</span>
          </div>

          <div v-if="products.data.length" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <ProductCard v-for="product in products.data" :key="product.id" :product="product" />
          </div>

          <div v-else class="text-center py-16 text-gray-500">
            <p class="text-lg">No products found.</p>
          </div>

          <!-- Pagination -->
          <div v-if="products.last_page > 1" class="mt-10 flex justify-center gap-2">
            <Link
              v-for="link in products.links"
              :key="link.label"
              :href="link.url ?? '#'"
              :class="['px-3 py-2 rounded-lg text-sm border', link.active ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-600 border-gray-200 hover:border-indigo-300']"
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
import { ref, computed } from 'vue'
import Layout from '../Layout.vue'
import ProductCard from '../components/ProductCard.vue'

const props = defineProps({
  products: Object,
  categories: Array,
  activeCategory: String,
  filters: Object,
})

const filters = ref({
  min_price: props.filters?.min_price ?? '',
  max_price: props.filters?.max_price ?? '',
})

const currentCategoryName = computed(() =>
  props.categories?.find(c => c.slug === props.activeCategory)?.name ?? ''
)

function applyFilters() {
  router.get(route('shop'), {
    category: props.activeCategory,
    ...filters.value,
  }, { preserveState: true })
}
</script>
