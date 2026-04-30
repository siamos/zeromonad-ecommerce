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

          <!-- Flash success -->
          <div v-if="$page.props.flash?.success"
            class="bg-green-50 border border-green-200 text-green-700 text-sm rounded-xl px-4 py-3">
            {{ $page.props.flash.success }}
          </div>

          <!-- Account details -->
          <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
            <div class="flex items-center justify-between mb-4">
              <h2 class="text-lg font-semibold text-gray-900">{{ t('account.account_details') }}</h2>
              <button @click="editingProfile = !editingProfile"
                class="text-sm text-indigo-600 hover:underline">
                {{ editingProfile ? t('account.cancel') : t('account.edit') }}
              </button>
            </div>

            <form v-if="editingProfile" @submit.prevent="submitProfile" class="space-y-4">
              <div>
                <label class="block text-sm text-gray-500 mb-1">{{ t('account.name') }}</label>
                <input v-model="profileForm.name" type="text"
                  class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                <p v-if="profileForm.errors.name" class="text-red-500 text-xs mt-1">{{ profileForm.errors.name }}</p>
              </div>
              <div>
                <label class="block text-sm text-gray-500 mb-1">{{ t('account.email') }}</label>
                <input v-model="profileForm.email" type="email"
                  class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                <p v-if="profileForm.errors.email" class="text-red-500 text-xs mt-1">{{ profileForm.errors.email }}</p>
              </div>
              <div class="border-t border-gray-100 pt-4">
                <p class="text-sm text-gray-400 mb-3">{{ t('account.change_password_optional') }}</p>
                <div class="space-y-3">
                  <div>
                    <input v-model="profileForm.current_password" type="password"
                      :placeholder="t('account.current_password')"
                      class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                    <p v-if="profileForm.errors.current_password" class="text-red-500 text-xs mt-1">{{ profileForm.errors.current_password }}</p>
                  </div>
                  <input v-model="profileForm.password" type="password"
                    :placeholder="t('account.new_password')"
                    class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                  <input v-model="profileForm.password_confirmation" type="password"
                    :placeholder="t('account.confirm_password')"
                    class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                </div>
              </div>
              <button type="submit" :disabled="profileForm.processing"
                class="bg-indigo-600 text-white text-sm font-medium px-5 py-2 rounded-lg hover:bg-indigo-700 disabled:opacity-50 transition-colors">
                {{ t('account.save_changes') }}
              </button>
            </form>

            <dl v-else class="space-y-4">
              <div>
                <dt class="text-sm text-gray-500">{{ t('account.name') }}</dt>
                <dd class="font-medium text-gray-900">{{ user.name }}</dd>
              </div>
              <div>
                <dt class="text-sm text-gray-500">{{ t('account.email') }}</dt>
                <dd class="font-medium text-gray-900">{{ user.email }}</dd>
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

          <!-- Saved Items -->
          <div v-if="$page.props.saved_items?.length" class="bg-white rounded-xl shadow-sm border border-gray-100">
            <div class="p-6 border-b border-gray-100 flex items-center justify-between">
              <h2 class="text-lg font-semibold text-gray-900">Saved for Later</h2>
              <span class="text-sm text-gray-400">{{ $page.props.saved_items.length }} items</span>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 p-6">
              <div v-for="saved in $page.props.saved_items" :key="saved.id"
                class="flex gap-3 border border-gray-100 rounded-xl p-3">
                <img :src="saved.saveable?.image_url ?? '/images/product-placeholder.svg'"
                  :alt="saved.saveable?.name" class="w-16 h-16 rounded-lg object-cover shrink-0" />
                <div class="flex-1 min-w-0">
                  <div class="font-medium text-sm text-gray-900 truncate">{{ saved.saveable?.name }}</div>
                  <div class="text-sm font-bold text-indigo-600 mt-0.5">{{ formatPrice(saved.saveable?.price ?? 0) }}</div>
                  <div class="flex items-center gap-3 mt-1">
                    <Link :href="route('cart.add')" method="post"
                      :data="{ product_id: saved.saveable_id, quantity: 1 }" as="button"
                      class="text-xs text-indigo-600 hover:text-indigo-800 font-medium cursor-pointer">
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

          <!-- Address Book -->
          <div class="bg-white rounded-xl shadow-sm border border-gray-100">
            <div class="p-6 border-b border-gray-100 flex items-center justify-between">
              <h2 class="text-lg font-semibold text-gray-900">{{ t('account.address_book') }}</h2>
              <button @click="addingAddress = !addingAddress" class="text-sm text-indigo-600 hover:underline">
                {{ addingAddress ? t('account.cancel') : t('account.add_address') }}
              </button>
            </div>

            <form v-if="addingAddress" @submit.prevent="submitAddress" class="p-6 space-y-3 border-b border-gray-100">
              <div class="grid grid-cols-2 gap-3">
                <div>
                  <input v-model="addressForm.label" type="text" :placeholder="t('account.address_label')"
                    class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                  <p v-if="addressForm.errors.label" class="text-red-500 text-xs mt-1">{{ addressForm.errors.label }}</p>
                </div>
                <div>
                  <input v-model="addressForm.name" type="text" :placeholder="t('account.address_full_name')"
                    class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                  <p v-if="addressForm.errors.name" class="text-red-500 text-xs mt-1">{{ addressForm.errors.name }}</p>
                </div>
              </div>
              <input v-model="addressForm.line1" type="text" :placeholder="t('account.address_line1')"
                class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
              <p v-if="addressForm.errors.line1" class="text-red-500 text-xs -mt-2">{{ addressForm.errors.line1 }}</p>
              <input v-model="addressForm.line2" type="text" :placeholder="t('account.address_line2')"
                class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
              <div class="grid grid-cols-3 gap-3">
                <div>
                  <input v-model="addressForm.city" type="text" :placeholder="t('account.address_city')"
                    class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                  <p v-if="addressForm.errors.city" class="text-red-500 text-xs mt-1">{{ addressForm.errors.city }}</p>
                </div>
                <div>
                  <input v-model="addressForm.zip" type="text" :placeholder="t('account.address_zip')"
                    class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                  <p v-if="addressForm.errors.zip" class="text-red-500 text-xs mt-1">{{ addressForm.errors.zip }}</p>
                </div>
                <input v-model="addressForm.country" type="text" :placeholder="t('account.address_country')"
                  class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
              </div>
              <label class="flex items-center gap-2 text-sm text-gray-600">
                <input v-model="addressForm.is_default" type="checkbox" class="rounded" />
                {{ t('account.set_as_default') }}
              </label>
              <button type="submit" :disabled="addressForm.processing"
                class="bg-indigo-600 text-white text-sm font-medium px-5 py-2 rounded-lg hover:bg-indigo-700 disabled:opacity-50 transition-colors">
                {{ t('account.save_address') }}
              </button>
            </form>

            <div v-if="addresses?.length" class="divide-y divide-gray-100">
              <div v-for="addr in addresses" :key="addr.id" class="p-4 flex items-start justify-between gap-4">
                <div class="text-sm">
                  <div class="flex items-center gap-2">
                    <span class="font-medium text-gray-900">{{ addr.label }}</span>
                    <span v-if="addr.is_default" class="text-xs bg-indigo-50 text-indigo-600 px-2 py-0.5 rounded-full">{{ t('account.default') }}</span>
                  </div>
                  <p class="text-gray-600 mt-1">{{ addr.name }}</p>
                  <p class="text-gray-500">{{ addr.line1 }}</p>
                  <p class="text-gray-500">{{ addr.city }}, {{ addr.zip }} {{ addr.country }}</p>
                </div>
                <button @click="deleteAddress(addr.id)"
                  class="text-xs text-red-400 hover:text-red-600 shrink-0 mt-1">
                  {{ t('account.remove') }}
                </button>
              </div>
            </div>
            <p v-else-if="!addingAddress" class="text-center text-sm text-gray-400 py-6">{{ t('account.no_addresses') }}</p>
          </div>

          <!-- Referral Program -->
          <div v-if="user?.referral_code" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
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
                class="shrink-0 bg-indigo-600 text-white text-sm font-medium px-4 py-2 rounded-lg hover:bg-indigo-700 transition-colors"
              >
                {{ copied ? 'Copied!' : 'Copy' }}
              </button>
            </div>
            <p class="text-xs text-gray-400 mt-2">Your code: <span class="font-mono font-semibold text-gray-600">{{ user.referral_code }}</span></p>
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
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import Layout from '../Layout.vue'
import { useI18n } from '@/composables/useI18n'

