<template>
  <Teleport to="body">
    <Transition
      enter-active-class="transition-all duration-300 ease-out"
      enter-from-class="translate-y-full opacity-0"
      leave-active-class="transition-all duration-200 ease-in"
      leave-to-class="translate-y-full opacity-0"
    >
      <div v-if="count > 0"
        class="fixed bottom-0 inset-x-0 z-50 bg-white border-t border-gray-200 shadow-2xl">
        <div class="max-w-7xl mx-auto px-4 py-3 flex items-center gap-4">
          <div class="flex items-center gap-3 flex-1 overflow-x-auto">
            <span class="text-sm font-semibold text-gray-700 shrink-0">
              Compare ({{ count }}/{{ MAX }})
            </span>
            <div class="flex items-center gap-2">
              <div v-for="item in items" :key="item.id"
                class="flex items-center gap-1.5 bg-gray-50 border border-gray-200 rounded-lg px-2.5 py-1.5 text-xs font-medium text-gray-700 shrink-0">
                <span class="line-clamp-1 max-w-28">{{ item.name }}</span>
                <button @click="remove(item.id)"
                  class="text-gray-400 hover:text-red-500 transition-colors ml-1 cursor-pointer">
                  <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
              </div>
              <!-- Empty slots -->
              <div v-for="n in MAX - count" :key="'empty-' + n"
                class="flex items-center justify-center w-24 h-7 border border-dashed border-gray-300 rounded-lg text-xs text-gray-300">
                + item
              </div>
            </div>
          </div>

          <div class="flex items-center gap-2 shrink-0">
            <button @click="clear"
              class="text-xs text-gray-400 hover:text-gray-600 transition-colors cursor-pointer">
              Clear
            </button>
            <button
              :disabled="count < 2"
              @click="open = true"
              class="px-4 py-2 rounded-xl text-sm font-semibold transition-colors cursor-pointer"
              :class="count >= 2
                ? 'bg-gray-900 text-white hover:bg-gray-700'
                : 'bg-gray-100 text-gray-400 cursor-not-allowed'">
              Compare Now
            </button>
          </div>
        </div>
      </div>
    </Transition>

    <!-- Comparison modal -->
    <Transition
      enter-active-class="transition-all duration-200 ease-out"
      enter-from-class="opacity-0"
      leave-active-class="transition-all duration-150 ease-in"
      leave-to-class="opacity-0"
    >
      <div v-if="open" class="fixed inset-0 z-50 bg-black/60 flex items-center justify-center p-4"
        @click.self="open = false">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[85vh] overflow-auto">
          <div class="flex items-center justify-between p-5 border-b border-gray-100">
            <h2 class="text-lg font-bold text-gray-900">Comparison</h2>
            <button @click="open = false" class="text-gray-400 hover:text-gray-600 transition-colors cursor-pointer">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead>
                <tr class="border-b border-gray-100">
                  <th class="text-left p-4 font-semibold text-gray-500 w-36"></th>
                  <th v-for="item in items" :key="item.id"
                    class="p-4 text-center align-top">
                    <img v-if="item.image_url" :src="item.image_url" :alt="item.name"
                      class="w-full h-28 object-cover rounded-xl mb-2" />
                    <div class="font-bold text-gray-900 leading-tight">{{ item.name }}</div>
                    <a :href="route('product.show', item.slug)"
                      class="text-xs text-blue-600 hover:underline mt-1 inline-block">View →</a>
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(row, idx) in comparisonRows" :key="row.key"
                  :class="idx % 2 === 0 ? 'bg-gray-50' : 'bg-white'">
                  <td class="p-4 font-medium text-gray-500">{{ row.label }}</td>
                  <td v-for="item in items" :key="item.id"
                    class="p-4 text-center text-gray-800">
                    {{ row.getValue(item) || '—' }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="p-5 border-t border-gray-100 flex justify-end">
            <button @click="open = false"
              class="px-4 py-2 rounded-lg text-sm font-medium bg-gray-100 text-gray-600 hover:bg-gray-200 transition-colors cursor-pointer">
              Close
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useComparison } from '@/composables/useComparison'

const { items, count, remove, clear } = useComparison()
const route = window.route
const MAX = 3
const open = ref(false)

const comparisonRows = computed(() => {
  const sample = items.value[0]
  if (!sample) return []

  const rows = [
    { key: 'price',    label: 'Price',    getValue: i => i.price != null ? formatPrice(i.price) : null },
    { key: 'category', label: 'Category', getValue: i => i.category ?? null },
    { key: 'location', label: 'Location', getValue: i => i.location ?? null },
    { key: 'duration', label: 'Duration', getValue: i => i.duration_minutes ? `${i.duration_minutes} min` : null },
    { key: 'capacity', label: 'Capacity', getValue: i => i.max_participants ?? i.max_guests ?? null },
    { key: 'bedrooms', label: 'Bedrooms', getValue: i => i.bedrooms ?? null },
    { key: 'bathrooms',label: 'Bathrooms',getValue: i => i.bathrooms ?? null },
    { key: 'seats',    label: 'Seats',    getValue: i => i.seats ?? null },
    { key: 'difficulty',label:'Difficulty',getValue: i => i.difficulty ?? null },
    { key: 'sku',      label: 'SKU',      getValue: i => i.sku ?? null },
  ]

  // Only show rows where at least one item has a value
  return rows.filter(row => items.value.some(i => row.getValue(i) != null))
})

function formatPrice(price) {
  return new Intl.NumberFormat('el-GR', {
    style: 'currency',
    currency: 'EUR',
  }).format(price)
}
</script>
