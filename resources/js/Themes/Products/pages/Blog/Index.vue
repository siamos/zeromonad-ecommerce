<template>
  <Layout>
    <Head :title="t('blog.title')" />
    <div class="max-w-5xl mx-auto px-4 py-10">
      <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ t('blog.title') }}</h1>
      <p class="text-gray-500 mb-10">{{ t('blog.subtitle_products') }}</p>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <article v-for="post in posts.data" :key="post.id"
          class="bg-white rounded-xl overflow-hidden shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
          <img v-if="post.featured_image" :src="post.featured_image" :alt="post.title"
            class="w-full h-48 object-cover" />
          <div class="p-5">
            <div class="flex items-center gap-2 mb-3">
              <span v-if="post.ai_generated"
                class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded-full font-medium">
                {{ t('blog.ai_generated') }}
              </span>
              <span class="text-xs text-gray-400">{{ post.published_at }}</span>
            </div>
            <h2 class="font-semibold text-gray-900 mb-2 line-clamp-2">
              <Link :href="route('blog.show', post.slug)" class="hover:text-indigo-600 transition-colors">
                {{ post.title }}
              </Link>
            </h2>
            <p class="text-sm text-gray-500 line-clamp-3">{{ post.excerpt }}</p>
            <Link :href="route('blog.show', post.slug)"
              class="mt-4 inline-block text-sm text-indigo-600 font-medium hover:underline">
              {{ t('blog.read_more') }}
            </Link>
          </div>
        </article>
      </div>

      <div v-if="posts.last_page > 1" class="mt-10 flex justify-center gap-2">
        <a v-for="link in posts.links" :key="link.label" :href="link.url ?? '#'"
          :class="['px-3 py-2 rounded-lg text-sm border', link.active ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-600 border-gray-200']"
          v-html="link.label" />
      </div>
    </div>
  </Layout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3'
import Layout from '../../Layout.vue'
import { useI18n } from '@/composables/useI18n'

const { t } = useI18n()
const route = window.route

defineProps({ posts: Object })
</script>
