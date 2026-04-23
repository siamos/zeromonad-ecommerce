<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
          <Link :href="route('home')" class="flex items-center gap-2 shrink-0">
            <img v-if="$page.props.site_logo_url" :src="$page.props.site_logo_url" :alt="$page.props.site_name" class="h-8 w-auto max-w-[160px] object-contain" />
            <span v-else class="text-xl font-bold text-indigo-600">{{ $page.props.site_name }}</span>
          </Link>

          <!-- Nav links -->
          <div class="hidden md:flex items-center gap-6">
            <Link :href="route('shop')" class="text-gray-600 hover:text-indigo-600 transition-colors">{{ t('nav.shop') }}</Link>
            <Link :href="route('blog.index')" class="text-gray-600 hover:text-indigo-600 transition-colors">{{ t('nav.blog') }}</Link>
          </div>

          <!-- Right side -->
          <div class="flex items-center gap-3">
            <!-- Desktop: Search + Locale + Auth -->
            <div class="hidden md:flex items-center gap-4">
              <SearchAutocomplete
                input-class="w-48 pl-9 pr-3 py-1.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-gray-50"
                price-class="text-indigo-600"
                see-all-class="text-indigo-600 hover:text-indigo-700"
              />
              <div class="flex items-center gap-1 text-xs">
                <button @click="setLocale('en')"
                  :class="$page.props.locale === 'en' ? 'font-bold text-indigo-700' : 'text-gray-400 hover:text-gray-600'"
                  class="px-1 py-0.5 rounded cursor-pointer">EN</button>
                <span class="text-gray-300">|</span>
                <button @click="setLocale('el')"
                  :class="$page.props.locale === 'el' ? 'font-bold text-indigo-700' : 'text-gray-400 hover:text-gray-600'"
                  class="px-1 py-0.5 rounded cursor-pointer">ΕΛ</button>
              </div>
              <template v-if="$page.props.auth.user">
                <Link :href="route('account.index')" class="text-gray-600 hover:text-indigo-600 text-sm">
                  {{ $page.props.auth.user.name }}
                </Link>
              </template>
              <template v-else>
                <Link :href="route('login')" class="text-gray-600 hover:text-indigo-600 text-sm">{{ t('nav.login') }}</Link>
                <Link :href="route('register')" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700 transition-colors">
                  {{ t('nav.sign_up') }}
                </Link>
              </template>
            </div>
            <!-- Cart -->
            <Link :href="route('cart.index')" class="relative text-gray-600 hover:text-indigo-600">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
              </svg>
              <span v-if="$page.props.cart_item_count > 0"
                class="absolute -top-2 -right-2 bg-indigo-600 text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center">
                {{ $page.props.cart_item_count > 99 ? '99+' : $page.props.cart_item_count }}
              </span>
            </Link>
            <!-- Mobile hamburger -->
            <button @click="mobileOpen = true" class="md:hidden p-1 text-gray-600 hover:text-indigo-600">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
              </svg>
            </button>
          </div>
        </div>
      </div>
    </nav>

    <!-- Mobile drawer -->
    <Teleport to="body">
      <Transition name="fade">
        <div v-if="mobileOpen" class="fixed inset-0 bg-black/50 z-40 md:hidden" @click="mobileOpen = false" />
      </Transition>
      <Transition name="slide-left">
        <div v-if="mobileOpen" class="fixed inset-y-0 left-0 w-72 bg-white z-50 md:hidden shadow-xl flex flex-col">
          <div class="flex items-center justify-between h-16 px-4 border-b border-gray-200">
            <Link :href="route('home')" @click="mobileOpen = false" class="flex items-center gap-2">
              <img v-if="$page.props.site_logo_url" :src="$page.props.site_logo_url" :alt="$page.props.site_name" class="h-8 w-auto max-w-[140px] object-contain" />
              <span v-else class="text-lg font-bold text-indigo-600">{{ $page.props.site_name }}</span>
            </Link>
            <button @click="mobileOpen = false" class="p-1 text-gray-400 hover:text-gray-600">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
          <nav class="flex-1 overflow-y-auto px-4 py-6">
            <div class="space-y-1">
              <Link :href="route('home')" @click="mobileOpen = false" class="block px-3 py-2 rounded-lg text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition-colors">{{ t('footer.home') }}</Link>
              <Link :href="route('shop')" @click="mobileOpen = false" class="block px-3 py-2 rounded-lg text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition-colors">{{ t('nav.shop') }}</Link>
              <Link :href="route('blog.index')" @click="mobileOpen = false" class="block px-3 py-2 rounded-lg text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition-colors">{{ t('nav.blog') }}</Link>
            </div>
          </nav>
          <div class="px-4 py-6 border-t border-gray-200 space-y-3">
            <template v-if="$page.props.auth.user">
              <Link :href="route('account.index')" @click="mobileOpen = false" class="block px-3 py-2 rounded-lg text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 text-sm transition-colors">
                {{ $page.props.auth.user.name }}
              </Link>
            </template>
            <template v-else>
              <Link :href="route('login')" @click="mobileOpen = false" class="block px-3 py-2 rounded-lg text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 text-sm transition-colors">{{ t('nav.login') }}</Link>
              <Link :href="route('register')" @click="mobileOpen = false" class="block text-center bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700 transition-colors">{{ t('nav.sign_up') }}</Link>
            </template>
            <div class="flex items-center gap-2 text-xs pt-1">
              <button @click="setLocale('en')"
                :class="$page.props.locale === 'en' ? 'font-bold text-indigo-700' : 'text-gray-400 hover:text-gray-600'"
                class="px-1 py-0.5 rounded cursor-pointer">EN</button>
              <span class="text-gray-300">|</span>
              <button @click="setLocale('el')"
                :class="$page.props.locale === 'el' ? 'font-bold text-indigo-700' : 'text-gray-400 hover:text-gray-600'"
                class="px-1 py-0.5 rounded cursor-pointer">ΕΛ</button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- Flash messages -->
    <div v-if="$page.props.flash.success || $page.props.flash.error" class="max-w-7xl mx-auto px-4 pt-4">
      <div v-if="$page.props.flash.success"
        class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm">
        {{ $page.props.flash.success }}
      </div>
      <div v-if="$page.props.flash.error"
        class="bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 text-sm">
        {{ $page.props.flash.error }}
      </div>
    </div>

    <!-- Page content -->
    <main>
      <slot />
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 mt-16">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
          <div>
            <Link :href="route('home')" class="flex items-center gap-2 mb-3">
              <img v-if="$page.props.site_logo_url" :src="$page.props.site_logo_url" :alt="$page.props.site_name" class="h-8 w-auto max-w-[160px] object-contain" />
              <span v-else class="text-lg font-bold text-indigo-600">{{ $page.props.site_name }}</span>
            </Link>
            <p class="text-sm text-gray-500 leading-relaxed">{{ $page.props.site_description || t('nav.footer_products') }}</p>
          </div>
          <div>
            <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-4">{{ t('footer.quick_links') }}</h3>
            <ul class="space-y-2">
              <li><Link :href="route('home')" class="text-sm text-gray-500 hover:text-indigo-600 transition-colors">{{ t('footer.home') }}</Link></li>
              <li><Link :href="route('shop')" class="text-sm text-gray-500 hover:text-indigo-600 transition-colors">{{ t('nav.shop') }}</Link></li>
              <li><Link :href="route('blog.index')" class="text-sm text-gray-500 hover:text-indigo-600 transition-colors">{{ t('nav.blog') }}</Link></li>
            </ul>
          </div>
          <div>
            <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-4">{{ t('footer.account') }}</h3>
            <ul class="space-y-2">
              <template v-if="$page.props.auth.user">
                <li><Link :href="route('account.index')" class="text-sm text-gray-500 hover:text-indigo-600 transition-colors">{{ t('footer.my_account') }}</Link></li>
              </template>
              <template v-else>
                <li><Link :href="route('login')" class="text-sm text-gray-500 hover:text-indigo-600 transition-colors">{{ t('nav.login') }}</Link></li>
                <li><Link :href="route('register')" class="text-sm text-gray-500 hover:text-indigo-600 transition-colors">{{ t('footer.register') }}</Link></li>
              </template>
            </ul>
          </div>
        </div>
        <div class="border-t border-gray-200 mt-8 pt-6 text-center text-sm text-gray-400">
          © {{ new Date().getFullYear() }} {{ $page.props.site_name }}. {{ t('footer.rights') }}
        </div>
      </div>
    </footer>
  </div>
  <CartSuccessModal />
</template>

<script setup>
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { useI18n } from '@/composables/useI18n'
import CartSuccessModal from '@/components/CartSuccessModal.vue'
import SearchAutocomplete from '@/components/SearchAutocomplete.vue'

const { t } = useI18n()
const route = window.route
const mobileOpen = ref(false)

function setLocale(locale) {
  router.post(route('locale.set'), { locale }, { preserveState: false })
}
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
.slide-left-enter-active, .slide-left-leave-active { transition: transform 0.25s ease; }
.slide-left-enter-from, .slide-left-leave-to { transform: translateX(-100%); }
</style>
