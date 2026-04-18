<template>
  <Layout>
    <Head title="Blog" />

    <div class="max-w-5xl mx-auto px-4 py-10">
      <h1 class="text-3xl font-bold text-gray-900 mb-2">Blog</h1>
      <p class="text-gray-500 mb-10">Tips, guides, and stories for adventurers.</p>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <article
          v-for="post in posts.data"
          :key="post.id"
          class="bg-white rounded-2xl overflow-hidden shadow-sm border border-stone-100 hover:shadow-md transition-shadow"
        >
          <img
            v-if="post.featured_image"
            :src="post.featured_image"
            :alt="post.title"
            class="w-full h-48 object-cover"
          />
          <div class="p-5">
            <div class="flex items-center gap-2 mb-3">
              <span v-if="post.ai_generated"
                class="text-xs bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full font-medium">
                AI Generated
              </span>
              <span class="text-xs text-gray-400">{{ post.published_at }}</span>
            </div>
            <h2 class="font-semibold text-gray-900 mb-2 line-clamp-2">
              <Link :href="route('blog.show', post.slug)" class="hover:text-emerald-600 transition-colors">
                {{ post.title }}
              </Link>
            </h2>
            <p class="text-sm text-gray-500 line-clamp-3">{{ post.excerpt }}</p>
            <Link
              :href="route('blog.show', post.slug)"
              class="mt-4 inline-block text-sm text-emerald-600 font-medium hover:underline"
            >
              Read more →
            </Link>
          </div>
        </article>
      </div>

      <div v-if="posts.last_page > 1" class="mt-10 flex justify-center gap-2">
        <Link
          v-for="link in posts.links"
          :key="link.label"
          :href="link.url ?? '#'"
          :class="['px-3 py-2 rounded-lg text-sm border', link.active
            ? 'bg-emerald-600 text-white border-emerald-600'
            : 'bg-white text-gray-600 border-gray-200']"
          v-html="link.label"
        />
      </div>
    </div>
  </Layout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3'
import Layout from '../../Layout.vue'

defineProps({ posts: Object })
</script>
