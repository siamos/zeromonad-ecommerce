<template>
  <div class="bg-white rounded-2xl border border-stone-100 shadow-sm p-6">
    <h3 class="font-bold text-gray-900 text-lg mb-1">{{ t('booking.title') }}</h3>
    <p v-if="activity.activity_detail?.capacity" class="text-sm text-gray-500 mb-4">
      {{ spotsLeft }} {{ t('booking.spots_remaining') }}
    </p>

    <form @submit.prevent="submit" class="space-y-4">
      <div v-if="activity.activity_detail?.event_date">
        <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('booking.date') }}</label>
        <div class="border border-gray-200 rounded-lg px-3 py-2 text-sm bg-gray-50 text-gray-700">
          {{ formatDate(activity.activity_detail.event_date) }}
        </div>
      </div>
      <div v-else>
        <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('booking.select_date') }}</label>
        <input type="date" v-model="form.booking_date" :min="today" required
          class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500" />
      </div>

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

      <button type="submit" :disabled="submitting"
        class="w-full bg-emerald-600 text-white py-3 rounded-xl font-semibold hover:bg-emerald-700 transition-colors disabled:opacity-60 cursor-pointer">
        {{ submitting ? t('booking.adding') : t('booking.add_to_cart') }}
      </button>
    </form>
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
const props = defineProps({ activity: Object })
const page = usePage()

const today = new Date().toISOString().split('T')[0]
const maxQty = computed(() => props.activity.activity_detail?.capacity ?? 99)
const spotsLeft = computed(() => props.activity.activity_detail?.capacity ?? '∞')

const form = reactive({
  product_id:   props.activity.id,
  quantity:     1,
  booking_date: props.activity.activity_detail?.event_date ?? '',
})

const submitting = ref(false)

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
  router.post(route('cart.add'), form, {
    preserveScroll: true,
    onSuccess: () => openModal(props.activity.name),
    onFinish: () => { submitting.value = false },
  })
}
</script>
