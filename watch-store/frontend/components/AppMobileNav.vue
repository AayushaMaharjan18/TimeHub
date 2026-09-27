<template>
  <nav class="fixed bottom-0 left-0 right-0 z-50 bg-white border-t border-gray-200 lg:hidden safe-area-bottom">
    <div class="flex items-center justify-around h-16">
      <NuxtLink to="/" class="flex flex-col items-center space-y-1 px-3 py-2" exact-active-class="text-gold-500">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
        </svg>
        <span class="text-2xs">Home</span>
      </NuxtLink>

      <NuxtLink to="/shop" class="flex flex-col items-center space-y-1 px-3 py-2" active-class="text-gold-500">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
        </svg>
        <span class="text-2xs">Shop</span>
      </NuxtLink>

      <NuxtLink to="/cart" class="flex flex-col items-center space-y-1 px-3 py-2 relative" active-class="text-gold-500">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
        </svg>
        <span class="text-2xs">Cart</span>
        <ClientOnly><span v-if="cartStore.totalItems > 0" class="absolute -top-1 right-1 bg-gold-500 text-white text-2xs rounded-full w-4 h-4 flex items-center justify-center">
          {{ cartStore.totalItems }}
        </span></ClientOnly>
      </NuxtLink>

      <NuxtLink to="/wishlist" class="flex flex-col items-center space-y-1 px-3 py-2 relative" active-class="text-gold-500">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
        </svg>
        <span class="text-2xs">Wishlist</span>
        <ClientOnly><span v-if="wishlistStore.count > 0" class="absolute -top-1 right-1 bg-gold-500 text-white text-2xs rounded-full w-4 h-4 flex items-center justify-center">
          {{ wishlistStore.count }}
        </span></ClientOnly>
      </NuxtLink>

      <ClientOnly>
      <NuxtLink v-if="authStore.isAuthenticated" to="/account" class="flex flex-col items-center space-y-1 px-3 py-2" active-class="text-gold-500">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
        </svg>
        <span class="text-2xs">Account</span>
      </NuxtLink>

      <NuxtLink v-else to="/auth/login" class="flex flex-col items-center space-y-1 px-3 py-2" active-class="text-gold-500">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
        </svg>
        <span class="text-2xs">Login</span>
      </NuxtLink>
      <template #fallback><span class="w-12" /></template>
      </ClientOnly>
    </div>
  </nav>
</template>

<script setup lang="ts">
import { useAuthStore } from '~/stores/auth'
import { useCartStore } from '~/stores/cart'
import { useWishlistStore } from '~/stores/wishlist'

const authStore = useAuthStore()
const cartStore = useCartStore()
const wishlistStore = useWishlistStore()
</script>

<style scoped>
.safe-area-bottom {
  padding-bottom: env(safe-area-inset-bottom, 0px);
}
</style>