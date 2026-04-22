<template>
  <div class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <Head title="Sign In" />

    <div class="sm:mx-auto sm:w-full sm:max-w-md">
      <Link :href="route('home')" class="flex justify-center text-2xl font-bold text-indigo-600">
        {{ $page.props.site_name }}
      </Link>
      <h2 class="mt-6 text-center text-3xl font-bold text-gray-900">Sign in to your account</h2>
      <p class="mt-2 text-center text-sm text-gray-600">
        Or
        <Link :href="route('register')" class="font-medium text-indigo-600 hover:text-indigo-500">create a new account</Link>
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

          <div>
            <label class="block text-sm font-medium text-gray-700">Password</label>
            <input v-model="form.password" type="password" required
              class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500" />
            <p v-if="form.errors.password" class="mt-1 text-sm text-red-600">{{ form.errors.password }}</p>
          </div>

          <div class="flex items-center justify-between">
            <label class="flex items-center gap-2 text-sm text-gray-700">
              <input v-model="form.remember" type="checkbox" class="rounded border-gray-300 text-indigo-600" />
              Remember me
            </label>
            <Link v-if="canResetPassword" :href="route('password.request')"
              class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
              Forgot your password?
            </Link>
          </div>

          <button type="submit" :disabled="form.processing"
            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 disabled:opacity-50 cursor-pointer">
            Sign in
          </button>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'

const props = defineProps({
  canResetPassword: Boolean,
  status: String,
})

const form = useForm({
  email: '',
  password: '',
  remember: false,
})

function submit() {
  form.post(route('login'), { onFinish: () => form.reset('password') })
}
</script>
