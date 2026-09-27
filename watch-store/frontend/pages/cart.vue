<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Page Header -->
    <div class="bg-luxury-black text-white py-12">
      <div class="container mx-auto px-4">
        <h1 class="text-4xl font-serif text-center mb-2">Shopping Cart</h1>
        <p class="text-center text-gray-400">Review your items before checkout</p>
      </div>
    </div>

    <ClientOnly>
    <template #fallback>
      <div class="flex justify-center py-24"><div class="animate-spin rounded-full h-10 w-10 border-b-2 border-gold-500" /></div>
    </template>
    <div class="container mx-auto px-4 py-8">
      <div v-if="cartStore.isEmpty" class="text-center py-12">
        <svg class="w-24 h-24 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
        </svg>
        <p class="text-gray-500 mb-4">Your cart is empty</p>
        <NuxtLink to="/shop" class="inline-block bg-luxury-black text-white px-6 py-2 rounded-lg hover:bg-gray-800 transition-colors">
          Continue Shopping
        </NuxtLink>
      </div>

      <div v-else class="flex flex-col lg:flex-row gap-8">
        <!-- Cart Items -->
        <div class="flex-1">
          <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <TransitionGroup name="cart-item" tag="div">
            <div v-for="item in cartStore.items" :key="item.product_id" class="flex gap-4 p-6 border-b last:border-b-0 bg-white">
              <div class="w-24 h-24 flex-shrink-0">
                <NuxtLink :to="`/product/${item.product.slug}`" class="block w-full h-full rounded-lg overflow-hidden">
                  <img
                    :src="item.product.thumbnail"
                    :alt="item.product.name"
                    class="w-full h-full object-cover rounded-lg"
                  />
                </NuxtLink>
              </div>
              <div class="flex-1">
                <div class="flex justify-between">
                  <div>
                    <h3 class="font-semibold text-lg">
                      <NuxtLink :to="`/product/${item.product.slug}`" class="hover:text-gold-500">
                        {{ item.product.name }}
                      </NuxtLink>
                    </h3>
                    <p class="text-gray-500 text-sm">{{ item.product.sku }}</p>
                    <p class="text-gold-500 font-bold mt-2">Rs. {{ item.product.final_price.toLocaleString() }}</p>
                  </div>
                  <button
                    @click="cartStore.removeItem(item.product_id)"
                    class="text-gray-400 hover:text-red-500 transition-colors"
                    :aria-label="`Remove ${item.product.name}`"
                  >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                  </button>
                </div>
                <div class="flex items-center gap-2 mt-4">
                  <button
                    @click="cartStore.updateQuantity(item.product_id, item.quantity - 1)"
                    :disabled="item.quantity <= 1"
                    class="w-8 h-8 border border-gray-300 rounded hover:bg-gray-100 disabled:opacity-40"
                    aria-label="Decrease quantity"
                  >
                    -
                  </button>
                  <span class="w-8 text-center">{{ item.quantity }}</span>
                  <button
                    @click="cartStore.updateQuantity(item.product_id, item.quantity + 1)"
                    :disabled="item.quantity >= item.product.stock_quantity"
                    class="w-8 h-8 border border-gray-300 rounded hover:bg-gray-100 disabled:opacity-40"
                    aria-label="Increase quantity"
                  >
                    +
                  </button>
                  <span v-if="item.quantity >= item.product.stock_quantity" class="text-xs text-gray-400 ml-2">Max available</span>
                  <span class="ml-auto font-semibold">Rs. {{ (item.product.final_price * item.quantity).toLocaleString() }}</span>
                </div>
              </div>
            </div>
            </TransitionGroup>
          </div>

          <div class="mt-6">
            <NuxtLink to="/shop" class="inline-flex items-center text-gold-500 hover:text-gold-600">
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
              </svg>
              Continue Shopping
            </NuxtLink>
          </div>
        </div>

        <!-- Order Summary -->
        <div class="lg:w-96">
          <div class="bg-white rounded-lg shadow-sm p-6 sticky top-24">
            <h2 class="text-xl font-semibold mb-6">Order Summary</h2>

            <div class="space-y-3 mb-6">
              <div class="flex justify-between">
                <span class="text-gray-600">Subtotal</span>
                <span class="font-medium">Rs. {{ cartStore.subtotal.toLocaleString() }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Shipping</span>
                <span class="font-medium">Calculated at checkout</span>
              </div>
            </div>

            <div class="border-t pt-4 mb-6">
              <div class="flex justify-between text-lg font-semibold">
                <span>Estimated total</span>
                <span>Rs. {{ cartStore.subtotal.toLocaleString() }}</span>
              </div>
            </div>


            <button
              @click="proceedToCheckout"
              class="w-full bg-luxury-black text-white py-3 rounded-lg hover:bg-gray-800 transition-colors"
            >
              Proceed to Checkout
            </button>

            <p v-if="!authStore.isAuthenticated" class="text-center text-sm text-gray-500 mt-4">
              You'll be asked to sign in before checkout.
            </p>
          </div>
        </div>
      </div>
    </div>
    </ClientOnly>
  </div>
</template>

<script setup lang="ts">
import { useRouter } from 'vue-router'
import { useCartStore } from '~/stores/cart'
import { useAuthStore } from '~/stores/auth'

useSeoMeta({ title: 'Shopping Cart' })

const router = useRouter()
const cartStore = useCartStore()
const authStore = useAuthStore()

function proceedToCheckout() {
  if (!authStore.isAuthenticated) {
    router.push('/auth/login?redirect=/checkout')
    return
  }
  if (cartStore.isEmpty) {
    return
  }
  router.push('/checkout')
}
</script>

<style scoped>
.cart-item-leave-active {
  transition: all 0.4s ease;
}
.cart-item-leave-to {
  opacity: 0;
  transform: translateX(40px);
}
.cart-item-move {
  transition: transform 0.4s ease;
}
</style>
