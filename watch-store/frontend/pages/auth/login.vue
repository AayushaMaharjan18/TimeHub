<template>
  <div class="min-h-screen bg-gray-50 flex items-center justify-center py-12 px-4">
    <div class="max-w-md w-full">
      <div class="text-center mb-8">
        <h1 class="text-3xl font-serif text-luxury-black mb-2">Welcome Back</h1>
        <p class="text-gray-600">Sign in to your account</p>
      </div>

      <div class="bg-white rounded-lg shadow-sm p-8">
        <form @submit.prevent="handleLogin">
          <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
            <input
              v-model="form.email"
              type="email"
              required
              placeholder="your@email.com"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gold-500 focus:border-transparent"
            />
          </div>

          <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Password</label>
            <div class="relative">
              <input
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                required
                placeholder="••••••••"
                class="w-full px-4 py-3 pr-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gold-500 focus:border-transparent"
              />
              <button
                type="button"
                @click="showPassword = !showPassword"
                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none"
              >
                <!-- Eye icon (visible when password is hidden) -->
                <svg v-if="!showPassword" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                </svg>
                <!-- Eye-off icon (visible when password is shown) -->
                <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
              </button>
            </div>
          </div>

          <div class="flex items-center justify-between mb-6">
            <label class="flex items-center">
              <input v-model="form.remember" type="checkbox" class="w-4 h-4 text-gold-500 border-gray-300 rounded focus:ring-gold-500" />
              <span class="ml-2 text-sm text-gray-600">Remember me</span>
            </label>
            <NuxtLink to="/auth/forgot-password" class="text-sm text-gold-500 hover:text-gold-600">
              Forgot password?
            </NuxtLink>
          </div>

          <button
            type="submit"
            :disabled="loading"
            class="w-full bg-luxury-black text-white py-3 rounded-lg hover:bg-gray-800 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {{ loading ? 'Signing in...' : 'Sign In' }}
          </button>

          <div v-if="error" class="mt-4 p-3 bg-red-50 text-red-600 rounded-lg text-sm">
            {{ error }}
          </div>
        </form>

        <div class="mt-6 text-center">
          <p class="text-gray-600">
            Don't have an account?
            <NuxtLink :to="{ path: '/auth/register', query: route.query.redirect ? { redirect: route.query.redirect } : {} }" class="text-gold-500 hover:text-gold-600 font-medium">
              Sign up
            </NuxtLink>
          </p>
        </div>
      </div>

      <div class="mt-6 text-center">
        <NuxtLink to="/" class="text-gray-500 hover:text-gold-500">
          ← Back to Home
        </NuxtLink>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '~/stores/auth'
import { useApi } from '~/composables/useApi'
import type { AuthResponse } from '~/types'

useSeoMeta({ title: 'Sign In', robots: 'noindex' })

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()
const api = useApi()

const showPassword = ref(false)

const form = ref({
  email: '',
  password: '',
  remember: false
})

const loading = ref(false)
const error = ref('')

async function handleLogin() {
  loading.value = true
  error.value = ''
  
  try {
    const response = await api.post<AuthResponse>('/v1/auth/login', {
      email: form.value.email,
      password: form.value.password
    })
    
    authStore.setAuth(response.user, response.token)
    
    router.push(safeRedirect(route.query.redirect))
  } catch (err: any) {
    error.value = err.message || 'Invalid email or password'
  } finally {
    loading.value = false
  }
}
</script>
