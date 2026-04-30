<template>
  <Layout>
    <Head :title="activity.name">
      <meta name="description" :content="activity.short_description ?? activity.description" />
      <meta property="og:type" content="website" />
      <meta property="og:title" :content="activity.name" />
      <meta property="og:description" :content="activity.short_description ?? activity.description" />
      <meta v-if="activity.image_url" property="og:image" :content="activity.image_url" />
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
              :src="activity.image_url ?? '/images/product-placeholder.svg'"
              :alt="activity.name"
              class="w-full aspect-video object-cover"
            />
          </div>

          <div>
            <div v-if="activity.category" class="text-xs text-emerald-600 font-semibold uppercase tracking-wide mb-2">
              {{ activity.category.name }}
            </div>
            <h1 class="text-3xl font-bold text-gray-900 mb-3">{{ activity.name }}</h1>
            <p class="text-gray-600 leading-relaxed">{{ activity.short_description }}</p>
          </div>

          <!-- Meta details -->
          <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div v-if="activity.location" class="bg-emerald-50 rounded-xl p-4 text-center">
              <svg class="w-5 h-5 text-emerald-600 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
              </svg>
              <div class="text-xs text-gray-500">{{ t('activity.location') }}</div>
              <div class="text-sm font-semibold text-gray-800 mt-0.5">{{ activity.location }}</div>
            </div>

            <div v-if="activity.max_participants" class="bg-emerald-50 rounded-xl p-4 text-center">
              <svg class="w-5 h-5 text-emerald-600 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0" />
              </svg>
              <div class="text-xs text-gray-500">{{ t('activity.capacity') }}</div>
              <div class="text-sm font-semibold text-gray-800 mt-0.5">{{ activity.max_participants }} {{ t('activity.spots') }}</div>
            </div>

            <div v-if="activity.duration_minutes" class="bg-emerald-50 rounded-xl p-4 text-center">
              <svg class="w-5 h-5 text-emerald-600 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <div class="text-xs text-gray-500">{{ t('activity.duration') }}</div>
              <div class="text-sm font-semibold text-gray-800 mt-0.5">{{ activity.duration_minutes }} min</div>
            </div>

            <div v-if="activity.booking_cutoff_hours" class="bg-emerald-50 rounded-xl p-4 text-center">
              <svg class="w-5 h-5 text-emerald-600 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
              </svg>
              <div class="text-xs text-gray-500">{{ t('activity.book_by') }}</div>
              <div class="text-sm font-semibold text-gray-800 mt-0.5">{{ activity.booking_cutoff_hours }}h {{ t('activity.before') }}</div>
            </div>
          </div>

          <!-- Difficulty, age, weather badges -->
          <div class="flex flex-wrap gap-2">
            <span v-if="activity.difficulty"
              :class="['text-xs font-semibold px-3 py-1 rounded-full', difficultyClass]">
              {{ difficultyLabel }}
            </span>
            <span v-if="activity.min_age" class="text-xs font-semibold px-3 py-1 rounded-full bg-blue-100 text-blue-700">
              Age {{ activity.min_age }}+
            </span>
          </div>

          <!-- Weather warning -->
          <div v-if="activity.weather_dependent" class="flex items-start gap-3 bg-amber-50 border border-amber-200 rounded-xl p-4">
            <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z" />
            </svg>
            <div>
              <p class="text-sm font-semibold text-amber-800">Weather Dependent</p>
              <p class="text-xs text-amber-700 mt-0.5">This activity may be cancelled or rescheduled due to adverse weather conditions.</p>
            </div>
          </div>

          <!-- Cancellation policy -->
          <div v-if="activity.cancellation_policy" class="border border-gray-100 rounded-xl overflow-hidden">
            <button @click="policyOpen = !policyOpen"
              class="w-full flex items-center justify-between px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors cursor-pointer">
              <span>Cancellation Policy</span>
              <svg :class="['w-4 h-4 text-gray-400 transition-transform', policyOpen ? 'rotate-180' : '']" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
            </button>
            <div v-if="policyOpen" class="px-4 pb-4 text-sm text-gray-600 leading-relaxed border-t border-gray-100 pt-3">
              {{ activity.cancellation_policy }}
            </div>
          </div>

          <div v-if="activity.description" class="prose prose-emerald max-w-none" v-html="activity.description" />

          <div v-if="activity.tags?.length" class="flex flex-wrap gap-2">
            <span v-for="tag in activity.tags" :key="tag.id"
              class="bg-stone-100 text-gray-600 text-xs px-3 py-1 rounded-full">
              {{ tag.name }}
            </span>
          </div>
        </div>

        <!-- Right: booking sidebar -->
        <div class="lg:col-span-1">
          <div class="sticky top-6">
            <div class="mb-2 text-3xl font-bold text-gray-900">
              {{ formatPrice(activity.is_on_sale ? activity.sale_price : activity.price) }}<span class="text-base font-normal text-gray-500"> {{ t('activity.per_person') }}</span>
              <span v-if="activity.is_on_sale" class="ml-2 text-lg font-normal text-gray-400 line-through">{{ formatPrice(activity.price) }}</span>
            </div>
            <SaleCountdown v-if="activity.is_on_sale && activity.sale_ends_at" :ends-at="activity.sale_ends_at" class="mb-4" />

            <div v-if="spotsRemaining !== null && spotsRemaining !== undefined" class="mb-4">
              <div v-if="spotsRemaining === 0" class="flex items-center gap-2 text-red-600">
                <span class="w-2 h-2 rounded-full bg-red-500 inline-block"></span>
                <span class="text-sm font-semibold">{{ t('activity.sold_out') }}</span>
              </div>
              <div v-else-if="spotsRemaining <= 5" class="flex items-center gap-2 text-orange-600">
                <span class="w-2 h-2 rounded-full bg-orange-500 inline-block animate-pulse"></span>
                <span class="text-sm font-semibold">{{ t('activity.only_x_left', { count: spotsRemaining }) }}</span>
              </div>
              <div v-else class="flex items-center gap-2 text-emerald-600">
                <span class="w-2 h-2 rounded-full bg-emerald-500 inline-block"></span>
                <span class="text-sm">{{ spotsRemaining }} {{ t('activity.spots_available') }}</span>
              </div>
            </div>

            <BookingForm :activity="activity" :spots-remaining="spotsRemaining" :available-slots="availableSlots" />
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
              class="bg-white rounded-2xl border border-stone-100 shadow-sm p-5">
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
        <ReviewForm :reviewable-type="'activity'" :reviewable-id="activity.id" />
      </div>

      <!-- Recommendations -->
      <div v-if="recommended?.length" class="mt-16">
        <h2 class="text-xl font-bold text-gray-900 mb-6">{{ t('activity.you_may_also_like') }}</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
          <ActivityCard v-for="item in recommended" :key="item.id" :activity="item" />
        </div>
      </div>

      <RecentlyViewed accent-color="text-emerald-600" />
    </div>
  </Layout>
