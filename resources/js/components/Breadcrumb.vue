<template>
  <nav aria-label="Breadcrumb" class="mb-4">
    <ol class="flex items-center flex-wrap gap-1 text-sm">
      <li v-for="(item, index) in items" :key="index" class="flex items-center gap-1">
        <span v-if="index > 0" class="text-gray-300">/</span>
        <Link v-if="item.href" :href="item.href" class="text-gray-400 hover:text-gray-600 transition-colors">
          {{ item.label }}
        </Link>
        <span v-else class="text-gray-700 font-medium">{{ item.label }}</span>
      </li>
    </ol>
  </nav>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'

defineProps({
  items: {
    type: Array,
    required: true,
  },
})
</script>
