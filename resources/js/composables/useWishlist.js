import { ref } from 'vue'
import { usePage } from '@inertiajs/vue3'

export function useWishlist(itemId, wishableType = null) {
  const page = usePage()
  const route = window.route

  function checkWishlisted() {
    if (wishableType && page.props.wishlist_items) {
      return page.props.wishlist_items.some(
        w => w.type === wishableType && w.id === itemId
      )
    }
    return page.props.wishlist_ids?.includes(itemId) ?? false
  }

  const isWishlisted = ref(checkWishlisted())
  const loading = ref(false)

  async function toggle() {
    if (!page.props.auth.user) {
      window.location.href = route('login')
      return
    }

    loading.value = true

    try {
      const body = wishableType
        ? { wishable_type: wishableType, wishable_id: itemId }
        : { product_id: itemId }

      const res = await fetch(route('wishlist.toggle'), {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
          Accept: 'application/json',
        },
        body: JSON.stringify(body),
      })

      const data = await res.json()
      isWishlisted.value = data.wishlisted

      if (wishableType && page.props.wishlist_items) {
        const items = page.props.wishlist_items ?? []
        if (data.wishlisted) {
          page.props.wishlist_items = [...items, { type: wishableType, id: itemId }]
        } else {
          page.props.wishlist_items = items.filter(w => !(w.type === wishableType && w.id === itemId))
        }
      } else {
        const ids = page.props.wishlist_ids ?? []
        if (data.wishlisted) {
          page.props.wishlist_ids = [...ids, itemId]
        } else {
          page.props.wishlist_ids = ids.filter(id => id !== itemId)
        }
      }
    } finally {
      loading.value = false
    }
  }

  return { isWishlisted, loading, toggle }
}
