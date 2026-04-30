import { ref, computed } from 'vue'

const MAX_COMPARE = 3
const STORAGE_KEY = 'compare_items'

const items = ref(JSON.parse(localStorage.getItem(STORAGE_KEY) ?? '[]'))

function persist() {
  localStorage.setItem(STORAGE_KEY, JSON.stringify(items.value))
}

export function useComparison() {
  const isComparing = computed(() => id => items.value.some(i => i.id === id))
  const count = computed(() => items.value.length)
  const canAdd = computed(() => items.value.length < MAX_COMPARE)

  function toggle(item) {
    const idx = items.value.findIndex(i => i.id === item.id)
    if (idx !== -1) {
      items.value.splice(idx, 1)
    } else if (items.value.length < MAX_COMPARE) {
      items.value.push(item)
    }
    persist()
  }

  function remove(id) {
    items.value = items.value.filter(i => i.id !== id)
    persist()
  }

  function clear() {
    items.value = []
    persist()
  }

  return { items, count, canAdd, isComparing, toggle, remove, clear }
}
