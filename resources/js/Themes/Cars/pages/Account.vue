<template>
  <Layout>
    <Head :title="t('account.title')" />

    <div class="max-w-4xl mx-auto px-4 py-10">
      <h1 class="text-3xl font-bold text-gray-900 mb-8">{{ t('account.title') }}</h1>

      <div v-if="$page.props.flash?.success"
        class="mb-6 bg-green-50 border border-green-200 text-green-700 text-sm rounded-xl px-4 py-3">
        {{ $page.props.flash.success }}
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <!-- Profile sidebar -->
        <div class="md:col-span-1 space-y-4">
          <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 text-center">
            <div class="w-16 h-16 rounded-full bg-slate-100 text-slate-700 flex items-center justify-center text-2xl font-bold mx-auto mb-3">
              {{ initials }}
            </div>
            <div class="font-semibold text-gray-900">{{ user.name }}</div>
            <div class="text-sm text-gray-500 mt-0.5">{{ user.email }}</div>
            <button @click="editingProfile = !editingProfile"
              class="mt-3 text-sm text-slate-700 hover:underline block mx-auto">
              {{ editingProfile ? t('account.cancel') : t('account.edit_profile') }}
            </button>
            <Link :href="route('logout')" method="post" as="button"
              class="mt-2 text-sm text-red-500 hover:text-red-700 transition-colors block">
              {{ t('account.sign_out') }}
            </Link>
          </div>

          <!-- Address book -->
          <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
            <div class="flex items-center justify-between mb-3">
              <span class="text-sm font-semibold text-gray-900">{{ t('account.address_book') }}</span>
              <button @click="addingAddress = !addingAddress" class="text-xs text-slate-700 hover:underline">
                {{ addingAddress ? t('account.cancel') : '+' }}
              </button>
            </div>
            <form v-if="addingAddress" @submit.prevent="submitAddress" class="space-y-2 mb-3">
              <input v-model="addressForm.label" type="text" :placeholder="t('account.address_label')"
                class="w-full rounded-lg border border-slate-200 px-2.5 py-1.5 text-xs focus:outline-none focus:ring-1 focus:ring-slate-500" />
              <input v-model="addressForm.name" type="text" :placeholder="t('account.address_full_name')"
                class="w-full rounded-lg border border-slate-200 px-2.5 py-1.5 text-xs focus:outline-none focus:ring-1 focus:ring-slate-500" />
              <input v-model="addressForm.line1" type="text" :placeholder="t('account.address_line1')"
                class="w-full rounded-lg border border-slate-200 px-2.5 py-1.5 text-xs focus:outline-none focus:ring-1 focus:ring-slate-500" />
              <div class="grid grid-cols-2 gap-2">
                <input v-model="addressForm.city" type="text" :placeholder="t('account.address_city')"
                  class="w-full rounded-lg border border-slate-200 px-2.5 py-1.5 text-xs focus:outline-none focus:ring-1 focus:ring-slate-500" />
                <input v-model="addressForm.zip" type="text" :placeholder="t('account.address_zip')"
                  class="w-full rounded-lg border border-slate-200 px-2.5 py-1.5 text-xs focus:outline-none focus:ring-1 focus:ring-slate-500" />
              </div>
              <button type="submit" :disabled="addressForm.processing"
                class="w-full bg-slate-800 text-white text-xs font-medium py-1.5 rounded-lg hover:bg-slate-900 disabled:opacity-50 transition-colors">
                {{ t('account.save_address') }}
              </button>
            </form>
            <div v-if="addresses?.length" class="space-y-2">
              <div v-for="addr in addresses" :key="addr.id" class="flex justify-between items-start text-xs text-gray-600">
                <div>
                  <span class="font-medium text-gray-800">{{ addr.label }}</span>
                  <span v-if="addr.is_default" class="ml-1 text-slate-700">({{ t('account.default') }})</span>
                  <p>{{ addr.line1 }}, {{ addr.city }}</p>
                </div>
                <button @click="deleteAddress(addr.id)" class="text-red-400 hover:text-red-600 ml-2">✕</button>
              </div>
            </div>
            <p v-else-if="!addingAddress" class="text-xs text-gray-400 text-center py-2">{{ t('account.no_addresses') }}</p>
          </div>

          <div v-if="editingProfile" class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
            <h3 class="font-semibold text-gray-900 mb-4 text-sm">{{ t('account.edit_profile') }}</h3>
            <form @submit.prevent="submitProfile" class="space-y-3">
              <div>
                <input v-model="profileForm.name" type="text" :placeholder="t('account.name')"
                  class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-500" />
                <p v-if="profileForm.errors.name" class="text-red-500 text-xs mt-1">{{ profileForm.errors.name }}</p>
              </div>
              <div>
                <input v-model="profileForm.email" type="email" :placeholder="t('account.email')"
                  class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-500" />
                <p v-if="profileForm.errors.email" class="text-red-500 text-xs mt-1">{{ profileForm.errors.email }}</p>
              </div>
              <div class="pt-2 border-t border-slate-100">
                <p class="text-xs text-gray-400 mb-2">{{ t('account.change_password_optional') }}</p>
                <div>
                  <input v-model="profileForm.current_password" type="password" :placeholder="t('account.current_password')"
                    class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm mb-2 focus:outline-none focus:ring-2 focus:ring-slate-500" />
                  <p v-if="profileForm.errors.current_password" class="text-red-500 text-xs mb-2">{{ profileForm.errors.current_password }}</p>
                </div>
                <input v-model="profileForm.password" type="password" :placeholder="t('account.new_password')"
                  class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm mb-2 focus:outline-none focus:ring-2 focus:ring-slate-500" />
                <input v-model="profileForm.password_confirmation" type="password" :placeholder="t('account.confirm_password')"
                  class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-500" />
              </div>
              <button type="submit" :disabled="profileForm.processing"
                class="w-full bg-slate-800 text-white text-sm font-medium py-2 rounded-lg hover:bg-slate-900 disabled:opacity-50 transition-colors">
                {{ t('account.save_changes') }}
              </button>
            </form>
          </div>
        </div>

        <!-- Rentals -->
        <div class="md:col-span-2">
          <h2 class="font-bold text-gray-900 text-lg mb-4">{{ t('account.my_rentals') }}</h2>

          <div v-if="orders.data?.length" class="space-y-4">
            <div v-for="order in orders.data" :key="order.id"
              class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
              <div class="flex items-start justify-between mb-3">
                <div>
                  <span class="font-semibold text-gray-900">#{{ order.order_number }}</span>
                  <span class="text-sm text-gray-400 ml-2">{{ formatDate(order.created_at) }}</span>
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
                  <div class="w-1.5 h-1.5 rounded-full bg-slate-400 shrink-0"></div>
                  <span class="text-gray-700">{{ item.product_name }}</span>
                  <span class="text-gray-400">×{{ item.quantity }} {{ t('booking.days') }}</span>
                  <span class="ml-auto text-gray-700 font-medium">{{ formatPrice(item.subtotal) }}</span>
                </div>
              </div>

              <div class="flex justify-between items-center mt-3 pt-3 border-t border-gray-100">
                <span class="text-sm text-gray-500">{{ t('account.total') }}</span>
                <div class="flex items-center gap-3">
                  <span class="font-bold text-gray-900">{{ formatPrice(order.total) }}</span>
                  <Link :href="route('account.orders.show', order.id)"
                    class="text-xs text-slate-700 hover:text-slate-900 font-medium">
                    {{ t('account.view') }}
                  </Link>
                </div>
              </div>
            </div>
          </div>

          <div v-else class="text-center py-12 text-gray-400">
            <svg class="w-12 h-12 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
            <p class="font-medium">{{ t('account.no_rentals') }}</p>
            <Link :href="route('shop')" class="mt-3 inline-block text-sm text-slate-700 hover:underline">
              {{ t('account.find_car') }}
            </Link>
          </div>

          <div v-if="orders.last_page > 1" class="mt-6 flex justify-center gap-2">
            <a v-for="link in orders.links" :key="link.label" :href="link.url ?? '#'"
              :class="['px-3 py-2 rounded-lg text-sm border', link.active
                ? 'bg-slate-800 text-white border-slate-800'
                : 'bg-white text-gray-600 border-gray-200']"
              v-html="link.label" />
          </div>
        </div>
      </div>

      <!-- Saved Items -->
      <div v-if="$page.props.saved_items?.length" class="mt-8 bg-white rounded-2xl border border-slate-100 shadow-sm">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
          <h2 class="text-lg font-semibold text-gray-900">Saved for Later</h2>
          <span class="text-sm text-gray-400">{{ $page.props.saved_items.length }} items</span>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 p-6">
          <div v-for="saved in $page.props.saved_items" :key="saved.id"
            class="flex gap-3 border border-slate-100 rounded-xl p-3">
            <img :src="saved.saveable?.image_url ?? '/images/product-placeholder.svg'"
              :alt="saved.saveable?.name" class="w-16 h-16 rounded-xl object-cover shrink-0" />
            <div class="flex-1 min-w-0">
              <div class="font-medium text-sm text-gray-900 truncate">{{ saved.saveable?.name }}</div>
              <div class="text-sm font-bold text-slate-700 mt-0.5">{{ formatPrice(saved.saveable?.price ?? 0) }}</div>
              <div class="flex items-center gap-3 mt-1">
                <Link :href="route('cart.add')" method="post"
                  :data="{ product_id: saved.saveable_id, quantity: 1 }" as="button"
                  class="text-xs text-slate-700 hover:text-slate-900 font-medium cursor-pointer">
                  Move to Cart
                </Link>
                <Link :href="route('saved-items.destroy', saved.id)" method="delete" as="button"
                  class="text-xs text-gray-400 hover:text-red-500 cursor-pointer">
                  Remove
                </Link>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Referral Program -->
      <div v-if="user?.referral_code" class="mt-8 bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-1">Refer a Friend</h2>
        <p class="text-sm text-gray-500 mb-4">Share your link and earn 100 points for every friend who signs up.</p>
        <div class="flex items-center gap-2">
          <input
            :value="referralUrl"
            readonly
            class="flex-1 rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700 bg-gray-50 focus:outline-none"
          />
          <button
            @click="copyReferralCode"
            class="shrink-0 bg-slate-800 text-white text-sm font-medium px-4 py-2 rounded-lg hover:bg-slate-900 transition-colors"
          >
            {{ copied ? 'Copied!' : 'Copy' }}
          </button>
        </div>
        <p class="text-xs text-gray-400 mt-2">Your code: <span class="font-mono font-semibold text-gray-600">{{ user.referral_code }}</span></p>
      </div>
    </div>
  </Layout>
