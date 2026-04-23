import { ref } from 'vue'
import { usePage } from '@inertiajs/vue3'

export function useWishlist(productId) {
  const page = usePage()
  const route = window.route

  const isWishlisted = ref(page.props.wishlist_ids?.includes(productId) ?? false)
  const loading = ref(false)

  async function toggle() {
    if (!page.props.auth.user) {
      window.location.href = route('login')
      return
    }

    loading.value = true

    try {
      const res = await fetch(route('wishlist.toggle'), {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
          Accept: 'application/json',
        },
        body: JSON.stringify({ product_id: productId }),
      })

      const data = await res.json()
      isWishlisted.value = data.wishlisted

      const ids = page.props.wishlist_ids ?? []
      if (data.wishlisted) {
        page.props.wishlist_ids = [...ids, productId]
      } else {
        page.props.wishlist_ids = ids.filter(id => id !== productId)
      }
    } finally {
      loading.value = false
    }
  }

  return { isWishlisted, loading, toggle }
}
