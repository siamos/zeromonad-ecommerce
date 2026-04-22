<template>
  <div class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <Head title="Forgot Password" />

    <div class="sm:mx-auto sm:w-full sm:max-w-md">
      <Link :href="route('home')" class="flex justify-center text-2xl font-bold text-indigo-600">
        {{ $page.props.site_name }}
      </Link>
      <h2 class="mt-6 text-center text-3xl font-bold text-gray-900">Reset your password</h2>
      <p class="mt-2 text-center text-sm text-gray-600">
        Enter your email and we'll send you a reset link.
      </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
      <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
        <div v-if="status" class="mb-4 text-sm font-medium text-green-600">{{ status }}</div>

        <form @submit.prevent="submit" class="space-y-6">
          <div>
            <label class="block text-sm font-medium text-gray-700">Email address</label>
            <input v-model="form.email" type="email" required autofocus
              class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500" />
            <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</p>
          </div>

          <div class="flex items-center justify-between">
            <Link :href="route('login')" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
              Back to sign in
            </Link>
            <button type="submit" :disabled="form.processing"
              class="flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 disabled:opacity-50 cursor-pointer">
              Send reset link
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'

defineProps({ status: String })

const form = useForm({ email: '' })

function submit() {
  form.post(route('password.email'))
}
</script>
