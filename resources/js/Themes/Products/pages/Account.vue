<template>
  <Layout>
    <Head title="My Account" />
    <div class="max-w-4xl mx-auto px-4 py-10">
      <h1 class="text-3xl font-bold text-gray-900 mb-8">My Account</h1>

      <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        <!-- Sidebar -->
        <nav class="space-y-1">
          <Link :href="route('account.index')"
            class="block px-4 py-2 rounded-lg text-sm font-medium text-indigo-600 bg-indigo-50">
            Overview
          </Link>
          <Link :href="route('account.orders')"
            class="block px-4 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-50 transition-colors">
            Orders
          </Link>
          <Link :href="route('logout')" method="post" as="button"
            class="block w-full text-left px-4 py-2 rounded-lg text-sm font-medium text-red-500 hover:bg-red-50 transition-colors">
            Sign Out
          </Link>
        </nav>

        <!-- Content -->
        <div class="lg:col-span-3 space-y-6">
          <!-- Account details -->
          <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Account Details</h2>
            <dl class="space-y-4">
              <div>
                <dt class="text-sm text-gray-500">Name</dt>
                <dd class="font-medium text-gray-900">{{ $page.props.auth.user?.name }}</dd>
              </div>
              <div>
                <dt class="text-sm text-gray-500">Email</dt>
                <dd class="font-medium text-gray-900">{{ $page.props.auth.user?.email }}</dd>
              </div>
            </dl>
          </div>

          <!-- Orders -->
          <div class="bg-white rounded-xl shadow-sm border border-gray-100">
            <div class="p-6 border-b border-gray-100 flex items-center justify-between">
              <h2 class="text-lg font-semibold text-gray-900">Orders</h2>
            </div>

            <div v-if="orders?.data?.length" class="divide-y divide-gray-100">
              <div v-for="order in orders.data" :key="order.id"
                class="px-6 py-4 flex items-center justify-between gap-4">
                <div>
                  <div class="font-medium text-gray-900 text-sm">{{ order.order_number }}</div>
                  <div class="text-xs text-gray-400 mt-0.5">{{ order.created_at }}</div>
                </div>
                <div class="flex items-center gap-4">
                  <div class="text-right">
                    <div class="font-bold text-gray-900 text-sm">{{ formatPrice(order.total) }}</div>
                    <span class="text-xs px-2 py-0.5 rounded-full font-medium"
                      :class="{
                        'bg-yellow-100 text-yellow-800': order.status === 'pending',
                        'bg-blue-100 text-blue-800': order.status === 'processing',
                        'bg-green-100 text-green-800': order.status === 'delivered',
                        'bg-red-100 text-red-800': order.status === 'cancelled',
                      }">
                      {{ order.status }}
                    </span>
                  </div>
                  <Link :href="route('account.orders.show', order.id)"
                    class="text-sm text-indigo-600 hover:text-indigo-800 font-medium shrink-0">
                    View →
                  </Link>
                </div>
              </div>
            </div>

            <div v-else class="text-center py-12 text-gray-400">
              <p class="font-medium">No orders yet</p>
              <Link :href="route('shop')" class="mt-3 inline-block text-sm text-indigo-600 hover:underline">
                Start shopping →
              </Link>
            </div>

            <!-- Pagination -->
            <div v-if="orders?.last_page > 1" class="px-6 py-4 border-t border-gray-100 flex justify-center gap-2">
              <Link v-for="link in orders.links" :key="link.label" :href="link.url ?? '#'"
                :class="['px-3 py-1.5 rounded-lg text-sm border', link.active
                  ? 'bg-indigo-600 text-white border-indigo-600'
                  : 'bg-white text-gray-600 border-gray-200 hover:border-gray-300']">
                <span v-html="link.label" />
              </Link>
            </div>
          </div>
        </div>
      </div>
    </div>
  </Layout>
</template>

<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3'
import Layout from '../Layout.vue'

const props = defineProps({ orders: Object })
const page = usePage()

function formatPrice(price) {
  return new Intl.NumberFormat('el-GR', {
    style: 'currency',
    currency: page.props.currency ?? 'EUR',
  }).format(price)
}
</script>
