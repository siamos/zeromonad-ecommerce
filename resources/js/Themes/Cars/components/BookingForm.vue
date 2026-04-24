<template>
  <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">

    <!-- Not available -->
    <template v-if="!isAvailable">
      <div class="text-center py-6">
        <span class="inline-block bg-red-100 text-red-700 text-sm font-semibold px-4 py-2 rounded-full">{{ t('booking.not_available') }}</span>
        <p class="text-sm text-gray-500 mt-2">{{ t('booking.not_available_desc') }}</p>
      </div>
    </template>

    <template v-else>
      <h3 class="font-bold text-gray-900 text-lg mb-5">{{ t('booking.title_car') }}</h3>

      <form @submit.prevent="submit" class="space-y-5">

        <!-- Date range -->
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">{{ t('booking.pickup_date') }}</label>
            <input type="date" v-model="form.pickup_date" :min="today" :max="maxPickupDate"
              @change="onPickupChange" required
              class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-500" />
          </div>
          <div>
            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">{{ t('booking.return_date') }}</label>
            <input type="date" v-model="form.return_date" :min="minReturnDate" required
              class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-500" />
          </div>
        </div>

        <!-- Days badge -->
        <div v-if="days > 0" class="flex items-center gap-2 text-sm text-slate-700 bg-slate-50 rounded-lg px-3 py-2">
          <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
          </svg>
          <span><strong>{{ days }}</strong> {{ t('booking.days') }}</span>
        </div>

        <!-- Date conflict warning -->
        <div v-if="dateConflict" class="flex items-start gap-2 bg-red-50 border border-red-200 rounded-lg px-3 py-2 text-sm text-red-700">
          <svg class="w-4 h-4 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          These dates overlap with an existing booking. Please choose different dates.
        </div>

        <!-- Pickup location -->
        <div v-if="pickupLocations.length">
          <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Pickup Location</label>
          <select v-model="form.pickup_location_id" required
            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-500 bg-white">
            <option value="" disabled>Select pickup location…</option>
            <option v-for="loc in pickupLocations" :key="loc.id" :value="loc.id">
              {{ loc.name }}{{ loc.address ? ` — ${loc.address}` : '' }}{{ Number(loc.pickup_fee) > 0 ? ` (+${formatPrice(loc.pickup_fee)})` : ' (free)' }}
            </option>
          </select>
          <p v-if="selectedPickup && Number(selectedPickup.pickup_fee) > 0" class="text-xs text-slate-600 mt-1">
            Pickup fee: <strong>{{ formatPrice(selectedPickup.pickup_fee) }}</strong>
          </p>
        </div>

        <!-- Drop-off location -->
        <div v-if="dropoffLocations.length">
          <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Drop-off Location</label>
          <select v-model="form.dropoff_location_id" required
            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-500 bg-white">
            <option value="" disabled>Select drop-off location…</option>
            <option v-for="loc in dropoffLocations" :key="loc.id" :value="loc.id">
              {{ loc.name }}{{ loc.address ? ` — ${loc.address}` : '' }}{{ Number(loc.dropoff_fee) > 0 ? ` (+${formatPrice(loc.dropoff_fee)})` : ' (free)' }}
            </option>
          </select>
          <p v-if="selectedDropoff && Number(selectedDropoff.dropoff_fee) > 0" class="text-xs text-slate-600 mt-1">
            Drop-off fee: <strong>{{ formatPrice(selectedDropoff.dropoff_fee) }}</strong>
          </p>
        </div>

        <!-- Extras -->
        <div v-if="availableExtras.length" class="border border-slate-100 rounded-xl p-4 space-y-3">
          <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">{{ t('booking.extras') }}</p>
          <label v-for="extra in availableExtras" :key="extra.key" class="flex items-center justify-between gap-3 cursor-pointer">
            <div class="flex items-center gap-2">
              <input type="checkbox" v-model="selectedExtras" :value="extra.key"
                class="rounded border-gray-300 text-slate-700 focus:ring-slate-500 cursor-pointer" />
              <span class="text-sm text-gray-700">{{ extra.label }}</span>
            </div>
            <span class="text-sm text-slate-600 font-medium whitespace-nowrap">+{{ formatPrice(extra.price) }}/{{ t('booking.day') }}</span>
          </label>
        </div>

        <!-- Price breakdown -->
        <div class="border-t border-gray-100 pt-4 space-y-1.5 text-sm">
          <div class="flex justify-between text-gray-600">
            <span>{{ formatPrice(vehicle.price_per_day) }} × {{ days || 1 }} {{ t('booking.days') }}</span>
            <span>{{ formatPrice(vehicle.price_per_day * (days || 1)) }}</span>
          </div>
          <div v-if="selectedPickup && Number(selectedPickup.pickup_fee) > 0" class="flex justify-between text-gray-600">
            <span>Pickup — {{ selectedPickup.name }}</span>
            <span>+{{ formatPrice(selectedPickup.pickup_fee) }}</span>
          </div>
          <div v-if="selectedDropoff && Number(selectedDropoff.dropoff_fee) > 0" class="flex justify-between text-gray-600">
            <span>Drop-off — {{ selectedDropoff.name }}</span>
            <span>+{{ formatPrice(selectedDropoff.dropoff_fee) }}</span>
          </div>
          <div v-for="extra in selectedExtraDetails" :key="extra.key" class="flex justify-between text-gray-600">
            <span>{{ extra.label }}</span>
            <span>+{{ formatPrice((extra.price ?? 0) * (days || 1)) }}</span>
          </div>
          <div class="flex justify-between font-bold text-gray-900 pt-2 border-t border-gray-100 text-base">
            <span>{{ t('booking.total') }}</span>
            <span>{{ formatPrice(grandTotal) }}</span>
          </div>
        </div>

        <button type="submit" :disabled="submitting || days === 0 || dateConflict || !canSubmit"
          class="w-full bg-slate-800 text-white py-3 rounded-xl font-semibold hover:bg-slate-700 transition-colors disabled:opacity-50 cursor-pointer">
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
  vehicle: Object,
  isAvailable: { type: Boolean, default: true },
  bookedRanges: { type: Array, default: () => [] },
  pickupLocations: { type: Array, default: () => [] },
  dropoffLocations: { type: Array, default: () => [] },
})

