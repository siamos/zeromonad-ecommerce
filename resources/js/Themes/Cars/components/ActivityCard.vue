<template>
  <div class="bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition-shadow border border-slate-100">
    <Link :href="route('product.show', activity.slug)">
      <div class="relative">
        <img
          :src="activity.image_url ?? '/images/product-placeholder.svg'"
          :alt="activity.name"
          class="w-full h-52 object-cover"
        />
        <!-- Price per day badge -->
        <div class="absolute top-3 left-3 bg-white/90 backdrop-blur-sm rounded-lg px-3 py-1.5 text-sm font-bold text-gray-800">
          {{ formatPrice(activity.price) }}<span class="text-xs font-normal text-gray-500"> {{ t('activity.per_day') }}</span>
        </div>
        <div class="absolute top-3 right-3 bg-slate-800 text-white text-xs font-semibold px-2.5 py-1 rounded-full">
          {{ vehicleType }}
        </div>
      </div>
    </Link>
    <div class="p-5">
      <div v-if="activity.category" class="text-xs text-slate-600 font-semibold uppercase tracking-wide mb-1">
        {{ activity.category.name }}
      </div>
      <Link :href="route('product.show', activity.slug)">
        <h3 class="font-bold text-gray-900 hover:text-slate-700 transition-colors text-lg leading-tight mb-2">
          {{ activity.name }}
        </h3>
      </Link>
      <p class="text-sm text-gray-500 line-clamp-2 mb-4">{{ activity.short_description }}</p>

      <div v-if="activity.activity_detail" class="flex items-center gap-4 text-sm text-gray-500 mb-4">
        <span v-if="activity.activity_detail.extra_attributes?.transmission" class="flex items-center gap-1">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
          </svg>
          {{ activity.activity_detail.extra_attributes.transmission }}
        </span>
        <span v-if="activity.activity_detail.extra_attributes?.seats" class="flex items-center gap-1">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0" />
          </svg>
          {{ activity.activity_detail.extra_attributes.seats }} seats
        </span>
        <span v-if="activity.activity_detail.location" class="flex items-center gap-1">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
          </svg>
          {{ activity.activity_detail.location }}
        </span>
      </div>

      <div class="flex items-center justify-between">
        <span class="text-sm text-gray-500"></span>
        <Link :href="route('product.show', activity.slug)"
          class="bg-slate-800 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-slate-700 transition-colors">
          {{ t('card.rent_now') }}
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

const vehicleType = computed(() =>
  props.activity.activity_detail?.extra_attributes?.vehicle_type ?? 'Vehicle'
)

function formatPrice(price) {
  return new Intl.NumberFormat('el-GR', {
    style: 'currency',
    currency: page.props.currency ?? 'EUR',
  }).format(price)
}
</script>
