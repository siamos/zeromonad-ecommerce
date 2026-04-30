<template>
  <Layout>
    <Head :title="vehicle.name">
      <meta name="description" :content="vehicle.short_description ?? vehicle.description" />
      <meta property="og:type" content="website" />
      <meta property="og:title" :content="vehicle.name" />
      <meta property="og:description" :content="vehicle.short_description ?? vehicle.description" />
      <meta v-if="vehicle.image_url" property="og:image" :content="vehicle.image_url" />
      <script v-if="schema" type="application/ld+json" v-html="JSON.stringify(schema)" />
      <script type="application/ld+json" v-html="JSON.stringify(breadcrumbSchema)" />
    </Head>

    <div class="max-w-7xl mx-auto px-4 py-10">
      <Breadcrumb :items="breadcrumbs" />

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
        <!-- Left: image + details -->
        <div class="lg:col-span-2 space-y-6">
          <div class="relative rounded-2xl overflow-hidden">
            <img
              :src="vehicle.image_url ?? '/images/product-placeholder.svg'"
              :alt="vehicle.name"
              class="w-full aspect-video object-cover"
            />
            <div class="absolute top-4 left-4 bg-white/95 backdrop-blur-sm rounded-xl px-4 py-2 shadow-sm">
              <div class="text-xs font-semibold text-slate-600 uppercase tracking-wide">{{ vehicle.vehicle_type }}</div>
            </div>
          </div>

          <div>
            <div v-if="vehicle.category" class="text-xs text-slate-600 font-semibold uppercase tracking-wide mb-2">
              {{ vehicle.category.name }}
            </div>
            <h1 class="text-3xl font-bold text-gray-900 mb-3">{{ vehicle.name }}</h1>
            <p class="text-gray-600 leading-relaxed">{{ vehicle.short_description }}</p>
          </div>

          <!-- Specs table -->
          <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div v-if="vehicle.vehicle_type" class="bg-slate-50 rounded-xl p-4 text-center">
              <svg class="w-5 h-5 text-slate-700 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
              </svg>
              <div class="text-xs text-gray-500">{{ t('activity.vehicle_type') }}</div>
              <div class="text-sm font-semibold text-gray-800 mt-0.5 capitalize">{{ vehicle.vehicle_type }}</div>
            </div>

            <div v-if="vehicle.transmission" class="bg-slate-50 rounded-xl p-4 text-center">
              <svg class="w-5 h-5 text-slate-700 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
              </svg>
              <div class="text-xs text-gray-500">{{ t('activity.transmission') }}</div>
              <div class="text-sm font-semibold text-gray-800 mt-0.5 capitalize">{{ vehicle.transmission }}</div>
            </div>

            <div v-if="vehicle.seats" class="bg-slate-50 rounded-xl p-4 text-center">
              <svg class="w-5 h-5 text-slate-700 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0" />
              </svg>
              <div class="text-xs text-gray-500">{{ t('activity.seats') }}</div>
              <div class="text-sm font-semibold text-gray-800 mt-0.5">{{ vehicle.seats }}</div>
            </div>

            <div v-if="vehicle.pickup_location" class="bg-slate-50 rounded-xl p-4 text-center">
              <svg class="w-5 h-5 text-slate-700 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
              </svg>
              <div class="text-xs text-gray-500">{{ t('activity.location') }}</div>
              <div class="text-sm font-semibold text-gray-800 mt-0.5">{{ vehicle.pickup_location }}</div>
            </div>
          </div>

          <!-- Policies -->
          <div v-if="vehicle.mileage_policy || vehicle.fuel_policy" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div v-if="vehicle.mileage_policy" class="border border-slate-100 rounded-xl p-4">
              <div class="text-xs text-gray-500 mb-1">{{ t('activity.mileage_policy') }}</div>
              <div class="text-sm font-semibold text-gray-800">{{ vehicle.mileage_policy }}</div>
            </div>
            <div v-if="vehicle.fuel_policy" class="border border-slate-100 rounded-xl p-4">
              <div class="text-xs text-gray-500 mb-1">{{ t('activity.fuel_policy') }}</div>
              <div class="text-sm font-semibold text-gray-800">{{ vehicle.fuel_policy }}</div>
            </div>
          </div>

          <!-- Availability indicator -->
          <div class="flex items-center gap-2">
            <span class="w-2 h-2 rounded-full inline-block" :class="isAvailable ? 'bg-green-500' : 'bg-red-500'"></span>
            <span class="text-sm" :class="isAvailable ? 'text-green-700' : 'text-red-700'">
              {{ isAvailable ? t('booking.available') : t('booking.not_available') }}
            </span>
          </div>

          <div v-if="vehicle.description" class="prose prose-slate max-w-none" v-html="vehicle.description" />
        </div>

        <!-- Right: rental sidebar -->
        <div class="lg:col-span-1">
          <div class="sticky top-6">
            <div class="mb-2 text-3xl font-bold text-gray-900">
              {{ formatPrice(vehicle.is_on_sale ? vehicle.sale_price : vehicle.price_per_day) }}<span class="text-base font-normal text-gray-500"> {{ t('activity.per_day') }}</span>
              <span v-if="vehicle.is_on_sale" class="ml-2 text-lg font-normal text-gray-400 line-through">{{ formatPrice(vehicle.price_per_day) }}</span>
            </div>
            <SaleCountdown v-if="vehicle.is_on_sale && vehicle.sale_ends_at" :ends-at="vehicle.sale_ends_at" class="mb-4" />
            <BookingForm
              :vehicle="vehicle"
              :is-available="isAvailable"
              :booked-ranges="bookedRanges ?? []"
              :pickup-locations="pickupLocations ?? []"
              :dropoff-locations="dropoffLocations ?? []"
            />
          </div>
        </div>
      </div>

      <!-- Reviews -->
      <div class="mt-16 grid grid-cols-1 lg:grid-cols-2 gap-8">
        <div>
          <h2 class="text-xl font-bold text-gray-900 mb-6">
            {{ t('activity.reviews') }}
            <span v-if="approvedReviews.length" class="text-base font-normal text-gray-400">({{ approvedReviews.length }})</span>
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
              <div v-if="review.image_urls?.length" class="flex gap-2 flex-wrap mt-3">
                <a v-for="(img, i) in review.image_urls" :key="i" :href="img.url" target="_blank">
                  <img :src="img.thumb" class="w-16 h-16 object-cover rounded-xl border border-gray-200 hover:opacity-90 transition" />
                </a>
              </div>
            </div>
          </div>
          <p v-else class="text-gray-400 text-sm">{{ t('activity.no_reviews') }}</p>
        </div>
        <ReviewForm :reviewable-type="'vehicle'" :reviewable-id="vehicle.id" />
      </div>

      <!-- Recommendations -->
      <div v-if="recommended?.length" class="mt-16">
        <h2 class="text-xl font-bold text-gray-900 mb-6">{{ t('activity.you_may_also_like') }}</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
          <ActivityCard v-for="item in recommended" :key="item.id" :activity="item" />
        </div>
      </div>

      <RecentlyViewed accent-color="text-slate-800" />
    </div>
  </Layout>
