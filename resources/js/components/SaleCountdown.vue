<template>
  <div v-if="isActive" class="flex items-center gap-2">
    <span class="text-xs font-semibold uppercase tracking-wide text-red-500">Sale ends in</span>
    <div class="flex items-center gap-1 font-mono text-sm font-bold text-red-600">
      <span class="bg-red-50 border border-red-200 rounded px-1.5 py-0.5">{{ pad(hours) }}</span>
      <span class="text-red-400">:</span>
      <span class="bg-red-50 border border-red-200 rounded px-1.5 py-0.5">{{ pad(minutes) }}</span>
      <span class="text-red-400">:</span>
      <span class="bg-red-50 border border-red-200 rounded px-1.5 py-0.5">{{ pad(seconds) }}</span>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'

const props = defineProps({
  endsAt: { type: String, required: true },
})

const remaining = ref(0)
let timer = null

const isActive = computed(() => remaining.value > 0)
const hours = computed(() => Math.floor(remaining.value / 3600))
const minutes = computed(() => Math.floor((remaining.value % 3600) / 60))
const seconds = computed(() => remaining.value % 60)

function pad(n) {
  return String(n).padStart(2, '0')
}

function tick() {
  const diff = Math.floor((new Date(props.endsAt) - Date.now()) / 1000)
  remaining.value = diff > 0 ? diff : 0
  if (remaining.value === 0 && timer) {
    clearInterval(timer)
    timer = null
  }
}

onMounted(() => {
  tick()
  if (remaining.value > 0) {
    timer = setInterval(tick, 1000)
  }
})

onUnmounted(() => {
  if (timer) { clearInterval(timer) }
})
</script>
