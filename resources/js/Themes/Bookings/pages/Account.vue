<template>
  <Layout>
    <Head :title="t('account.title')" />

    <div class="max-w-4xl mx-auto px-4 py-10">
      <h1 class="text-3xl font-bold text-gray-900 mb-8">{{ t('account.title') }}</h1>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <!-- Profile sidebar -->
        <div class="md:col-span-1">
          <div class="bg-white rounded-2xl border border-stone-100 shadow-sm p-6 text-center">
            <div class="w-16 h-16 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center text-2xl font-bold mx-auto mb-3">
              {{ initials }}
            </div>
            <div class="font-semibold text-gray-900">{{ user.name }}</div>
            <div class="text-sm text-gray-500 mt-0.5">{{ user.email }}</div>
            <Link :href="route('logout')" method="post" as="button"
              class="mt-4 text-sm text-red-500 hover:text-red-700 transition-colors">
              {{ t('account.sign_out') }}
            </Link>
          </div>
        </div>

        <!-- Stays -->
        <div class="md:col-span-2">
          <h2 class="font-bold text-gray-900 text-lg mb-4">{{ t('account.my_stays') }}</h2>

          <div v-if="orders.data?.length" class="space-y-4">
            <div v-for="order in orders.data" :key="order.id"
              class="bg-white rounded-2xl border border-stone-100 shadow-sm p-5">
              <div class="flex items-start justify-between mb-3">
                <div>
                  <span class="font-semibold text-gray-900">#{{ order.order_number }}</span>
                  <span class="text-sm text-gray-400 ml-2">{{ order.created_at }}</span>
                </div>
                <div class="flex gap-2">
                  <span :class="statusClass(order.status)" class="text-xs px-2.5 py-1 rounded-full font-semibold capitalize">
                    {{ order.status }}
                  </span>
                  <span :class="paymentClass(order.payment_status)" class="text-xs px-2.5 py-1 rounded-full font-semibold capitalize">
                    {{ order.payment_status }}
                  </span>
                </div>
              </div>

              <div class="space-y-2">
                <div v-for="item in order.items" :key="item.id" class="flex items-center gap-3 text-sm">
                  <div class="w-1.5 h-1.5 rounded-full bg-amber-400 shrink-0"></div>
                  <span class="text-gray-700">{{ item.product_name }}</span>
                  <span class="text-gray-400">×{{ item.quantity }} {{ t('booking.nights') }}</span>
                  <span class="ml-auto text-gray-700 font-medium">{{ formatPrice(item.subtotal) }}</span>
                </div>
              </div>

              <div class="flex justify-between items-center mt-3 pt-3 border-t border-gray-100">
                <span class="text-sm text-gray-500">{{ t('account.total') }}</span>
                <div class="flex items-center gap-3">
                  <span class="font-bold text-gray-900">{{ formatPrice(order.total) }}</span>
                  <Link :href="route('account.orders.show', order.id)"
                    class="text-xs text-amber-600 hover:text-amber-800 font-medium">
                    {{ t('account.view') }}
                  </Link>
                </div>
              </div>
            </div>
          </div>

          <div v-else class="text-center py-12 text-gray-400">
            <svg class="w-12 h-12 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            <p class="font-medium">{{ t('account.no_stays') }}</p>
            <Link :href="route('shop')" class="mt-3 inline-block text-sm text-amber-600 hover:underline">
              {{ t('account.find_stay') }}
            </Link>
          </div>

          <!-- Pagination -->
          <div v-if="orders.last_page > 1" class="mt-6 flex justify-center gap-2">
            <a v-for="link in orders.links" :key="link.label" :href="link.url ?? '#'"
              :class="['px-3 py-2 rounded-lg text-sm border', link.active
                ? 'bg-amber-600 text-white border-amber-600'
                : 'bg-white text-gray-600 border-gray-200']"
              v-html="link.label" />
          </div>
        </div>
      </div>
    </div>
  </Layout>
</template>

<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import Layout from '../Layout.vue'
import { useI18n } from '@/composables/useI18n'

const { t } = useI18n()
const route = window.route
const props = defineProps({ user: Object, orders: Object })
const page = usePage()
const initials = computed(() => props.user.name.split(' ').map(w => w[0]).join('').toUpperCase().slice(0, 2))

function formatPrice(price) {
  return new Intl.NumberFormat('el-GR', {
    style: 'currency',
    currency: page.props.currency ?? 'EUR',
  }).format(price)
}

function statusClass(status) {
  const map = {
    pending: 'bg-yellow-100 text-yellow-700',
    processing: 'bg-blue-100 text-blue-700',
    paid: 'bg-amber-100 text-amber-700',
    delivered: 'bg-amber-100 text-amber-700',
    cancelled: 'bg-red-100 text-red-700',
    refunded: 'bg-gray-100 text-gray-600',
  }
  return map[status] ?? 'bg-gray-100 text-gray-600'
}

function paymentClass(status) {
  return status === 'paid' ? 'bg-amber-100 text-amber-700' : 'bg-orange-100 text-orange-700'
}
</script>
