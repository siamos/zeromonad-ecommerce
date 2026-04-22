<template>
  <Teleport to="body">
    <div v-if="isOpen" class="fixed inset-0 z-50 flex items-center justify-center px-4">
      <div class="absolute inset-0 bg-black/40" @click="closeModal" />
      <div class="relative bg-white rounded-2xl shadow-2xl p-8 max-w-sm w-full text-center">
        <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
          <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
          </svg>
        </div>
        <h3 class="text-xl font-bold text-gray-900 mb-1">{{ t('cart_modal.title') }}</h3>
        <p class="text-gray-500 text-sm mb-6 line-clamp-2">{{ productName }}</p>
        <div class="flex gap-3">
          <button
            @click="closeModal"
            class="flex-1 py-2.5 px-4 rounded-xl text-sm font-medium bg-gray-100 text-gray-700 hover:bg-gray-200 transition-colors cursor-pointer">
            {{ t('cart_modal.continue') }}
          </button>
          <Link
            :href="route('cart.index')"
            @click="closeModal"
            class="flex-1 py-2.5 px-4 rounded-xl text-sm font-medium bg-indigo-600 text-white hover:bg-indigo-700 transition-colors text-center">
            {{ t('cart_modal.view_cart') }}
          </Link>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import { useCartModal } from '@/composables/useCartModal'
import { useI18n } from '@/composables/useI18n'

const { isOpen, productName, closeModal } = useCartModal()
const { t } = useI18n()
const route = window.route
</script>
