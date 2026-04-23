<template>
  <div class="group bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition-all border border-stone-100">
    <Link :href="route('product.show', activity.slug)">
      <div class="relative overflow-hidden">
        <img
          :src="activity.image_url ?? '/images/product-placeholder.svg'"
          :alt="activity.name"
          class="w-full h-52 object-cover group-hover:scale-105 transition-transform duration-300"
        />
        <div v-if="activity.activity_detail?.event_date"
          class="absolute top-3 left-3 bg-white/90 backdrop-blur-sm rounded-lg px-3 py-1.5 text-sm font-semibold text-gray-800">
          {{ formatDate(activity.activity_detail.event_date) }}
        </div>
        <span v-else-if="isNew" class="absolute top-3 left-3 bg-emerald-600 text-white text-xs font-bold px-2 py-0.5 rounded-full">{{ t('card.new') }}</span>
        <div class="absolute top-3 right-3 flex items-center gap-2">
          <button @click.prevent="toggle" :disabled="loading" aria-label="Toggle wishlist"
            class="w-8 h-8 rounded-full bg-white/90 backdrop-blur-sm flex items-center justify-center shadow hover:scale-110 transition-transform cursor-pointer">
            <svg class="w-4 h-4 transition-colors" :class="isWishlisted ? 'text-red-500 fill-red-500' : 'text-gray-400'" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
            </svg>
          </button>
          <span class="bg-emerald-600 text-white text-xs font-semibold px-2.5 py-1 rounded-full">{{ typeLabel }}</span>
        </div>
      </div>
    </Link>
    <div class="p-5">
      <div v-if="activity.category" class="text-xs text-emerald-600 font-semibold uppercase tracking-wide mb-1">
        {{ activity.category.name }}
      </div>
      <Link :href="route('product.show', activity.slug)">
        <h3 class="font-bold text-gray-900 hover:text-emerald-700 transition-colors text-lg leading-tight mb-1">
          {{ activity.name }}
        </h3>
      </Link>
      <div v-if="activity.reviews_count > 0" class="flex items-center gap-1 mb-2">
        <div class="flex">
          <svg v-for="i in 5" :key="i" class="w-3.5 h-3.5"
            :class="i <= Math.round(activity.reviews_avg_rating ?? 0) ? 'text-yellow-400 fill-yellow-400' : 'text-gray-200 fill-gray-200'"
            viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
          </svg>
        </div>
        <span class="text-xs text-gray-500">({{ activity.reviews_count }})</span>
      </div>
      <p class="text-sm text-gray-500 line-clamp-2 mb-4">{{ activity.short_description }}</p>

      <div v-if="activity.activity_detail" class="flex items-center gap-4 text-sm text-gray-500 mb-4">
        <span v-if="activity.activity_detail.location" class="flex items-center gap-1">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
          </svg>
          {{ activity.activity_detail.location }}
        </span>
        <span v-if="bookingType === 'accommodation' && activity.activity_detail.extra_attributes?.bedrooms" class="flex items-center gap-1">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
          </svg>
          {{ activity.activity_detail.extra_attributes.bedrooms }} bed
        </span>
        <span v-else-if="bookingType === 'vehicle' && activity.activity_detail.extra_attributes?.vehicle_type" class="flex items-center gap-1">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
          </svg>
          {{ activity.activity_detail.extra_attributes.vehicle_type }}
        </span>
        <span v-else-if="activity.activity_detail.duration_minutes" class="flex items-center gap-1">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          {{ activity.activity_detail.duration_minutes }} min
        </span>
      </div>

      <div class="flex items-center justify-between">
        <span class="text-xl font-bold text-gray-900">{{ formatPrice(activity.price) }}</span>
        <Link :href="route('product.show', activity.slug)"
          class="bg-emerald-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-emerald-700 transition-colors">
          {{ ctaLabel }}
        </Link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import { useI18n } from '@/composables/useI18n'
import { useWishlist } from '@/composables/useWishlist'

const props = defineProps({ activity: Object })
const page = usePage()
const { t } = useI18n()
const route = window.route
const { isWishlisted, loading, toggle } = useWishlist(props.activity.id)

const isNew = computed(() => {
  if (!props.activity.created_at) { return false }
  return new Date(props.activity.created_at) > new Date(Date.now() - 30 * 24 * 60 * 60 * 1000)
})

const bookingType = computed(() => props.activity.activity_detail?.booking_type ?? 'activity')

const typeLabel = computed(() => {
  const labels = {
    activity: 'Activity',
    accommodation: 'Accommodation',
    vehicle: 'Vehicle',
    tour: 'Tour',
    event: 'Event',
  }
  return labels[bookingType.value] ?? 'Activity'
})

const ctaLabel = computed(() => {
  if (bookingType.value === 'accommodation') return t('card.book_stay')
  if (bookingType.value === 'vehicle') return t('card.rent_now')
  return t('card.book_now')
})

function formatPrice(price) {
  return new Intl.NumberFormat('el-GR', {
    style: 'currency',
    currency: page.props.currency ?? 'EUR',
  }).format(price)
}

function formatDate(date) {
  return new Date(date).toLocaleDateString('el-GR', { day: 'numeric', month: 'short' })
}
</script>
