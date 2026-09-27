<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Page Header -->
    <div class="bg-luxury-black text-white py-12">
      <div class="container mx-auto px-4">
        <h1 class="text-4xl font-serif text-center mb-2">Wishlist</h1>
        <p class="text-center text-gray-400">Your saved items</p>
      </div>
    </div>

    <ClientOnly>
    <div class="container mx-auto px-4 py-8">
      <div v-if="!authStore.isAuthenticated" class="text-center py-12">
        <p class="text-gray-500 mb-4">Please login to view your wishlist</p>
        <NuxtLink to="/auth/login?redirect=/wishlist" class="inline-block bg-luxury-black text-white px-6 py-2 rounded-lg hover:bg-gray-800 transition-colors">
          Login
        </NuxtLink>
      </div>

      <div v-else-if="loading" class="flex justify-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-gold-500"></div>
      </div>

      <div v-else-if="errorMessage" class="text-center py-12">
        <p class="text-red-500 mb-4">{{ errorMessage }}</p>
        <button @click="fetchWishlist" class="inline-block bg-luxury-black text-white px-6 py-2 rounded-lg hover:bg-gray-800 transition-colors">
          Try Again
        </button>
      </div>

      <div v-else-if="wishlistItems.length === 0" class="text-center py-12">
        <svg class="w-24 h-24 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
        </svg>
        <p class="text-gray-500 mb-4">Your wishlist is empty</p>
        <NuxtLink to="/shop" class="inline-block bg-luxury-black text-white px-6 py-2 rounded-lg hover:bg-gray-800 transition-colors">
          Browse Products
        </NuxtLink>
      </div>

      <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <div
          v-for="item in wishlistItems"
          :key="item.id"
          class="bg-white rounded-lg shadow-sm overflow-hidden group"
        >
          <div class="relative aspect-square">
            <img
              :src="item.thumbnail"
              :alt="item.name"
              class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
            />
            <button
              @click="removeFromWishlist(item.id)"
              class="absolute top-2 right-2 w-8 h-8 bg-white rounded-full shadow-md flex items-center justify-center hover:bg-red-50 transition-colors"
            >
              <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 24 24">
                <path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/>
              </svg>
            </button>
          </div>
          <div class="p-4">
            <NuxtLink :to="`/product/${item.slug}`" class="block">
              <h3 class="font-semibold text-lg mb-1 hover:text-gold-500 transition-colors">{{ item.name }}</h3>
              <div class="flex items-center gap-2 mb-2">
                <span v-if="item.brand" class="text-sm text-gray-500">{{ item.brand.name }}</span>
              </div>
              <div class="flex items-center gap-2">
                <span class="text-lg font-bold text-luxury-black">Rs. {{ item.final_price.toLocaleString() }}</span>
                <span v-if="item.compare_price" class="text-sm text-gray-400 line-through">
                  Rs. {{ item.compare_price.toLocaleString() }}
                </span>
              </div>
            </NuxtLink>
            <button
              @click="addToCart(item)"
              class="w-full mt-3 bg-luxury-black text-white py-2 rounded-lg hover:bg-gray-800 transition-colors"
            >
              Add to Cart
            </button>
          </div>
        </div>
      </div>
    </div>
    </ClientOnly>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted } from 'vue'
import { storeToRefs } from 'pinia'
import { useAuthStore } from '~/stores/auth'
import { useCartStore } from '~/stores/cart'
import { useWishlistStore } from '~/stores/wishlist'
import { useToast } from '~/composables/useToast'
import type { Product } from '~/types'

useSeoMeta({ title: 'Wishlist', robots: 'noindex' })

const toast = useToast()
const authStore = useAuthStore()
const cartStore = useCartStore()
const wishlistStore = useWishlistStore()

// Reactive references to the store so the UI updates automatically whenever
// items are added or removed (no fragile manual copies).
const { products, loading, error } = storeToRefs(wishlistStore)
const wishlistItems = computed<Product[]>(() => products.value)

const errorMessage = computed(() => {
  if (!error.value) return ''
  return typeof error.value === 'string' ? error.value : 'Something went wrong'
})

async function fetchWishlist() {
  if (!authStore.isAuthenticated) return
  await wishlistStore.load()
}

async function removeFromWishlist(productId: number) {
  try {
    await wishlistStore.remove(productId)
  } catch (e) {
    console.error('Failed to remove from wishlist:', e)
  }
}

function addToCart(product: Product) {
  const added = cartStore.addItem(product, 1)
  if (added > 0) toast.success(`${product.name} added to cart`, { label: 'View cart', to: '/cart' })
  else toast.info(product.stock_quantity > 0 ? 'All available stock is already in your cart.' : 'This watch is out of stock.')
}

onMounted(() => {
  fetchWishlist()
})
</script>
