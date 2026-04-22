<template>
  <Layout>
    <Head title="Home" />

    <!-- Hero -->
    <section class="relative bg-gradient-to-br from-slate-800 to-slate-900 text-white py-28 overflow-hidden">
      <div class="absolute inset-0 opacity-5 bg-[url('/images/pattern.svg')]"></div>
      <div class="relative max-w-7xl mx-auto px-4 text-center">
        <h1 class="text-5xl font-bold mb-6 leading-tight" v-html="t('home.hero_title_cars').replace('\n', '<br />')"></h1>
        <p class="text-xl text-slate-300 mb-8 max-w-2xl mx-auto">
          {{ t('home.hero_subtitle_cars') }}
        </p>
        <Link :href="route('shop')"
          class="bg-white text-slate-800 font-bold px-8 py-3 rounded-full hover:bg-slate-100 transition-colors inline-block">
          {{ t('home.hero_cta_cars') }}
        </Link>
      </div>
    </section>

    <!-- Featured Vehicles -->
    <section class="max-w-7xl mx-auto px-4 py-16">
      <h2 class="text-3xl font-bold text-gray-900 mb-2">{{ t('home.featured_cars_title') }}</h2>
      <p class="text-gray-500 mb-8">{{ t('home.featured_subtitle') }}</p>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <ActivityCard
          v-for="activity in featuredActivities"
          :key="activity.id"
          :activity="activity"
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
            class="bg-slate-50 rounded-xl p-6 text-center hover:bg-slate-100 transition-all border border-transparent hover:border-slate-200"
          >
            <div class="text-lg font-semibold text-gray-800">{{ category.name }}</div>
            <div class="text-sm text-gray-500 mt-1">{{ category.products_count }} {{ t('home.listings_count') }}</div>
          </Link>
        </div>
      </div>
    </section>
  </Layout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3'
import Layout from '../Layout.vue'
import ActivityCard from '../components/ActivityCard.vue'
import { useI18n } from '@/composables/useI18n'

const { t } = useI18n()

defineProps({
  featuredActivities: Array,
  categories: Array,
})
</script>
