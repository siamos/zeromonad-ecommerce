<template>
  <div class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <Head title="Verify Email" />

    <div class="sm:mx-auto sm:w-full sm:max-w-md">
      <Link :href="route('home')" class="flex justify-center text-2xl font-bold text-indigo-600">
        {{ $page.props.site_name }}
      </Link>
      <h2 class="mt-6 text-center text-3xl font-bold text-gray-900">Verify your email</h2>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
      <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
        <p class="text-sm text-gray-600 mb-6">
          Thanks for signing up! Please verify your email address by clicking the link we sent you.
        </p>

        <div v-if="status === 'verification-link-sent'" class="mb-4 text-sm font-medium text-green-600">
          A new verification link has been sent to your email address.
        </div>

        <div class="flex items-center justify-between">
          <form @submit.prevent="resend">
            <button type="submit" :disabled="resendForm.processing"
              class="text-sm font-medium text-indigo-600 hover:text-indigo-500 disabled:opacity-50 cursor-pointer">
              Resend verification email
            </button>
          </form>

          <form @submit.prevent="logout">
            <button type="submit" class="text-sm text-gray-500 hover:text-gray-700 cursor-pointer">
              Log out
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'

defineProps({ status: String })

const resendForm = useForm({})
const logoutForm = useForm({})

function resend() {
  resendForm.post(route('verification.send'))
}

function logout() {
  logoutForm.post(route('logout'))
}
</script>
