<template>
  <div class="bg-white rounded-2xl border border-stone-100 shadow-sm p-6">
    <!-- Out-of-stock: notify me form -->
    <template v-if="!activity.in_stock">
      <div class="text-center py-4 mb-4">
        <span class="inline-block bg-red-100 text-red-700 text-xs font-semibold px-3 py-1 rounded-full">{{ t('booking.sold_out') }}</span>
      </div>
      <p class="text-sm text-gray-600 mb-4">{{ t('booking.notify_me_desc') }}</p>
      <form @submit.prevent="submitAlert" class="space-y-3">
        <input type="email" v-model="alertEmail" required :placeholder="t('booking.notify_email_placeholder')"
          class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500" />
        <button type="submit" :disabled="alertSubmitting || alertSent"
          class="w-full bg-emerald-600 text-white py-3 rounded-xl font-semibold hover:bg-emerald-700 transition-colors disabled:opacity-60 cursor-pointer">
          {{ alertSent ? t('booking.notify_subscribed') : (alertSubmitting ? t('booking.notify_submitting') : t('booking.notify_me')) }}
        </button>
      </form>
    </template>

    <!-- Booking closed: fixed-date event past cutoff -->
    <template v-else-if="bookingClosed">
      <div class="text-center py-6">
        <span class="inline-block bg-gray-100 text-gray-600 text-sm font-semibold px-4 py-2 rounded-full">{{ t('booking.booking_closed') }}</span>
        <p class="text-sm text-gray-500 mt-2">{{ t('booking.booking_closed_desc') }}</p>
      </div>
    </template>

    <!-- No slots available (slot mode, all full or past) -->
    <template v-else-if="hasSlots && availableSlots.length === 0">
      <div class="text-center py-6">
        <span class="inline-block bg-gray-100 text-gray-600 text-sm font-semibold px-4 py-2 rounded-full">{{ t('booking.no_slots') }}</span>
      </div>
    </template>

    <template v-else>
      <h3 class="font-bold text-gray-900 text-lg mb-1">{{ t('booking.title') }}</h3>
      <p v-if="activity.max_participants && !hasSlots" class="text-sm text-gray-500 mb-4">
        {{ spotsLeft }} {{ t('booking.spots_remaining') }}
      </p>

      <form @submit.prevent="submit" class="space-y-4">

        <!-- Slot picker mode -->
        <div v-if="hasSlots">
          <label class="block text-sm font-medium text-gray-700 mb-2">{{ t('booking.select_date') }}</label>
          <div class="space-y-2 max-h-52 overflow-y-auto pr-1">
            <label
              v-for="slot in availableSlots"
              :key="slot.id"
              :class="[
                'flex items-center justify-between px-3 py-2.5 rounded-xl border cursor-pointer transition-colors',
                form.slot_id === slot.id
                  ? 'border-emerald-500 bg-emerald-50'
                  : 'border-gray-200 hover:border-emerald-300',
              ]"
            >
              <div class="flex items-center gap-3">
                <input type="radio" v-model="form.slot_id" :value="slot.id" class="text-emerald-600 focus:ring-emerald-500" />
                <div>
                  <div class="text-sm font-medium text-gray-900">{{ formatDate(slot.date) }}</div>
                  <div class="text-xs text-gray-500">{{ slot.spots_remaining }} {{ t('booking.spots_remaining') }}</div>
                </div>
              </div>
            </label>
          </div>
          <p v-if="!form.slot_id" class="text-xs text-gray-400 mt-1">{{ t('booking.select_slot') }}</p>
        </div>

        <!-- Open date picker or fixed date (non-slot mode) -->
        <template v-else>
          <div v-if="false"><!-- no fixed event_date on Activity model --></div>
          <div v-else>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('booking.select_date') }}</label>
            <input type="date" v-model="form.booking_date" :min="minBookingDate" required
              class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500" />
            <p v-if="cutoffHours > 0" class="text-xs text-gray-400 mt-1">{{ t('booking.book_at_least') }} {{ cutoffHours }}h {{ t('booking.before_event') }}</p>
          </div>
        </template>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('booking.participants') }}</label>
          <div class="flex items-center gap-3">
            <button type="button" @click="form.quantity = Math.max(1, form.quantity - 1)"
              class="w-9 h-9 rounded-full border border-gray-200 flex items-center justify-center text-gray-600 hover:border-emerald-500 hover:text-emerald-600 transition-colors cursor-pointer">−</button>
            <span class="w-8 text-center font-semibold text-gray-900">{{ form.quantity }}</span>
            <button type="button" @click="form.quantity = Math.min(maxQty, form.quantity + 1)"
              class="w-9 h-9 rounded-full border border-gray-200 flex items-center justify-center text-gray-600 hover:border-emerald-500 hover:text-emerald-600 transition-colors cursor-pointer">+</button>
          </div>
        </div>

        <div class="border-t border-gray-100 pt-4 space-y-1">
          <div class="flex justify-between text-sm text-gray-600">
            <span>{{ formatPrice(activity.price) }} × {{ form.quantity }}</span>
            <span>{{ formatPrice(activity.price * form.quantity) }}</span>
          </div>
          <div class="flex justify-between font-bold text-gray-900">
            <span>{{ t('booking.total') }}</span>
            <span>{{ formatPrice(activity.price * form.quantity) }}</span>
          </div>
        </div>

        <button type="submit" :disabled="submitting || submitDisabled"
          class="w-full bg-emerald-600 text-white py-3 rounded-xl font-semibold hover:bg-emerald-700 transition-colors disabled:opacity-60 cursor-pointer">
          {{ submitting ? t('booking.adding') : t('booking.add_to_cart') }}
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
  spotsRemaining: { type: Number, default: null },
  availableSlots: { type: Array, default: () => [] },
})
const page = usePage()

