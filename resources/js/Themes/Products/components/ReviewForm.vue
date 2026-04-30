<template>
  <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
    <h3 class="font-semibold text-gray-900 mb-4">{{ t('review.write') }}</h3>

    <div v-if="$page.props.flash?.success" class="mb-4 p-3 bg-green-50 text-green-700 rounded-lg text-sm">
      {{ $page.props.flash.success }}
    </div>

    <div v-if="!$page.props.auth.user" class="text-sm text-gray-500">
      <Link :href="route('login')" class="text-indigo-600 hover:underline">{{ t('review.sign_in') }}</Link> {{ t('review.sign_in_to_leave') }}
    </div>

    <form v-else @submit.prevent="submit" class="space-y-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">{{ t('review.rating') }}</label>
        <div class="flex gap-1">
          <button v-for="n in 5" :key="n" type="button" @click="form.rating = n"
            class="text-2xl leading-none transition-colors cursor-pointer"
            :class="n <= form.rating ? 'text-yellow-400' : 'text-gray-300 hover:text-yellow-300'">★</button>
        </div>
        <p v-if="form.errors.rating" class="text-red-500 text-xs mt-1">{{ form.errors.rating }}</p>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('review.title') }} <span class="text-gray-400 font-normal">{{ t('review.optional') }}</span></label>
        <input v-model="form.title" type="text" maxlength="150"
          class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none" />
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('review.body') }}</label>
        <textarea v-model="form.body" rows="4" required maxlength="2000"
          class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none resize-none" />
        <p v-if="form.errors.body" class="text-red-500 text-xs mt-1">{{ form.errors.body }}</p>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('review.photos') }} <span class="text-gray-400 font-normal">{{ t('review.optional') }}</span></label>
        <input type="file" multiple accept="image/*" @change="handleImages"
          class="block w-full text-sm text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer" />
        <p class="text-xs text-gray-400 mt-1">Up to 5 images, max 5 MB each.</p>
        <div v-if="previews.length" class="flex gap-2 flex-wrap mt-2">
          <img v-for="(src, i) in previews" :key="i" :src="src" class="w-16 h-16 object-cover rounded-lg border border-gray-200" />
        </div>
        <p v-if="form.errors['images.0']" class="text-red-500 text-xs mt-1">{{ form.errors['images.0'] }}</p>
      </div>

      <button type="submit" :disabled="!form.rating || form.processing"
        class="px-5 py-2.5 bg-indigo-600 text-white text-sm font-semibold rounded-lg hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors cursor-pointer">
        {{ form.processing ? t('review.submitting') : t('review.submit') }}
      </button>
    </form>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import { useForm } from '@inertiajs/vue3'
import { ref } from 'vue'
import { useI18n } from '@/composables/useI18n'

const { t } = useI18n()
const route = window.route

const props = defineProps({ productId: Number })

const previews = ref([])

const form = useForm({
  product_id: props.productId,
  rating: 0,
  title: '',
  body: '',
  images: [],
})

function handleImages(e) {
  const files = Array.from(e.target.files).slice(0, 5)
  form.images = files
  previews.value = files.map(f => URL.createObjectURL(f))
}

function submit() {
  form.post(route('reviews.store'), {
    preserveScroll: true,
    onSuccess: () => {
      form.reset()
      previews.value = []
    },
  })
}
</script>
