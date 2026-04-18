<template>
  <Layout>
    <Head title="Home" />

    <!-- Hero -->
    <section class="bg-gradient-to-br from-indigo-600 to-purple-700 text-white py-24">
      <div class="max-w-7xl mx-auto px-4 text-center">
        <h1 class="text-5xl font-bold mb-6">Discover Amazing Products</h1>
        <p class="text-xl text-indigo-100 mb-8 max-w-2xl mx-auto">
          Browse our curated collection of high-quality products at unbeatable prices.
        </p>
        <Link :href="route('shop')"
          class="bg-white text-indigo-600 font-semibold px-8 py-3 rounded-full hover:bg-indigo-50 transition-colors inline-block">
          Shop Now
        </Link>
      </div>
    </section>

    <!-- Featured Products -->
    <section class="max-w-7xl mx-auto px-4 py-16">
      <h2 class="text-3xl font-bold text-gray-900 mb-8">Featured Products</h2>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <ProductCard
          v-for="product in featuredProducts"
          :key="product.id"
          :product="product"
        />
      </div>
      <div class="text-center mt-10">
        <Link :href="route('shop')"
          class="text-indigo-600 font-semibold hover:underline">
          View all products →
        </Link>
      </div>
    </section>

    <!-- Categories -->
    <section class="bg-white py-16">
      <div class="max-w-7xl mx-auto px-4">
        <h2 class="text-3xl font-bold text-gray-900 mb-8">Shop by Category</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
          <Link
            v-for="category in categories"
            :key="category.id"
            :href="route('shop') + '?category=' + category.slug"
            class="bg-gray-50 rounded-xl p-6 text-center hover:bg-indigo-50 hover:border-indigo-200 border border-transparent transition-all"
          >
            <div class="text-lg font-semibold text-gray-800">{{ category.name }}</div>
            <div class="text-sm text-gray-500 mt-1">{{ category.products_count }} products</div>
          </Link>
        </div>
      </div>
    </section>
  </Layout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3'
import Layout from '../Layout.vue'
import ProductCard from '../components/ProductCard.vue'

defineProps({
  featuredProducts: Array,
  categories: Array,
})
</script>
