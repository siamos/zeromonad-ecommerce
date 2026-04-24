<template>
  <Teleport to="body">
    <Transition name="popup">
      <div v-if="visible" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50" @click="close" />
        <div class="relative w-full max-w-md rounded-2xl overflow-hidden shadow-2xl" :style="backgroundStyle">
          <!-- Overlay for image backgrounds -->
          <div v-if="promotion.background_type === 'image'" class="absolute inset-0 bg-black/40" />

          <div class="relative z-10 p-8 text-center">
            <button @click="close"
              class="absolute top-4 right-4 w-8 h-8 rounded-full bg-white/20 hover:bg-white/30 flex items-center justify-center transition-colors cursor-pointer"
              aria-label="Close">
              <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>

            <h2 class="text-2xl font-bold mb-2" :class="textClass">{{ promotion.title }}</h2>
            <p v-if="promotion.subtitle" class="mb-6 opacity-90" :class="textClass">{{ promotion.subtitle }}</p>

            <div v-if="promotion.coupon_code" class="mb-6">
              <div class="inline-flex items-center gap-2 bg-white/20 backdrop-blur-sm rounded-xl px-4 py-2">
                <span class="font-mono font-bold text-lg" :class="textClass">{{ promotion.coupon_code }}</span>
                <button @click="copyCoupon" class="text-white/70 hover:text-white transition-colors cursor-pointer" aria-label="Copy code">
                  <svg v-if="!copied" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                  </svg>
                  <svg v-else class="w-4 h-4 text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  </svg>
                </button>
              </div>
            </div>

            <a v-if="promotion.cta_label && promotion.cta_url"
              :href="promotion.cta_url"
              @click="close"
              class="inline-block bg-white text-gray-900 font-bold px-8 py-3 rounded-full hover:bg-gray-100 transition-colors">
              {{ promotion.cta_label }}
            </a>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { usePage } from '@inertiajs/vue3'

const page = usePage()
const visible = ref(false)
const copied = ref(false)

const promotion = computed(() => page.props.active_promotion)

const backgroundStyle = computed(() => {
  if (!promotion.value) return {}
  if (promotion.value.background_type === 'image' && promotion.value.background_image_url) {
    return { backgroundImage: `url(${promotion.value.background_image_url})`, backgroundSize: 'cover', backgroundPosition: 'center' }
  }
  return { backgroundColor: promotion.value.background_color ?? '#1e293b' }
})

const textClass = computed(() => {
  if (!promotion.value) return 'text-white'
  if (promotion.value.background_type === 'image') return 'text-white'
  const bg = promotion.value.background_color ?? '#1e293b'
  const r = parseInt(bg.slice(1, 3), 16)
  const g = parseInt(bg.slice(3, 5), 16)
  const b = parseInt(bg.slice(5, 7), 16)
  const luminance = (0.299 * r + 0.587 * g + 0.114 * b) / 255
  return luminance > 0.6 ? 'text-gray-900' : 'text-white'
})

function close() {
  if (promotion.value) {
    sessionStorage.setItem(`zm_popup_${promotion.value.id}`, '1')
  }
  visible.value = false
}

function copyCoupon() {
  if (!promotion.value?.coupon_code) return
  navigator.clipboard.writeText(promotion.value.coupon_code).then(() => {
    copied.value = true
    setTimeout(() => { copied.value = false }, 2000)
  })
}

onMounted(() => {
  const promo = promotion.value
  if (!promo) return
  if (sessionStorage.getItem(`zm_popup_${promo.id}`)) return
  const delay = (promo.delay_seconds ?? 3) * 1000
  setTimeout(() => { visible.value = true }, delay)
})
</script>

<style scoped>
.popup-enter-active, .popup-leave-active { transition: opacity 0.3s ease; }
.popup-enter-from, .popup-leave-to { opacity: 0; }
</style>
