<template>
  <div class="bg-white rounded-2xl border border-stone-100 shadow-sm p-6">
    <h3 class="font-semibold text-gray-900 mb-4">Write a Review</h3>

    <div v-if="$page.props.flash?.success" class="mb-4 p-3 bg-emerald-50 text-emerald-700 rounded-xl text-sm">
      {{ $page.props.flash.success }}
    </div>

    <div v-if="!$page.props.auth.user" class="text-sm text-gray-500">
      <Link :href="route('login')" class="text-emerald-600 hover:underline">Sign in</Link> to leave a review.
    </div>

    <form v-else @submit.prevent="submit" class="space-y-4">
      <input type="hidden" :value="productId" name="product_id" />

      <!-- Star rating -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Rating</label>
        <div class="flex gap-1">
          <button
            v-for="n in 5"
            :key="n"
            type="button"
            @click="form.rating = n"
            class="text-2xl leading-none transition-colors"
            :class="n <= form.rating ? 'text-yellow-400' : 'text-gray-300 hover:text-yellow-300'"
          >★</button>
        </div>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Title <span class="text-gray-400 font-normal">(optional)</span></label>
        <input v-model="form.title" type="text" maxlength="150"
          class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none" />
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Review</label>
        <textarea v-model="form.body" rows="4" required maxlength="2000"
          class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none resize-none" />
      </div>

      <button type="submit" :disabled="!form.rating || submitting"
        class="px-5 py-2.5 bg-emerald-600 text-white text-sm font-semibold rounded-xl hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
        {{ submitting ? 'Submitting…' : 'Submit Review' }}
      </button>
    </form>
  </div>
</template>

<script setup>
import { Link, router, usePage } from '@inertiajs/vue3'
import { reactive, ref } from 'vue'

const props = defineProps({ productId: Number })

const form = reactive({ rating: 0, title: '', body: '' })
const submitting = ref(false)

function submit() {
  submitting.value = true
  router.post(route('reviews.store'), { product_id: props.productId, ...form }, {
    preserveScroll: true,
    onSuccess: () => { form.rating = 0; form.title = ''; form.body = '' },
    onFinish: () => { submitting.value = false },
  })
}
</script>
