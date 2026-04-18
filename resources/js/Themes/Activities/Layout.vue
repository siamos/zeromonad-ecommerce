<template>
  <div class="min-h-screen bg-stone-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm border-b border-stone-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
          <Link :href="route('home')" class="text-xl font-bold text-emerald-600">
            {{ $page.props.site_name }}
          </Link>

          <div class="hidden md:flex items-center gap-6">
            <Link :href="route('shop')" class="text-gray-600 hover:text-emerald-600 transition-colors">Activities</Link>
            <Link :href="route('blog.index')" class="text-gray-600 hover:text-emerald-600 transition-colors">Blog</Link>
          </div>

          <div class="flex items-center gap-4">
            <!-- Search -->
            <form @submit.prevent="search" class="hidden md:flex items-center">
              <div class="relative">
                <input
                  v-model="searchQuery"
                  type="text"
                  placeholder="Search activities…"
                  class="w-48 pl-9 pr-3 py-1.5 text-sm border border-stone-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-300 bg-stone-50"
                />
                <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
              </div>
            </form>
            <Link :href="route('cart.index')" class="relative text-gray-600 hover:text-emerald-600">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
            </Link>
            <template v-if="$page.props.auth.user">
              <Link :href="route('account.index')" class="text-gray-600 hover:text-emerald-600 text-sm">
                {{ $page.props.auth.user.name }}
              </Link>
            </template>
            <template v-else>
              <Link :href="route('login')" class="text-gray-600 hover:text-emerald-600 text-sm">Login</Link>
              <Link :href="route('register')"
                class="bg-emerald-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-emerald-700 transition-colors">
                Sign Up
              </Link>
            </template>
          </div>
        </div>
      </div>
    </nav>

    <!-- Flash messages -->
    <div v-if="$page.props.flash.success || $page.props.flash.error" class="max-w-7xl mx-auto px-4 pt-4">
      <div v-if="$page.props.flash.success"
        class="bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg px-4 py-3 text-sm">
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

    <footer class="bg-white border-t border-stone-200 mt-16">
      <div class="max-w-7xl mx-auto px-4 py-8 text-center text-sm text-gray-500">
        © {{ new Date().getFullYear() }} {{ $page.props.site_name }}. Adventure awaits.
      </div>
    </footer>
  </div>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3'
import { ref } from 'vue'

const route = window.route
const searchQuery = ref('')

function search() {
  if (!searchQuery.value.trim()) return
  router.get(route('shop'), { search: searchQuery.value.trim() }, { preserveState: false })
}
</script>
