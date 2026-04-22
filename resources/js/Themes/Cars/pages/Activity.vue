<template>
  <Layout>
    <Head :title="activity.name" />

    <div class="max-w-7xl mx-auto px-4 py-10">
      <!-- Breadcrumb -->
      <nav class="text-sm text-gray-500 mb-6 flex items-center gap-1">
        <Link :href="route('shop')" class="hover:text-slate-700">{{ t('activity.listings') }}</Link>
        <span>/</span>
        <span v-if="activity.category">
          <Link :href="route('shop') + '?category=' + activity.category.slug" class="hover:text-slate-700">
            {{ activity.category.name }}
          </Link>
          <span class="mx-1">/</span>
        </span>
        <span class="text-gray-800 font-medium">{{ activity.name }}</span>
      </nav>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
        <!-- Left: image + details -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Image -->
          <div class="relative rounded-2xl overflow-hidden">
            <img
              :src="activity.image_url ?? '/images/product-placeholder.svg'"
              :alt="activity.name"
              class="w-full aspect-video object-cover"
            />
          </div>

          <!-- Category + title -->
          <div>
            <div v-if="activity.category" class="text-xs text-slate-600 font-semibold uppercase tracking-wide mb-2">
              {{ activity.category.name }}
            </div>
            <h1 class="text-3xl font-bold text-gray-900 mb-3">{{ activity.name }}</h1>
            <p class="text-gray-600 leading-relaxed">{{ activity.short_description }}</p>
          </div>

          <!-- Meta details: vehicle specs -->
          <div v-if="activity.activity_detail" class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div v-if="activity.activity_detail.extra_attributes?.vehicle_type" class="bg-slate-50 rounded-xl p-4 text-center">
              <svg class="w-5 h-5 text-slate-700 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
              </svg>
              <div class="text-xs text-gray-500">{{ t('activity.vehicle_type') }}</div>
              <div class="text-sm font-semibold text-gray-800 mt-0.5">{{ activity.activity_detail.extra_attributes.vehicle_type }}</div>
            </div>

            <div v-if="activity.activity_detail.extra_attributes?.transmission" class="bg-slate-50 rounded-xl p-4 text-center">
              <svg class="w-5 h-5 text-slate-700 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
              </svg>
              <div class="text-xs text-gray-500">{{ t('activity.transmission') }}</div>
              <div class="text-sm font-semibold text-gray-800 mt-0.5">{{ activity.activity_detail.extra_attributes.transmission }}</div>
            </div>

            <div v-if="activity.activity_detail.extra_attributes?.seats" class="bg-slate-50 rounded-xl p-4 text-center">
              <svg class="w-5 h-5 text-slate-700 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0" />
              </svg>
              <div class="text-xs text-gray-500">{{ t('activity.seats') }}</div>
              <div class="text-sm font-semibold text-gray-800 mt-0.5">{{ activity.activity_detail.extra_attributes.seats }}</div>
            </div>

            <div v-if="activity.activity_detail.location" class="bg-slate-50 rounded-xl p-4 text-center">
              <svg class="w-5 h-5 text-slate-700 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
              </svg>
              <div class="text-xs text-gray-500">{{ t('activity.location') }}</div>
              <div class="text-sm font-semibold text-gray-800 mt-0.5">{{ activity.activity_detail.location }}</div>
            </div>
          </div>

          <!-- Full description -->
          <div v-if="activity.description" class="prose prose-slate max-w-none" v-html="activity.description" />

          <!-- Tags -->
          <div v-if="activity.tags?.length" class="flex flex-wrap gap-2">
            <span
              v-for="tag in activity.tags"
              :key="tag.id"
              class="bg-slate-100 text-gray-600 text-xs px-3 py-1 rounded-full"
            >
              {{ tag.name }}
            </span>
          </div>
        </div>

        <!-- Right: rental form -->
        <div class="lg:col-span-1">
          <div class="sticky top-6">
            <div class="mb-4 text-3xl font-bold text-gray-900">
              {{ formatPrice(activity.price) }}<span class="text-base font-normal text-gray-500"> {{ t('activity.per_day') }}</span>
            </div>
            <BookingForm :activity="activity" />
          </div>
        </div>
      </div>

      <!-- Reviews -->
      <div class="mt-16 grid grid-cols-1 lg:grid-cols-2 gap-8">
        <div>
          <h2 class="text-xl font-bold text-gray-900 mb-6">
            {{ t('activity.reviews') }} <span v-if="approvedReviews.length" class="text-base font-normal text-gray-400">({{ approvedReviews.length }})</span>
          </h2>
          <div v-if="approvedReviews.length" class="space-y-4">
            <div v-for="review in approvedReviews" :key="review.id"
              class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
              <div class="flex items-center justify-between mb-2">
                <span class="font-medium text-gray-900 text-sm">{{ review.user?.name }}</span>
                <span class="text-yellow-400 text-sm">{{ '★'.repeat(review.rating) }}<span class="text-gray-200">{{ '★'.repeat(5 - review.rating) }}</span></span>
              </div>
              <p v-if="review.title" class="font-semibold text-gray-800 text-sm mb-1">{{ review.title }}</p>
              <p class="text-gray-600 text-sm leading-relaxed">{{ review.body }}</p>
            </div>
          </div>
          <p v-else class="text-gray-400 text-sm">{{ t('activity.no_reviews') }}</p>
        </div>

        <ReviewForm :product-id="activity.id" />
      </div>
    </div>
  </Layout>
</template>

<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import Layout from '../Layout.vue'
import BookingForm from '../components/BookingForm.vue'
import ReviewForm from '../components/ReviewForm.vue'
import { useI18n } from '@/composables/useI18n'

const { t } = useI18n()
const route = window.route
const props = defineProps({ activity: Object })
const approvedReviews = computed(() => props.activity.reviews?.filter(r => r.status === 'approved') ?? [])
const page = usePage()

function formatPrice(price) {
  return new Intl.NumberFormat('el-GR', {
    style: 'currency',
    currency: page.props.currency ?? 'EUR',
  }).format(price)
}
</script>
