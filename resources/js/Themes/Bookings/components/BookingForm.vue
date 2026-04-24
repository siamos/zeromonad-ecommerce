<template>
  <div class="bg-white rounded-2xl border border-stone-100 shadow-sm p-6">
    <!-- Out-of-stock: notify me form -->
    <template v-if="false">
      <div class="text-center py-4 mb-4">
        <span class="inline-block bg-red-100 text-red-700 text-xs font-semibold px-3 py-1 rounded-full">{{ t('booking.sold_out') }}</span>
      </div>
      <p class="text-sm text-gray-600 mb-4">{{ t('booking.notify_me_desc') }}</p>
      <form @submit.prevent="submitAlert" class="space-y-3">
        <input type="email" v-model="alertEmail" required :placeholder="t('booking.notify_email_placeholder')"
          class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500" />
        <button type="submit" :disabled="alertSubmitting || alertSent"
          class="w-full bg-amber-600 text-white py-3 rounded-xl font-semibold hover:bg-amber-700 transition-colors disabled:opacity-60 cursor-pointer">
          {{ alertSent ? t('booking.notify_subscribed') : (alertSubmitting ? t('booking.notify_submitting') : t('booking.notify_me')) }}
        </button>
      </form>
    </template>

    <template v-else>
    <h3 class="font-bold text-gray-900 text-lg mb-1">{{ t('booking.title_stay') }}</h3>
    <p v-if="accommodation.max_guests" class="text-sm text-gray-500 mb-4">
      {{ t('booking.max_guests', { count: accommodation.max_guests }) }}
    </p>

    <form @submit.prevent="submit" class="space-y-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('booking.check_in') }}</label>
        <input type="date" v-model="form.check_in" :min="today" required
          class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500" />
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('booking.check_out') }}</label>
        <input type="date" v-model="form.check_out" :min="minCheckOut" required
          class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500" />
      </div>

      <div v-if="rangeConflict" class="flex items-center gap-2 text-sm text-red-700 bg-red-50 rounded-lg px-3 py-2">
        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
        </svg>
        {{ t('booking.dates_unavailable') }}
      </div>
      <div v-else-if="nights > 0" class="flex items-center gap-2 text-sm text-amber-700 bg-amber-50 rounded-lg px-3 py-2">
        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
        {{ nights }} {{ t('booking.nights') }}
      </div>

      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('booking.adults') }}</label>
          <div class="flex items-center gap-2">
            <button type="button" @click="form.adults = Math.max(1, form.adults - 1)"
              class="w-8 h-8 rounded-full border border-gray-200 flex items-center justify-center text-gray-600 hover:border-amber-500 hover:text-amber-600 transition-colors cursor-pointer text-sm">−</button>
            <span class="w-6 text-center font-semibold text-gray-900 text-sm">{{ form.adults }}</span>
            <button type="button" @click="form.adults = Math.min(maxGuests, form.adults + form.children + 1)"
              class="w-8 h-8 rounded-full border border-gray-200 flex items-center justify-center text-gray-600 hover:border-amber-500 hover:text-amber-600 transition-colors cursor-pointer text-sm">+</button>
          </div>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('booking.children') }}</label>
          <div class="flex items-center gap-2">
            <button type="button" @click="form.children = Math.max(0, form.children - 1)"
              class="w-8 h-8 rounded-full border border-gray-200 flex items-center justify-center text-gray-600 hover:border-amber-500 hover:text-amber-600 transition-colors cursor-pointer text-sm">−</button>
            <span class="w-6 text-center font-semibold text-gray-900 text-sm">{{ form.children }}</span>
            <button type="button" @click="form.children = Math.min(maxGuests - form.adults, form.children + 1)"
              class="w-8 h-8 rounded-full border border-gray-200 flex items-center justify-center text-gray-600 hover:border-amber-500 hover:text-amber-600 transition-colors cursor-pointer text-sm">+</button>
          </div>
        </div>
      </div>

      <div class="border-t border-gray-100 pt-4 space-y-1">
        <div class="flex justify-between text-sm text-gray-600">
          <span>{{ formatPrice(accommodation.price_per_night) }} × {{ nights || 1 }} {{ t('booking.nights') }}</span>
          <span>{{ formatPrice(accommodation.price_per_night * (nights || 1)) }}</span>
        </div>
        <div class="flex justify-between font-bold text-gray-900">
          <span>{{ t('booking.total') }}</span>
          <span>{{ formatPrice(accommodation.price_per_night * (nights || 1)) }}</span>
        </div>
      </div>

      <button type="submit" :disabled="submitting || nights === 0 || rangeConflict"
        class="w-full bg-amber-600 text-white py-3 rounded-xl font-semibold hover:bg-amber-700 transition-colors disabled:opacity-60 cursor-pointer">
        {{ submitting ? t('booking.adding') : t('booking.book_stay') }}
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
const props = defineProps({ accommodation: Object, blockedDates: { type: Array, default: () => [] } })
const page = usePage()

const today = new Date().toISOString().split('T')[0]
const maxGuests = computed(() => props.accommodation.max_guests ?? 99)
const spotsLeft = computed(() => props.accommodation.max_guests ?? '∞')

const form = reactive({
  bookable_type: 'accommodation',
  bookable_id:   props.accommodation.id,
  quantity:      1,
  check_in:      '',
  check_out:     '',
  adults:        1,
  children:      0,
})

const submitting = ref(false)
const alertEmail = ref('')
const alertSubmitting = ref(false)
const alertSent = ref(false)

const minCheckOut = computed(() => {
  if (!form.check_in) return today
  const d = new Date(form.check_in)
  d.setDate(d.getDate() + 1)
  return d.toISOString().split('T')[0]
})

const nights = computed(() => {
  if (!form.check_in || !form.check_out) return 0
  const diff = new Date(form.check_out) - new Date(form.check_in)
  return Math.max(0, Math.floor(diff / (1000 * 60 * 60 * 24)))
})

const rangeConflict = computed(() => {
  if (!form.check_in || !form.check_out || !props.blockedDates.length) return false
  return props.blockedDates.some(range =>
    form.check_in < range.check_out && form.check_out > range.check_in
  )
})

function formatPrice(price) {
  return new Intl.NumberFormat('el-GR', {
    style: 'currency',
    currency: page.props.currency ?? 'EUR',
  }).format(price)
}

function submit() {
  submitting.value = true
  router.post(route('cart.add'), form, {
    preserveScroll: true,
    onSuccess: () => openModal(props.accommodation.name),
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
      body: JSON.stringify({ bookable_type: 'accommodation', bookable_id: props.accommodation.id, email: alertEmail.value }),
    })
    alertSent.value = true
  } finally {
    alertSubmitting.value = false
  }
}
</script>