</template>

<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import Layout from '../Layout.vue'
import { useI18n } from '@/composables/useI18n'

const { t } = useI18n()
const route = window.route
const props = defineProps({ user: Object, orders: Object, addresses: Array })
const page = usePage()
const initials = computed(() => props.user.name.split(' ').map(w => w[0]).join('').toUpperCase().slice(0, 2))

const editingProfile = ref(false)
const profileForm = useForm({
  name: props.user?.name ?? '',
  email: props.user?.email ?? '',
  current_password: '',
  password: '',
  password_confirmation: '',
})

function submitProfile() {
  profileForm.put(route('account.profile.update'), {
    preserveScroll: true,
    onSuccess: () => {
      editingProfile.value = false
      profileForm.reset('current_password', 'password', 'password_confirmation')
    },
  })
}

const addingAddress = ref(false)
const addressForm = useForm({
  label: 'Home', name: props.user?.name ?? '', email: '', phone: '',
  line1: '', line2: '', city: '', state: '', zip: '', country: 'GR', is_default: false,
})

function submitAddress() {
  addressForm.post(route('account.addresses.store'), {
    preserveScroll: true,
    onSuccess: () => { addingAddress.value = false; addressForm.reset() },
  })
}

function deleteAddress(id) {
  if (!confirm('Remove this address?')) { return }
  useForm({}).delete(route('account.addresses.destroy', id), { preserveScroll: true })
}

const copied = ref(false)
const referralUrl = computed(() => `${window.location.origin}/?ref=${props.user?.referral_code ?? ''}`)

function copyReferralCode() {
  navigator.clipboard.writeText(referralUrl.value).then(() => {
    copied.value = true
    setTimeout(() => { copied.value = false }, 2000)
  })
}

function formatDate(dateStr) {
  if (!dateStr) { return '' }
  return new Intl.DateTimeFormat(page.props.locale === 'el' ? 'el-GR' : 'en-GB', {
    day: 'numeric', month: 'short', year: 'numeric',
    hour: '2-digit', minute: '2-digit',
  }).format(new Date(dateStr))
}

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
    paid: 'bg-slate-100 text-slate-700',
    delivered: 'bg-slate-100 text-slate-700',
    cancelled: 'bg-red-100 text-red-700',
    refunded: 'bg-gray-100 text-gray-600',
  }
  return map[status] ?? 'bg-gray-100 text-gray-600'
}

function paymentClass(status) {
  return status === 'paid' ? 'bg-slate-100 text-slate-700' : 'bg-orange-100 text-orange-700'
}
</script>
