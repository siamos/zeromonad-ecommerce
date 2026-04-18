<template>
  <div class="bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition-shadow border border-stone-100">
    <Link :href="route('product.show', activity.slug)">
      <div class="relative">
        <img
          :src="activity.image_url ?? '/images/activity-placeholder.png'"
          :alt="activity.name"
          class="w-full h-52 object-cover"
        />
        <div v-if="activity.activity_detail?.event_date"
          class="absolute top-3 left-3 bg-white/90 backdrop-blur-sm rounded-lg px-3 py-1.5 text-sm font-semibold text-gray-800">
          {{ formatDate(activity.activity_detail.event_date) }}
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
        <span v-if="activity.activity_detail.duration_minutes" class="flex items-center gap-1">
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
          Book Now
        </Link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Link, usePage } from '@inertiajs/vue3'

const props = defineProps({ activity: Object })
const page = usePage()

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