</template>

<script setup>
import { Head, usePage } from '@inertiajs/vue3'
import { computed, onMounted, ref } from 'vue'
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
const policyOpen = ref(false)
const props = defineProps({
  activity: Object,
  recommended: Array,
  spotsRemaining: { type: Number, default: null },
  availableSlots: { type: Array, default: () => [] },
  schema: { type: Object, default: null },
})
const page = usePage()
const approvedReviews = computed(() => props.activity.reviews?.filter(r => r.status === 'approved') ?? [])

const difficultyLabel = computed(() => {
  const map = { easy: 'Easy', moderate: 'Moderate', hard: 'Hard', expert: 'Expert' }
  return map[props.activity.difficulty] ?? props.activity.difficulty
})

const difficultyClass = computed(() => {
  const map = {
    easy: 'bg-green-100 text-green-700',
    moderate: 'bg-yellow-100 text-yellow-700',
    hard: 'bg-orange-100 text-orange-700',
    expert: 'bg-red-100 text-red-700',
  }
  return map[props.activity.difficulty] ?? 'bg-gray-100 text-gray-700'
})

onMounted(() => {
  pushRecentlyViewed({ ...props.activity, name: props.activity.name ?? props.activity.title })
})

const breadcrumbs = computed(() => {
  const items = [
    { label: t('nav.home'), href: route('home') },
    { label: t('nav.browse'), href: route('shop') },
  ]
  if (props.activity.category) {
    items.push({ label: props.activity.category.name, href: route('shop') + '?category=' + props.activity.category.slug })
  }
  items.push({ label: props.activity.name })
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
