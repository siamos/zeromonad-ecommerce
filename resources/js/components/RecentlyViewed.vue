<template>
  <div v-if="items.length" class="mt-12">
    <h2 class="text-xl font-bold text-gray-900 mb-4">Recently Viewed</h2>
    <div class="flex gap-4 overflow-x-auto pb-2 scrollbar-hide">
      <a v-for="item in items" :key="item.id" :href="route('product.show', item.slug)"
        class="flex-none w-40 group">
        <div class="aspect-square rounded-xl overflow-hidden bg-gray-100 mb-2">
          <img v-if="item.image" :src="item.image" :alt="displayName(item.name)"
            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200" />
          <div v-else class="w-full h-full flex items-center justify-center text-gray-300">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
          </div>
        </div>
        <p class="text-sm font-medium text-gray-800 line-clamp-2 leading-tight">{{ displayName(item.name) }}</p>
        <p class="text-sm mt-1">
          <span v-if="item.is_on_sale && item.sale_price" :class="['font-semibold', accentColor]">
            €{{ item.sale_price }}
          </span>
          <span v-else :class="['font-semibold', accentColor]">€{{ item.price }}</span>
        </p>
      </a>
    </div>
  </div>
</template>

<script setup>
import { onMounted } from 'vue'
import { useRecentlyViewed } from '@/composables/useRecentlyViewed'

const props = defineProps({
  accentColor: { type: String, default: 'text-indigo-600' },
})

const route = window.route
const { getItems, items } = useRecentlyViewed()

onMounted(() => {
  getItems()
})

function displayName(name) {
  if (typeof name === 'object' && name !== null) {
    return name.en ?? name.el ?? Object.values(name)[0] ?? ''
  }
  return name ?? ''
}
</script>
