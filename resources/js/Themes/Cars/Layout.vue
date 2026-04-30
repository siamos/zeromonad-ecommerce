<template>
  <div class="min-h-screen flex flex-col bg-slate-50" :class="'palette-' + ($page.props.theme_palette ?? 'slate')">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm border-b border-slate-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
          <Link :href="route('home')" class="flex items-center gap-2 shrink-0">
            <img v-if="$page.props.site_logo_url" :src="$page.props.site_logo_url" :alt="$page.props.site_name" class="h-8 w-auto max-w-[160px] object-contain" />
            <span v-else class="text-xl font-bold text-slate-800">{{ $page.props.site_name }}</span>
          </Link>

          <div class="hidden md:flex items-center gap-6">
            <Link :href="route('shop')" class="text-gray-600 hover:text-slate-800 transition-colors">{{ t('nav.browse') }}</Link>
            <Link :href="route('blog.index')" class="text-gray-600 hover:text-slate-800 transition-colors">{{ t('nav.blog') }}</Link>
          </div>

          <div class="flex items-center gap-3">
            <!-- Desktop: Search + Locale + Auth -->
            <div class="hidden md:flex items-center gap-4">
              <SearchAutocomplete
                input-class="w-48 pl-9 pr-3 py-1.5 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-400 bg-slate-50"
                price-class="text-slate-700"
                see-all-class="text-slate-700 hover:text-slate-900"
              />
              <div class="flex items-center gap-1 text-xs">
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
                <Link :href="route('register')" class="bg-slate-800 text-white px-4 py-2 rounded-lg text-sm hover:bg-slate-700 transition-colors">
                  {{ t('nav.sign_up') }}
                </Link>
              </template>
            </div>
            <!-- Notification Bell -->
            <div v-if="$page.props.auth.user" class="relative hidden md:block">
              <button @click="notifOpen = !notifOpen" class="relative text-gray-600 hover:text-slate-800">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
                <span v-if="$page.props.unread_notifications_count > 0"
                  class="absolute -top-2 -right-2 bg-red-500 text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center">
                  {{ $page.props.unread_notifications_count > 9 ? '9+' : $page.props.unread_notifications_count }}
                </span>
              </button>
              <Teleport to="body">
                <div v-if="notifOpen" class="fixed inset-0 z-30" @click="notifOpen = false" />
                <div v-if="notifOpen" class="fixed top-16 right-16 z-40 w-80 bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
                  <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
                    <span class="font-semibold text-sm text-gray-900">Notifications</span>
                    <button v-if="$page.props.unread_notifications_count > 0" @click="markAllRead"
                      class="text-xs text-slate-700 hover:text-slate-900 cursor-pointer">
                      Mark all read
                    </button>
                  </div>
                  <div v-if="$page.props.recent_notifications?.length" class="divide-y divide-gray-50 max-h-80 overflow-y-auto">
                    <button v-for="notif in $page.props.recent_notifications" :key="notif.id"
                      @click="markRead(notif.id, notif.data?.url)"
                      :class="['w-full text-left px-4 py-3 hover:bg-gray-50 text-sm cursor-pointer', !notif.read_at ? 'bg-slate-50/60' : '']">
                      <div :class="['font-medium text-gray-900 truncate', !notif.read_at ? 'text-slate-900' : '']">{{ notif.data?.title }}</div>
                      <div class="text-gray-500 text-xs mt-0.5 line-clamp-2">{{ notif.data?.body }}</div>
                      <div class="text-gray-400 text-xs mt-1">{{ formatNotifDate(notif.created_at) }}</div>
                    </button>
                  </div>
                  <div v-else class="px-4 py-8 text-center text-sm text-gray-400">No notifications yet</div>
                </div>
              </Teleport>
            </div>
            <!-- Cart -->
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
            <!-- Mobile hamburger -->
            <button @click="mobileOpen = true" class="md:hidden p-1 text-gray-600 hover:text-slate-800">
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
          <div class="flex items-center justify-between h-16 px-4 border-b border-slate-200">
            <Link :href="route('home')" @click="mobileOpen = false" class="flex items-center gap-2">
              <img v-if="$page.props.site_logo_url" :src="$page.props.site_logo_url" :alt="$page.props.site_name" class="h-8 w-auto max-w-[140px] object-contain" />
              <span v-else class="text-lg font-bold text-slate-800">{{ $page.props.site_name }}</span>
            </Link>
            <button @click="mobileOpen = false" class="p-1 text-gray-400 hover:text-gray-600">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
          <nav class="flex-1 overflow-y-auto px-4 py-6">
            <div class="space-y-1">
              <Link :href="route('home')" @click="mobileOpen = false" class="block px-3 py-2 rounded-lg text-gray-700 hover:bg-slate-100 hover:text-slate-800 transition-colors">{{ t('footer.home') }}</Link>
              <Link :href="route('shop')" @click="mobileOpen = false" class="block px-3 py-2 rounded-lg text-gray-700 hover:bg-slate-100 hover:text-slate-800 transition-colors">{{ t('nav.browse') }}</Link>
              <Link :href="route('blog.index')" @click="mobileOpen = false" class="block px-3 py-2 rounded-lg text-gray-700 hover:bg-slate-100 hover:text-slate-800 transition-colors">{{ t('nav.blog') }}</Link>
            </div>
          </nav>
          <div class="px-4 py-6 border-t border-slate-200 space-y-3">
            <template v-if="$page.props.auth.user">
              <Link :href="route('account.index')" @click="mobileOpen = false" class="block px-3 py-2 rounded-lg text-gray-700 hover:bg-slate-100 hover:text-slate-800 text-sm transition-colors">
                {{ $page.props.auth.user.name }}
              </Link>
            </template>
            <template v-else>
              <Link :href="route('login')" @click="mobileOpen = false" class="block px-3 py-2 rounded-lg text-gray-700 hover:bg-slate-100 hover:text-slate-800 text-sm transition-colors">{{ t('nav.login') }}</Link>
              <Link :href="route('register')" @click="mobileOpen = false" class="block text-center bg-slate-800 text-white px-4 py-2 rounded-lg text-sm hover:bg-slate-700 transition-colors">{{ t('nav.sign_up') }}</Link>
            </template>
            <div class="flex items-center gap-2 text-xs pt-1">
              <button @click="setLocale('en')"
                :class="$page.props.locale === 'en' ? 'font-bold text-slate-800' : 'text-gray-400 hover:text-gray-600'"
                class="px-1 py-0.5 rounded cursor-pointer">EN</button>
              <span class="text-gray-300">|</span>
              <button @click="setLocale('el')"
                :class="$page.props.locale === 'el' ? 'font-bold text-slate-800' : 'text-gray-400 hover:text-gray-600'"
                class="px-1 py-0.5 rounded cursor-pointer">ΕΛ</button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

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

    <main class="grow">
      <slot />
    </main>

    <footer class="bg-white border-t border-slate-200 mt-16">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
          <div>
            <Link :href="route('home')" class="flex items-center gap-2 mb-3">
              <img v-if="$page.props.site_logo_url" :src="$page.props.site_logo_url" :alt="$page.props.site_name" class="h-8 w-auto max-w-[160px] object-contain" />
              <span v-else class="text-lg font-bold text-slate-800">{{ $page.props.site_name }}</span>
            </Link>
            <p class="text-sm text-gray-500 leading-relaxed">{{ $page.props.site_description || t('nav.footer_cars') }}</p>
          </div>
          <div>
            <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-4">{{ t('footer.quick_links') }}</h3>
            <ul class="space-y-2">
              <li><Link :href="route('home')" class="text-sm text-gray-500 hover:text-slate-800 transition-colors">{{ t('footer.home') }}</Link></li>
              <li><Link :href="route('shop')" class="text-sm text-gray-500 hover:text-slate-800 transition-colors">{{ t('nav.browse') }}</Link></li>
              <li><Link :href="route('blog.index')" class="text-sm text-gray-500 hover:text-slate-800 transition-colors">{{ t('nav.blog') }}</Link></li>
            </ul>
          </div>
          <div>
            <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-4">{{ t('footer.account') }}</h3>
            <ul class="space-y-2">
              <template v-if="$page.props.auth.user">
                <li><Link :href="route('account.index')" class="text-sm text-gray-500 hover:text-slate-800 transition-colors">{{ t('footer.my_account') }}</Link></li>
              </template>
              <template v-else>
                <li><Link :href="route('login')" class="text-sm text-gray-500 hover:text-slate-800 transition-colors">{{ t('nav.login') }}</Link></li>
                <li><Link :href="route('register')" class="text-sm text-gray-500 hover:text-slate-800 transition-colors">{{ t('footer.register') }}</Link></li>
              </template>
            </ul>
          </div>
        </div>
        <div class="border-t border-slate-200 mt-8 pt-6 text-center text-sm text-gray-400">
          © {{ new Date().getFullYear() }} {{ $page.props.site_name }}. {{ t('footer.rights') }}
        </div>
      </div>
    </footer>
  </div>
  <CartSuccessModal />
  <PromotionPopup />
  <CompareBar />
