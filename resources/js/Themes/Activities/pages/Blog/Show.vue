<template>
  <Layout>
    <Head :title="post.title" />

    <div class="max-w-3xl mx-auto px-4 py-10">
      <Link :href="route('blog.index')" class="text-sm text-emerald-600 hover:underline mb-6 block">
        ← Back to Blog
      </Link>

      <div class="flex items-center gap-2 mb-4">
        <span v-if="post.ai_generated"
          class="text-xs bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full font-medium">
          AI Generated
        </span>
        <span class="text-sm text-gray-400">{{ post.published_at }}</span>
      </div>

      <h1 class="text-4xl font-bold text-gray-900 mb-6">{{ post.title }}</h1>

      <img
        v-if="post.featured_image"
        :src="post.featured_image"
        :alt="post.title"
        class="w-full rounded-2xl mb-8 aspect-video object-cover"
      />

      <div class="prose prose-emerald max-w-none" v-html="post.content" />
    </div>
  </Layout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3'
import Layout from '../../Layout.vue'

defineProps({ post: Object })
</script>
