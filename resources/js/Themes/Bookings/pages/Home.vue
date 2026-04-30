<template>
  <Layout>
    <Head title="Home" />

    <!-- Hero -->
    <section class="relative bg-gradient-to-br from-amber-600 to-orange-700 text-white py-28 overflow-hidden">
      <div v-if="$page.props.hero_image_url" class="absolute inset-0 bg-cover bg-center"
        :style="{ backgroundImage: `url(${$page.props.hero_image_url})` }">
        <div class="absolute inset-0 bg-amber-900/60"></div>
      </div>
      <div v-else class="absolute inset-0 opacity-10 bg-[url('/images/pattern.svg')]"></div>
      <div class="relative max-w-7xl mx-auto px-4 text-center">
        <h1 class="text-5xl font-bold mb-6 leading-tight"
          v-html="($page.props.hero_title || t('home.hero_title_bookings')).replace('\n', '<br />')"></h1>
        <p class="text-xl text-amber-100 mb-8 max-w-2xl mx-auto">
          {{ $page.props.hero_subtitle || t('home.hero_subtitle_bookings') }}
        </p>
        <Link :href="route('shop')"
          class="bg-white text-amber-700 font-bold px-8 py-3 rounded-full hover:bg-amber-50 transition-colors inline-block">
          {{ t('home.hero_cta_bookings') }}
        </Link>
      </div>
    </section>

    <!-- Featured Stays -->
    <section class="max-w-7xl mx-auto px-4 py-16">
      <h2 class="text-3xl font-bold text-gray-900 mb-2">{{ t('home.featured_bookings_title') }}</h2>
      <p class="text-gray-500 mb-8">{{ t('home.featured_subtitle') }}</p>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <ActivityCard
          v-for="accommodation in featuredAccommodations"
          :key="accommodation.id"
          :activity="accommodation"
        />
      </div>
    </section>

    <!-- Categories -->
    <section class="bg-white py-16">
      <div class="max-w-7xl mx-auto px-4">
        <h2 class="text-3xl font-bold text-gray-900 mb-8">{{ t('home.categories_title') }}</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
          <Link
            v-for="category in categories"
            :key="category.id"
            :href="route('shop') + '?category=' + category.slug"
            class="bg-amber-50 rounded-xl p-6 text-center hover:bg-amber-100 transition-all border border-transparent hover:border-amber-200"
          >
            <div class="text-lg font-semibold text-gray-800">{{ category.name }}</div>
            <div class="text-sm text-gray-500 mt-1">{{ category.products_count }} {{ t('home.listings_count') }}</div>
          </Link>
        </div>
      </div>
    </section>

    <section class="max-w-7xl mx-auto px-4 pb-16">
      <RecentlyViewed accent-color="text-amber-600" />
    </section>
  </Layout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3'
import Layout from '../Layout.vue'
import ActivityCard from '../components/ActivityCard.vue'
import RecentlyViewed from '@/components/RecentlyViewed.vue'
import { useI18n } from '@/composables/useI18n'

const { t } = useI18n()

defineProps({
  featuredAccommodations: Array,
  categories: Array,
})
</script>