</template>

<script setup>
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { useI18n } from '@/composables/useI18n'
import CartSuccessModal from '@/components/CartSuccessModal.vue'
import PromotionPopup from '@/components/PromotionPopup.vue'
import CompareBar from '@/components/CompareBar.vue'
import SearchAutocomplete from '@/components/SearchAutocomplete.vue'

const { t } = useI18n()
const route = window.route
const mobileOpen = ref(false)
const notifOpen = ref(false)

function setLocale(locale) {
  router.post(route('locale.set'), { locale }, { preserveState: false })
}

function formatNotifDate(dateStr) {
  if (!dateStr) { return '' }
  const diff = Math.floor((Date.now() - new Date(dateStr)) / 60000)
  if (diff < 60) { return `${diff}m ago` }
  if (diff < 1440) { return `${Math.floor(diff / 60)}h ago` }
  return `${Math.floor(diff / 1440)}d ago`
}

function markRead(id, url) {
  router.post(route('notifications.read', id), {}, {
    preserveScroll: true,
    onSuccess: () => {
      notifOpen.value = false
      if (url) { window.location.href = url }
    },
  })
}

function markAllRead() {
  router.post(route('notifications.read-all'), {}, { preserveScroll: true })
  notifOpen.value = false
}
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
.slide-left-enter-active, .slide-left-leave-active { transition: transform 0.25s ease; }
.slide-left-enter-from, .slide-left-leave-to { transform: translateX(-100%); }
</style>