const today = new Date().toISOString().split('T')[0]

const hasSlots = computed(() => props.availableSlots.length > 0 || props.activity.activity_slots?.length > 0)

const selectedSlot = computed(() => props.availableSlots.find(s => s.id === form.slot_id) ?? null)

const maxQty = computed(() => {
  if (selectedSlot.value) return selectedSlot.value.spots_remaining
  return props.spotsRemaining ?? props.activity.max_participants ?? 99
})

const spotsLeft = computed(() => props.spotsRemaining ?? props.activity.max_participants ?? '∞')
const cutoffHours = computed(() => props.activity.booking_cutoff_hours ?? 0)

const minBookingDate = computed(() => {
  if (!cutoffHours.value) return today
  const d = new Date()
  d.setHours(d.getHours() + cutoffHours.value)
  return d.toISOString().split('T')[0]
})

const bookingClosed = computed(() => false)

const submitDisabled = computed(() => {
  if (hasSlots.value) return !form.slot_id
  return false
})

const form = reactive({
  bookable_type: 'activity',
  bookable_id:   props.activity.id,
  quantity:      1,
  booking_date:  '',
  slot_id:       null,
})

const submitting = ref(false)
const alertEmail = ref('')
const alertSubmitting = ref(false)
const alertSent = ref(false)

function formatPrice(price) {
  return new Intl.NumberFormat('el-GR', {
    style: 'currency',
    currency: page.props.currency ?? 'EUR',
  }).format(price)
}

function formatDate(date) {
  return new Date(date).toLocaleDateString('el-GR', {
    weekday: 'long', day: 'numeric', month: 'long', year: 'numeric',
  })
}

function submit() {
  submitting.value = true
  const payload = { ...form }
  if (selectedSlot.value) {
    payload.booking_date = selectedSlot.value.date
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
      body: JSON.stringify({ bookable_type: 'activity', bookable_id: props.activity.id, email: alertEmail.value }),
    })
    alertSent.value = true
  } finally {
    alertSubmitting.value = false
  }
}
</script>