</template>

<script setup>
import { Head, usePage } from '@inertiajs/vue3'
import { computed, onMounted } from 'vue'
import Layout from '../Layout.vue'
import BookingForm from '../components/BookingForm.vue'
import ReviewForm from '../components/ReviewForm.vue'
import ActivityCard from '../components/ActivityCard.vue'
import RecentlyViewed from '@/components/RecentlyViewed.vue'
import { useI18n } from '@/composables/useI18n'
import { useRecentlyViewed } from '@/composables/useRecentlyViewed'
import Breadcrumb from '@/components/Breadcrumb.vue'
import SaleCountdown from '@/components/SaleCountdown.vue'

const { t } = useI18n()
const { push: pushRecentlyViewed } = useRecentlyViewed()
const route = window.route
const props = defineProps({
  vehicle: Object,
  recommended: Array,
  isAvailable: { type: Boolean, default: true },
  bookedRanges: { type: Array, default: () => [] },
  pickupLocations: { type: Array, default: () => [] },
  dropoffLocations: { type: Array, default: () => [] },
  schema: { type: Object, default: null },
})
const page = usePage()
const approvedReviews = computed(() => props.vehicle.reviews?.filter(r => r.status === 'approved') ?? [])

onMounted(() => {
  pushRecentlyViewed({ ...props.vehicle, name: `${props.vehicle.make} ${props.vehicle.model} ${props.vehicle.year}`, price: props.vehicle.price_per_day })
})

const breadcrumbs = computed(() => {
  const items = [
    { label: t('nav.home'), href: route('home') },
    { label: t('nav.browse'), href: route('shop') },
  ]
  if (props.vehicle.category) {
    items.push({ label: props.vehicle.category.name, href: route('shop') + '?category=' + props.vehicle.category.slug })
  }
  items.push({ label: props.vehicle.name })
  return items
})

const breadcrumbSchema = computed(() => ({
  '@context': 'https://schema.org',
  '@type': 'BreadcrumbList',
  itemListElement: breadcrumbs.value
    .filter(b => b.href)
    .map((b, i) => ({ '@type': 'ListItem', position: i + 1, name: b.label, item: b.href })),
}))

function formatPrice(price) {
  return new Intl.NumberFormat('el-GR', {
    style: 'currency',
    currency: page.props.currency ?? 'EUR',
  }).format(price)
}
</script>
