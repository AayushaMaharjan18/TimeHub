<template>
  <div class="min-h-screen bg-gray-50 flex items-center justify-center py-16 px-4">
    <div class="bg-white rounded-lg shadow-sm p-8 max-w-md w-full text-center">
      <div v-if="verified">
        <div class="text-6xl mb-4">✓</div>
        <h1 class="text-2xl font-semibold mb-2">Payment Successful</h1>
        <p class="text-gray-600 mb-2" v-if="orderNumber">Order #{{ orderNumber }} has been confirmed.</p>
        <p class="text-gray-600 mb-6">Your eSewa payment was verified and your order has been confirmed.</p>
        <NuxtLink to="/account" class="inline-block bg-luxury-black text-white px-6 py-2 rounded-lg hover:bg-gray-800 transition-colors">
          View My Orders
        </NuxtLink>
      </div>

      <div v-else>
        <div class="text-6xl mb-4">✕</div>
        <h1 class="text-2xl font-semibold mb-2">Payment Failed</h1>
        <p class="text-gray-600 mb-6">
          We couldn't verify your eSewa payment. If money was deducted, please contact support with your order number — no order is marked paid until our server confirms it directly with eSewa.
        </p>
        <NuxtLink to="/checkout" class="inline-block bg-luxury-black text-white px-6 py-2 rounded-lg hover:bg-gray-800 transition-colors">
          Back to Checkout
        </NuxtLink>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'

// The backend's own callback route (routes/web.php: payments.esewa.callback)
// already verified this payment server-to-server against eSewa before
// redirecting here — this page only reflects that result, it never decides
// success itself.
const route = useRoute()
const cartStore = useCartStore()

const verified = ref(false)
const orderNumber = ref<string | null>(null)

onMounted(() => {
  verified.value = route.query.status === 'success'
  orderNumber.value = typeof route.query.order === 'string' ? route.query.order : null

  if (verified.value) {
    cartStore.clearCart()
  }
})
</script>