const { t } = useI18n()
const route = window.route
const props = defineProps({ wishlistItems: Array, user: Object, pointsHistory: Array, addresses: Array })
const page = usePage()

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
  label: 'Home',
  name: props.user?.name ?? '',
  email: '',
  phone: '',
  line1: '',
  line2: '',
  city: '',
  state: '',
  zip: '',
  country: 'GR',
  is_default: false,
})

function submitAddress() {
  addressForm.post(route('account.addresses.store'), {
    preserveScroll: true,
    onSuccess: () => {
      addingAddress.value = false
      addressForm.reset()
    },
  })
}

function deleteAddress(id) {
  if (!confirm('Remove this address?')) { return }
  useForm({}).delete(route('account.addresses.destroy', id), { preserveScroll: true })
}

function formatPrice(price) {
  return new Intl.NumberFormat('el-GR', {
    style: 'currency',
    currency: page.props.currency ?? 'EUR',
  }).format(price)
}

const copied = ref(false)
const referralUrl = computed(() => `${window.location.origin}/?ref=${props.user?.referral_code ?? ''}`)

function copyReferralCode() {
  navigator.clipboard.writeText(referralUrl.value).then(() => {
    copied.value = true
    setTimeout(() => { copied.value = false }, 2000)
  })
}

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
