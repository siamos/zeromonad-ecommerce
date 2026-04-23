<template>
  <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
    <!-- Out-of-stock: notify me form -->
    <template v-if="!activity.in_stock">
      <div class="text-center py-4 mb-4">
        <span class="inline-block bg-red-100 text-red-700 text-xs font-semibold px-3 py-1 rounded-full">{{ t('booking.sold_out') }}</span>
      </div>
      <p class="text-sm text-gray-600 mb-4">{{ t('booking.notify_me_desc') }}</p>
      <form @submit.prevent="submitAlert" class="space-y-3">
        <input type="email" v-model="alertEmail" required :placeholder="t('booking.notify_email_placeholder')"
          class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-500" />
        <button type="submit" :disabled="alertSubmitting || alertSent"
          class="w-full bg-slate-800 text-white py-3 rounded-xl font-semibold hover:bg-slate-700 transition-colors disabled:opacity-60 cursor-pointer">
          {{ alertSent ? t('booking.notify_subscribed') : (alertSubmitting ? t('booking.notify_submitting') : t('booking.notify_me')) }}
        </button>
      </form>
    </template>

    <!-- Not available for current dates -->
    <template v-else-if="!isAvailable">
      <div class="text-center py-6">
        <span class="inline-block bg-red-100 text-red-700 text-sm font-semibold px-4 py-2 rounded-full">{{ t('booking.not_available') }}</span>
        <p class="text-sm text-gray-500 mt-2">{{ t('booking.not_available_desc') }}</p>
      </div>
    </template>

    <template v-else>
      <h3 class="font-bold text-gray-900 text-lg mb-4">{{ t('booking.title_car') }}</h3>

      <form @submit.prevent="submit" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('booking.pickup_date') }}</label>
          <input type="date" v-model="form.pickup_date" :min="today" required
            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-500" />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('booking.return_date') }}</label>
          <input type="date" v-model="form.return_date" :min="minReturnDate" required
            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-500" />
        </div>

        <div v-if="days > 0" class="flex items-center gap-2 text-sm text-slate-700 bg-slate-50 rounded-lg px-3 py-2">
          <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
          </svg>
          {{ days }} {{ t('booking.days') }}
        </div>

        <!-- Extras -->
        <div v-if="availableExtras.length" class="border border-slate-100 rounded-xl p-4 space-y-3">
          <p class="text-sm font-medium text-gray-700">{{ t('booking.extras') }}</p>
          <label v-for="extra in availableExtras" :key="extra.key" class="flex items-center justify-between gap-3 cursor-pointer">
            <div class="flex items-center gap-2">
              <input type="checkbox" v-model="selectedExtras" :value="extra.key"
                class="rounded border-gray-300 text-slate-700 focus:ring-slate-500 cursor-pointer" />
              <span class="text-sm text-gray-700">{{ extra.label }}</span>
            </div>
            <span class="text-sm text-slate-600 font-medium">+{{ formatPrice(extra.price) }}/{{ t('booking.day') }}</span>
          </label>
        </div>

        <div class="border-t border-gray-100 pt-4 space-y-1">
          <div class="flex justify-between text-sm text-gray-600">
            <span>{{ formatPrice(activity.price) }} × {{ days || 1 }} {{ t('booking.days') }}</span>
            <span>{{ formatPrice(activity.price * (days || 1)) }}</span>
          </div>
          <div v-for="extra in selectedExtraDetails" :key="extra.key" class="flex justify-between text-sm text-gray-600">
            <span>{{ extra.label }}</span>
            <span>+{{ formatPrice(extra.price * (days || 1)) }}</span>
          </div>
          <div class="flex justify-between font-bold text-gray-900 pt-1 border-t border-gray-100">
            <span>{{ t('booking.total') }}</span>
            <span>{{ formatPrice(grandTotal) }}</span>
          </div>
        </div>

        <button type="submit" :disabled="submitting || days === 0"
          class="w-full bg-slate-800 text-white py-3 rounded-xl font-semibold hover:bg-slate-700 transition-colors disabled:opacity-60 cursor-pointer">
          {{ submitting ? t('booking.adding') : t('booking.rent_car') }}
        </button>
      </form>
    </template>
  </div>
</template>

<script setup>
import { reactive, computed, ref } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import { useI18n } from '@/composables/useI18n'
import { useCartModal } from '@/composables/useCartModal'

const { t } = useI18n()
const { openModal } = useCartModal()
const route = window.route
const props = defineProps({
  activity: Object,
  isAvailable: { type: Boolean, default: true },
})
const page = usePage()

const today = new Date().toISOString().split('T')[0]

const form = reactive({
  product_id:  props.activity.id,
  quantity:    1,
  pickup_date: '',
  return_date: '',
})

const selectedExtras = ref([])

const submitting = ref(false)
const alertEmail = ref('')
const alertSubmitting = ref(false)
const alertSent = ref(false)

const minReturnDate = computed(() => {
  if (!form.pickup_date) return today
  const d = new Date(form.pickup_date)
  d.setDate(d.getDate() + 1)
  return d.toISOString().split('T')[0]
})

const days = computed(() => {
  if (!form.pickup_date || !form.return_date) return 0
  const diff = new Date(form.return_date) - new Date(form.pickup_date)
  return Math.max(0, Math.floor(diff / (1000 * 60 * 60 * 24)))
})

const EXTRAS_CATALOG = [
  { key: 'gps', label: 'GPS Navigation', price: 5 },
  { key: 'child_seat', label: 'Child Seat', price: 3 },
  { key: 'full_insurance', label: 'Full Insurance', price: 10 },
  { key: 'extra_driver', label: 'Extra Driver', price: 7 },
]

const availableExtras = computed(() => {
  const extras = props.activity.activity_detail?.extra_attributes?.available_extras ?? []
  if (!extras.length) return EXTRAS_CATALOG
  return EXTRAS_CATALOG.filter(e => extras.includes(e.key))
})

const selectedExtraDetails = computed(() =>
  EXTRAS_CATALOG.filter(e => selectedExtras.value.includes(e.key))
)

const grandTotal = computed(() => {
  const base = props.activity.price * (days.value || 1)
  const extrasTotal = selectedExtraDetails.value.reduce((sum, e) => sum + e.price * (days.value || 1), 0)
  return base + extrasTotal
})

function formatPrice(price) {
  return new Intl.NumberFormat('el-GR', {
    style: 'currency',
    currency: page.props.currency ?? 'EUR',
  }).format(price)
}

function submit() {
  submitting.value = true
  const payload = {
    ...form,
    quantity: days.value || 1,
    extras: selectedExtras.value,
  }
  router.post(route('cart.add'), payload, {
    preserveScroll: true,
    onSuccess: () => openModal(props.activity.name),
    onFinish: () => { submitting.value = false },
  })
}

async function submitAlert() {
  alertSubmitting.value = true
  try {
    await fetch(route('stock-alerts.store'), {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
      },
      body: JSON.stringify({ product_id: props.activity.id, email: alertEmail.value }),
    })
    alertSent.value = true
  } finally {
    alertSubmitting.value = false
  }
}
</script>
