<template>
  <div class="group bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition-all border border-slate-100">
    <Link :href="route('product.show', activity.slug)">
      <div class="relative overflow-hidden">
        <img
          :src="activity.image_url ?? '/images/product-placeholder.svg'"
          :alt="activity.name"
          class="w-full h-52 object-cover group-hover:scale-105 transition-transform duration-300"
        />
        <!-- Price per day badge -->
        <div class="absolute top-3 left-3 bg-white/90 backdrop-blur-sm rounded-lg px-3 py-1.5 text-sm font-bold text-gray-800">
          {{ formatPrice(activity.price_per_day ?? activity.price) }}<span class="text-xs font-normal text-gray-500"> {{ t('activity.per_day') }}</span>
        </div>
        <span v-if="isNew" class="absolute bottom-3 left-3 bg-slate-700 text-white text-xs font-bold px-2 py-0.5 rounded-full">{{ t('card.new') }}</span>
        <div class="absolute top-3 right-3 flex items-center gap-2">
          <button @click.prevent="toggle" :disabled="loading" aria-label="Toggle wishlist"
            class="w-8 h-8 rounded-full bg-white/90 backdrop-blur-sm flex items-center justify-center shadow hover:scale-110 transition-transform cursor-pointer">
            <svg class="w-4 h-4 transition-colors" :class="isWishlisted ? 'text-red-500 fill-red-500' : 'text-gray-400'" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
            </svg>
          </button>
          <span class="bg-slate-800 text-white text-xs font-semibold px-2.5 py-1 rounded-full">{{ vehicleType }}</span>
        </div>
      </div>
    </Link>
    <div class="p-5">
      <div v-if="activity.category" class="text-xs text-slate-600 font-semibold uppercase tracking-wide mb-1">
        {{ activity.category.name }}
      </div>
      <Link :href="route('product.show', activity.slug)">
        <h3 class="font-bold text-gray-900 hover:text-slate-700 transition-colors text-lg leading-tight mb-1">
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

      <div class="flex items-center gap-4 text-sm text-gray-500 mb-4">
        <span v-if="activity.transmission" class="flex items-center gap-1 capitalize">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
          </svg>
          {{ activity.transmission }}
        </span>
        <span v-if="activity.seats" class="flex items-center gap-1">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0" />
          </svg>
          {{ activity.seats }} seats
        </span>
        <span v-if="activity.pickup_location" class="flex items-center gap-1">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
          </svg>
          {{ activity.pickup_location }}
        </span>
      </div>

      <SaleCountdown v-if="activity.is_on_sale && activity.sale_ends_at" :ends-at="activity.sale_ends_at" class="mb-3" />
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
import { useWishlist } from '@/composables/useWishlist'
import SaleCountdown from '@/components/SaleCountdown.vue'

const props = defineProps({ activity: Object })
const page = usePage()
const { t } = useI18n()
const route = window.route
const { isWishlisted, loading, toggle } = useWishlist(props.activity.id, 'vehicle')

const isNew = computed(() => {
  if (!props.activity.created_at) { return false }
  return new Date(props.activity.created_at) > new Date(Date.now() - 30 * 24 * 60 * 60 * 1000)
})

const vehicleType = computed(() =>
  props.activity.vehicle_type ?? 'Vehicle'
)

function formatPrice(price) {
  return new Intl.NumberFormat('el-GR', {
    style: 'currency',
    currency: page.props.currency ?? 'EUR',
  }).format(price)
}
</script>
