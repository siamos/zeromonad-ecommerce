<template>
  <div class="bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition-shadow border border-stone-100">
    <Link :href="route('product.show', activity.slug)">
      <div class="relative">
        <img
          :src="activity.image_url ?? '/images/product-placeholder.svg'"
          :alt="activity.name"
          class="w-full h-52 object-cover"
        />
        <div v-if="activity.activity_detail?.event_date"
          class="absolute top-3 left-3 bg-white/90 backdrop-blur-sm rounded-lg px-3 py-1.5 text-sm font-semibold text-gray-800">
          {{ formatDate(activity.activity_detail.event_date) }}
        </div>
        <div class="absolute top-3 right-3 bg-emerald-600 text-white text-xs font-semibold px-2.5 py-1 rounded-full">
          {{ typeLabel }}
        </div>
      </div>
    </Link>
    <div class="p-5">
      <div v-if="activity.category" class="text-xs text-emerald-600 font-semibold uppercase tracking-wide mb-1">
        {{ activity.category.name }}
      </div>
      <Link :href="route('product.show', activity.slug)">
        <h3 class="font-bold text-gray-900 hover:text-emerald-700 transition-colors text-lg leading-tight mb-2">
          {{ activity.name }}
        </h3>
      </Link>
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

const props = defineProps({ activity: Object })
const page = usePage()
const { t } = useI18n()
const route = window.route

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
