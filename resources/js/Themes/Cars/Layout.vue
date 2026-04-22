<template>
  <div class="min-h-screen bg-slate-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm border-b border-slate-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
          <Link :href="route('home')" class="text-xl font-bold text-slate-800">
            {{ $page.props.site_name }}
          </Link>

          <div class="hidden md:flex items-center gap-6">
            <Link :href="route('shop')" class="text-gray-600 hover:text-slate-800 transition-colors">{{ t('nav.browse') }}</Link>
            <Link :href="route('blog.index')" class="text-gray-600 hover:text-slate-800 transition-colors">{{ t('nav.blog') }}</Link>
          </div>

          <div class="flex items-center gap-4">
            <!-- Search -->
            <form @submit.prevent="search" class="hidden md:flex items-center">
              <div class="relative">
                <input
                  v-model="searchQuery"
                  type="text"
                  :placeholder="t('nav.search_placeholder')"
                  class="w-48 pl-9 pr-3 py-1.5 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-400 bg-slate-50"
                />
                <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
              </div>
            </form>

            <Link :href="route('cart.index')" class="relative text-gray-600 hover:text-slate-800">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
              </svg>
              <span v-if="$page.props.cart_item_count > 0"
                class="absolute -top-2 -right-2 bg-slate-700 text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center">
                {{ $page.props.cart_item_count > 99 ? '99+' : $page.props.cart_item_count }}
              </span>
            </Link>

            <!-- Locale switcher -->
            <div class="flex items-center gap-1 text-xs cursor-pointer">
              <button @click="setLocale('en')"
                :class="$page.props.locale === 'en' ? 'font-bold text-slate-800' : 'text-gray-400 hover:text-gray-600'"
                class="px-1 py-0.5 rounded cursor-pointer">EN</button>
              <span class="text-gray-300">|</span>
              <button @click="setLocale('el')"
                :class="$page.props.locale === 'el' ? 'font-bold text-slate-800' : 'text-gray-400 hover:text-gray-600'"
                class="px-1 py-0.5 rounded cursor-pointer">ΕΛ</button>
            </div>

            <template v-if="$page.props.auth.user">
              <Link :href="route('account.index')" class="text-gray-600 hover:text-slate-800 text-sm">
                {{ $page.props.auth.user.name }}
              </Link>
            </template>
            <template v-else>
              <Link :href="route('login')" class="text-gray-600 hover:text-slate-800 text-sm">{{ t('nav.login') }}</Link>
              <Link :href="route('register')"
                class="bg-slate-800 text-white px-4 py-2 rounded-lg text-sm hover:bg-slate-700 transition-colors">
                {{ t('nav.sign_up') }}
              </Link>
            </template>
          </div>
        </div>
      </div>
    </nav>

    <!-- Flash messages -->
    <div v-if="$page.props.flash.success || $page.props.flash.error" class="max-w-7xl mx-auto px-4 pt-4">
      <div v-if="$page.props.flash.success"
        class="bg-slate-50 border border-slate-200 text-slate-800 rounded-lg px-4 py-3 text-sm">
        {{ $page.props.flash.success }}
      </div>
      <div v-if="$page.props.flash.error"
        class="bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 text-sm">
        {{ $page.props.flash.error }}
      </div>
    </div>

    <main>
      <slot />
    </main>

    <footer class="bg-white border-t border-slate-200 mt-16">
      <div class="max-w-7xl mx-auto px-4 py-8 text-center text-sm text-gray-500">
        © {{ new Date().getFullYear() }} {{ $page.props.site_name }}. {{ t('nav.footer_cars') }}
      </div>
    </footer>
  </div>
  <CartSuccessModal />
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import { useI18n } from '@/composables/useI18n'
import CartSuccessModal from '@/components/CartSuccessModal.vue'

const { t } = useI18n()
const route = window.route
const searchQuery = ref('')

function search() {
  if (!searchQuery.value.trim()) return
  router.get(route('shop'), { search: searchQuery.value.trim() }, { preserveState: false })
}

function setLocale(locale) {
  router.post(route('locale.set'), { locale }, { preserveState: false })
}
</script>
