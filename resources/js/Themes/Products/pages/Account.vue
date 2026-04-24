<template>
  <Layout>
    <Head :title="t('account.title')" />
    <div class="max-w-4xl mx-auto px-4 py-10">
      <h1 class="text-3xl font-bold text-gray-900 mb-8">{{ t('account.title') }}</h1>

      <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        <!-- Sidebar -->
        <nav class="space-y-1">
          <Link :href="route('account.index')"
            class="block px-4 py-2 rounded-lg text-sm font-medium text-indigo-600 bg-indigo-50">
            {{ t('account.overview') }}
          </Link>
          <Link :href="route('account.orders')"
            class="block px-4 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-50 transition-colors">
            {{ t('account.orders') }}
          </Link>
          <Link :href="route('logout')" method="post" as="button"
            class="block w-full text-left px-4 py-2 rounded-lg text-sm font-medium text-red-500 hover:bg-red-50 transition-colors">
            {{ t('account.sign_out_long') }}
          </Link>
        </nav>

        <!-- Content -->
        <div class="lg:col-span-3 space-y-6">
          <!-- Account details -->
          <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ t('account.account_details') }}</h2>
            <dl class="space-y-4">
              <div>
                <dt class="text-sm text-gray-500">{{ t('account.name') }}</dt>
                <dd class="font-medium text-gray-900">{{ $page.props.auth.user?.name }}</dd>
              </div>
              <div>
                <dt class="text-sm text-gray-500">{{ t('account.email') }}</dt>
                <dd class="font-medium text-gray-900">{{ $page.props.auth.user?.email }}</dd>
              </div>
            </dl>
          </div>

          <!-- Wishlist -->
          <div v-if="wishlistItems?.length" class="bg-white rounded-xl shadow-sm border border-gray-100">
            <div class="p-6 border-b border-gray-100 flex items-center justify-between">
              <h2 class="text-lg font-semibold text-gray-900">{{ t('account.wishlist') }}</h2>
              <span class="text-sm text-gray-400">{{ wishlistItems.length }} {{ t('account.wishlist_items') }}</span>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 p-6">
              <div v-for="item in wishlistItems" :key="item.id"
                class="flex gap-3 border border-gray-100 rounded-xl p-3">
                <Link :href="route('product.show', item.slug)">
                  <img :src="item.image_url" :alt="item.name" class="w-16 h-16 rounded-lg object-cover shrink-0" />
                </Link>
                <div class="flex-1 min-w-0">
                  <Link :href="route('product.show', item.slug)">
                    <div class="font-medium text-sm text-gray-900 truncate hover:text-indigo-600">{{ item.name }}</div>
                  </Link>
                  <div class="text-sm font-bold text-indigo-600 mt-0.5">{{ formatPrice(item.price) }}</div>
                  <button @click="removeFromWishlist(item.id)"
                    class="text-xs text-gray-400 hover:text-red-500 mt-1 cursor-pointer">
                    {{ t('account.wishlist_remove') }}
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Loyalty Points -->
          <div v-if="user?.points_balance !== undefined" class="bg-white rounded-xl shadow-sm border border-gray-100">
            <div class="p-6 border-b border-gray-100 flex items-center justify-between">
              <h2 class="text-lg font-semibold text-gray-900">{{ t('account.loyalty_points') }}</h2>
              <span class="text-2xl font-bold text-indigo-600">{{ user.points_balance }} <span class="text-sm font-normal text-gray-400">{{ t('account.points') }}</span></span>
            </div>
            <div v-if="pointsHistory?.length" class="divide-y divide-gray-100">
              <div v-for="tx in pointsHistory" :key="tx.id" class="px-6 py-3 flex items-center justify-between text-sm">
                <span class="text-gray-600">{{ tx.description }}</span>
                <span :class="tx.points > 0 ? 'text-green-600 font-semibold' : 'text-red-500 font-semibold'">
                  {{ tx.points > 0 ? '+' : '' }}{{ tx.points }}
                </span>
              </div>
            </div>
            <p v-else class="text-center text-sm text-gray-400 py-6">{{ t('account.no_points_yet') }}</p>
          </div>

          <!-- Orders summary -->
          <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center justify-between">
            <div>
              <h2 class="text-lg font-semibold text-gray-900">{{ t('account.orders') }}</h2>
              <p class="text-sm text-gray-400 mt-0.5">{{ t('account.orders_summary') }}</p>
            </div>
            <Link :href="route('account.orders')"
              class="shrink-0 text-sm font-medium text-indigo-600 hover:text-indigo-800">
              {{ t('account.view_all_orders') }} →
            </Link>
          </div>
        </div>
      </div>
    </div>
  </Layout>
</template>

<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3'
import Layout from '../Layout.vue'
import { useI18n } from '@/composables/useI18n'

const { t } = useI18n()
const route = window.route

const props = defineProps({ wishlistItems: Array, user: Object, pointsHistory: Array })
const page = usePage()

async function removeFromWishlist(productId) {
  await fetch(route('wishlist.toggle'), {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
      Accept: 'application/json',
    },
    body: JSON.stringify({ product_id: productId }),
  })
  page.props.wishlist_ids = (page.props.wishlist_ids ?? []).filter(id => id !== productId)
  const idx = props.wishlistItems.findIndex(i => i.id === productId)
  if (idx !== -1) props.wishlistItems.splice(idx, 1)
}
</script>
