<template>
  <div ref="root" class="product-card group w-[280px] sm:w-[300px] max-w-full relative" :class="{ 'w-full sm:w-full': fluid }">
    <NuxtLink :to="`/product/${product.slug}`" class="block card-premium overflow-hidden group-hover:-translate-y-1.5 transition-transform duration-500">
      <!-- Image -->
      <div class="relative aspect-square overflow-hidden bg-gray-50">
        <img
          :src="product.thumbnail || product.images?.[0]"
          :alt="product.name"
          class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700"
          loading="lazy"
        />
        <!-- Second image cross-fades in on hover when available -->
        <img
          v-if="product.images?.[1]"
          :src="product.images[1]"
          :alt="''"
          class="absolute inset-0 w-full h-full object-cover opacity-0 group-hover:opacity-100 transition-opacity duration-700"
          loading="lazy"
        />
        <!-- Badges -->
        <div class="absolute top-3 left-3 flex flex-col gap-2">
          <span v-if="product.discount_percentage > 0" class="bg-red-500 text-white text-xs font-medium px-2.5 py-1 rounded-full">
            -{{ Math.round(product.discount_percentage) }}%
          </span>
          <span v-if="product.is_new" class="bg-gold-500 text-white text-xs font-medium px-2.5 py-1 rounded-full">New</span>
          <span v-if="product.is_limited_edition" class="bg-luxury-black text-white text-xs font-medium px-2.5 py-1 rounded-full">Limited</span>
        </div>
        <div v-if="!inStock" class="absolute inset-0 bg-white/60 flex items-center justify-center">
          <span class="bg-luxury-black text-white text-xs font-medium tracking-widest uppercase px-4 py-2 rounded-full">Sold out</span>
        </div>
      </div>
      <!-- Info -->
      <div class="p-4">
        <p v-if="product.brand" class="text-xs text-gray-400 uppercase tracking-wider mb-1">{{ product.brand.name }}</p>
        <h3 class="font-medium text-sm mb-2 line-clamp-1 group-hover:text-gold-500 transition-colors">{{ product.name }}</h3>
        <div class="flex items-center justify-between gap-2">
          <div class="flex items-baseline gap-2 min-w-0">
            <span class="text-lg font-semibold whitespace-nowrap">Rs. {{ formatPrice(product.final_price) }}</span>
            <span v-if="product.compare_price && product.compare_price > product.final_price" class="text-sm text-gray-400 line-through truncate">Rs. {{ formatPrice(product.compare_price) }}</span>
          </div>
          <div v-if="product.reviews_count > 0" class="flex items-center gap-0.5 flex-shrink-0">
            <svg v-for="i in 5" :key="i" class="w-3 h-3" :class="i <= Math.round(product.average_rating) ? 'text-gold-500' : 'text-gray-200'" fill="currentColor" viewBox="0 0 20 20">
              <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
            </svg>
          </div>
        </div>
      </div>
    </NuxtLink>

    <!-- Wishlist -->
    <div class="absolute top-3 right-3">
      <button
        ref="heart"
        :aria-label="inWishlist ? 'Remove from wishlist' : 'Add to wishlist'"
        :class="inWishlist ? 'text-red-500' : 'text-gray-400 hover:text-red-500'"
        class="w-9 h-9 bg-white/90 backdrop-blur-sm rounded-full shadow-premium flex items-center justify-center transition-colors"
        @click.stop.prevent="toggleWishlist"
      >
        <svg class="w-4 h-4" :fill="inWishlist ? 'currentColor' : 'none'" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
        </svg>
      </button>
    </div>

    <!-- Add to Cart: slides up on hover (desktop), always visible on touch -->
    <div v-if="inStock" class="add-overlay absolute inset-x-0 p-4 bg-gradient-to-t from-black/60 to-transparent transition-all duration-500">
      <button
        class="w-full bg-white text-luxury-black font-medium py-2.5 rounded-lg hover:bg-gold-500 hover:text-white transition-all duration-300 text-sm"
        @click.stop.prevent="addToCart"
      >
        Add to Cart
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue'
import type { Product } from '~/types'
import { useCartStore } from '~/stores/cart'
import { useAuthStore } from '~/stores/auth'
import { useWishlistStore } from '~/stores/wishlist'
import { useToast } from '~/composables/useToast'

const props = defineProps<{
  product: Product
  fluid?: boolean
}>()

const route = useRoute()
const cartStore = useCartStore()
const authStore = useAuthStore()
const wishlistStore = useWishlistStore()
const toast = useToast()
const heart = ref<HTMLElement | null>(null)

const inStock = computed(() => props.product.in_stock && props.product.stock_quantity > 0)
const inWishlist = computed(() => wishlistStore.isInWishlist(props.product.id))

function formatPrice(price: number): string {
  return Number(price).toLocaleString('en-US', { maximumFractionDigits: 0 })
}

function addToCart() {
  // Guests can build a cart; login is only required at checkout.
  const added = cartStore.addItem(props.product, 1)
  if (added > 0) {
    toast.success(`${props.product.name} added to cart`, { label: 'View cart', to: '/cart' })
  } else {
    toast.info(`Only ${props.product.stock_quantity} in stock — all are already in your cart.`)
  }
}

async function toggleWishlist() {
  if (!authStore.isAuthenticated) {
    toast.info('Please sign in to save items to your wishlist.')
    return navigateTo(`/auth/login?redirect=${encodeURIComponent(route.fullPath)}`)
  }
  const adding = !inWishlist.value
  try {
    await wishlistStore.toggle(props.product)
    if (heart.value && adding && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
      const { animate } = await import('animejs')
      animate(heart.value, { scale: [1, 1.4, 1], duration: 500, ease: 'outElastic(1, .5)' })
    }
  } catch {
    toast.error('Could not update your wishlist. Please try again.')
  }
}
</script>

<style scoped>
.add-overlay {
  bottom: 0;
}
@media (hover: hover) {
  .add-overlay {
    @apply opacity-0 translate-y-2 pointer-events-none;
  }
  .group:hover .add-overlay {
    @apply opacity-100 translate-y-0 pointer-events-auto;
  }
}
/* Keep the overlay above the price block, over the image */
.add-overlay {
  bottom: 5.5rem;
}
</style>