const page = usePage()
const today = new Date().toISOString().split('T')[0]
const maxPickupDate = computed(() => {
  const d = new Date()
  d.setFullYear(d.getFullYear() + 2)
  return d.toISOString().split('T')[0]
})

const form = reactive({
  bookable_type:       'vehicle',
  bookable_id:         props.vehicle.id,
  pickup_date:         '',
  return_date:         '',
  pickup_location_id:  props.pickupLocations[0]?.id ?? '',
  dropoff_location_id: props.dropoffLocations[0]?.id ?? '',
})

const selectedExtras = ref([])
const submitting = ref(false)

const minReturnDate = computed(() => {
  if (!form.pickup_date) return today
  const d = new Date(form.pickup_date)
  d.setDate(d.getDate() + 1)
  return d.toISOString().split('T')[0]
})

function onPickupChange() {
  if (form.return_date && form.return_date <= form.pickup_date) {
    form.return_date = ''
  }
}

const days = computed(() => {
  if (!form.pickup_date || !form.return_date) return 0
  const diff = new Date(form.return_date) - new Date(form.pickup_date)
  return Math.max(0, Math.floor(diff / (1000 * 60 * 60 * 24)))
})

const dateConflict = computed(() => {
  if (!form.pickup_date || !form.return_date) return false
  const start = new Date(form.pickup_date)
  const end = new Date(form.return_date)
  return props.bookedRanges.some(r => start < new Date(r.to) && end > new Date(r.from))
})

const selectedPickup = computed(() =>
  props.pickupLocations.find(l => l.id === form.pickup_location_id) ?? null
)
const selectedDropoff = computed(() =>
  props.dropoffLocations.find(l => l.id === form.dropoff_location_id) ?? null
)

const availableExtras = computed(() => props.vehicle.extras ?? [])
const selectedExtraDetails = computed(() =>
  availableExtras.value.filter(e => selectedExtras.value.includes(e.key))
)

const pickupFee = computed(() => Number(selectedPickup.value?.pickup_fee ?? 0))
const dropoffFee = computed(() => Number(selectedDropoff.value?.dropoff_fee ?? 0))

const grandTotal = computed(() => {
  const base = props.vehicle.price_per_day * (days.value || 1)
  const extras = selectedExtraDetails.value.reduce((s, e) => s + (e.price ?? 0) * (days.value || 1), 0)
  return base + pickupFee.value + dropoffFee.value + extras
})

const canSubmit = computed(() => {
  const needsPickup = props.pickupLocations.length > 0
  const needsDropoff = props.dropoffLocations.length > 0
  return (!needsPickup || form.pickup_location_id) && (!needsDropoff || form.dropoff_location_id)
})

function formatPrice(price) {
  return new Intl.NumberFormat('el-GR', {
    style: 'currency',
    currency: page.props.currency ?? 'EUR',
  }).format(price)
}

function submit() {
  submitting.value = true
  router.post(route('cart.add'), {
    bookable_type:       form.bookable_type,
    bookable_id:         form.bookable_id,
    quantity:            days.value || 1,
    pickup_date:         form.pickup_date,
    return_date:         form.return_date,
    pickup_location_id:  form.pickup_location_id || null,
    dropoff_location_id: form.dropoff_location_id || null,
    extras:              selectedExtras.value,
  }, {
    preserveScroll: true,
    onSuccess: () => openModal(props.vehicle.name),
    onFinish: () => { submitting.value = false },
  })
}
</script>
