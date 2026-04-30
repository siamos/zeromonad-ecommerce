<template>
  <Layout>
    <Head title="Home" />

    <!-- Hero -->
    <section class="relative bg-gradient-to-br from-indigo-600 to-purple-700 text-white py-24 overflow-hidden">
      <div v-if="$page.props.hero_image_url" class="absolute inset-0 bg-cover bg-center"
        :style="{ backgroundImage: `url(${$page.props.hero_image_url})` }">
        <div class="absolute inset-0 bg-indigo-900/60"></div>
      </div>
      <div class="relative max-w-7xl mx-auto px-4 text-center">
        <h1 class="text-5xl font-bold mb-6"
          v-html="($page.props.hero_title || t('home.hero_title_products')).replace('\n', '<br />')"></h1>
        <p class="text-xl text-indigo-100 mb-8 max-w-2xl mx-auto">
          {{ $page.props.hero_subtitle || t('home.hero_subtitle_products') }}
        </p>
        <Link :href="route('shop')"
          class="bg-white text-indigo-600 font-semibold px-8 py-3 rounded-full hover:bg-indigo-50 transition-colors inline-block">
          {{ t('home.hero_cta_products') }}
        </Link>
      </div>
    </section>

    <!-- Featured Products -->
    <section class="max-w-7xl mx-auto px-4 py-16">
      <h2 class="text-3xl font-bold text-gray-900 mb-8">{{ t('home.featured_products_title') }}</h2>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <ProductCard v-for="product in featuredProducts" :key="product.id" :product="product" />
      </div>
      <div class="text-center mt-10">
        <Link :href="route('shop')" class="text-indigo-600 font-semibold hover:underline">
          {{ t('home.view_all_products') }}
        </Link>
      </div>
    </section>

    <!-- Categories -->
    <section class="bg-white py-16">
      <div class="max-w-7xl mx-auto px-4">
        <h2 class="text-3xl font-bold text-gray-900 mb-8">{{ t('home.shop_by_category') }}</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
          <Link v-for="category in categories" :key="category.id"
            :href="route('shop') + '?category=' + category.slug"
            class="bg-gray-50 rounded-xl p-6 text-center hover:bg-indigo-50 hover:border-indigo-200 border border-transparent transition-all">
            <div class="text-lg font-semibold text-gray-800">{{ category.name }}</div>
            <div class="text-sm text-gray-500 mt-1">{{ category.products_count }} {{ t('home.products_count') }}</div>
          </Link>
        </div>
      </div>
    </section>

    <section class="max-w-7xl mx-auto px-4 pb-16">
      <RecentlyViewed accent-color="text-indigo-600" />
    </section>
  </Layout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3'
import Layout from '../Layout.vue'
import ProductCard from '../components/ProductCard.vue'
import RecentlyViewed from '@/components/RecentlyViewed.vue'
import { useI18n } from '@/composables/useI18n'

const { t } = useI18n()
const route = window.route

defineProps({
  featuredProducts: Array,
  categories: Array,
})
</script>
