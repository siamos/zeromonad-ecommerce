import { ref } from 'vue'

const STORAGE_KEY = 'recently_viewed'
const MAX_ITEMS = 10

function load() {
  try {
    return JSON.parse(localStorage.getItem(STORAGE_KEY) ?? '[]')
  } catch {
    return []
  }
}

const items = ref(load())

export function useRecentlyViewed() {
  function push(product) {
    const list = load().filter(p => p.id !== product.id)
    list.unshift({
      id: product.id,
      name: product.name,
      slug: product.slug,
      price: product.price,
      sale_price: product.sale_price ?? null,
      is_on_sale: product.is_on_sale ?? false,
      image: product.images?.[0] ?? product.media?.[0]?.original_url ?? null,
    })
    const trimmed = list.slice(0, MAX_ITEMS)
    localStorage.setItem(STORAGE_KEY, JSON.stringify(trimmed))
    items.value = trimmed
  }

  function getItems() {
    items.value = load()
    return items
  }

  return { push, getItems, items }
}
