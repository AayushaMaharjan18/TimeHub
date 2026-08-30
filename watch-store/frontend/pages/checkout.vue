<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Page Header -->
    <div class="bg-luxury-black text-white py-12">
      <div class="container mx-auto px-4">
        <h1 class="text-4xl font-serif text-center mb-2">Checkout</h1>
        <p class="text-center text-gray-400">Complete your order</p>
      </div>
    </div>

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

      <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Checkout Form -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Shipping Address -->
          <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-xl font-semibold mb-4">Shipping Address</h2>
            <form @submit.prevent="placeOrder">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                  <input
                    v-model="shippingForm.full_name"
                    type="text"
                    required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gold-500 focus:border-transparent"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                  <input
                    v-model="shippingForm.phone"
                    type="tel"
                    required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gold-500 focus:border-transparent"
                  />
                </div>
                <div class="md:col-span-2">
                  <label class="block text-sm font-medium text-gray-700 mb-2">District</label>
                  <select
                    v-model="shippingForm.district"
                    required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gold-500 focus:border-transparent"
                  >
                    <option value="">Select District</option>
                    <option v-for="district in districts" :key="district" :value="district">
                      {{ district }}
                    </option>
                  </select>
                </div>
                <div class="md:col-span-2">
                  <label class="block text-sm font-medium text-gray-700 mb-2">Municipality</label>
                  <input
                    v-model="shippingForm.municipality"
                    type="text"
                    required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gold-500 focus:border-transparent"
                  />
                </div>
                <div class="md:col-span-2">
                  <label class="block text-sm font-medium text-gray-700 mb-2">Street Address</label>
                  <textarea
                    v-model="shippingForm.street"
                    rows="3"
                    required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gold-500 focus:border-transparent"
                  ></textarea>
                </div>
              </div>
            </form>
          </div>

          <!-- Payment Method -->
          <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-xl font-semibold mb-4">Payment Method</h2>
            <div class="space-y-3">
              <label
                v-for="method in paymentMethods"
                :key="method.value"
                class="flex items-center p-4 border rounded-lg cursor-pointer transition-colors"
                :class="paymentMethod === method.value ? 'border-gold-500 bg-gold-500/5' : 'border-gray-300 hover:bg-gray-50'"
              >
                <input
                  v-model="paymentMethod"
                  type="radio"
                  :value="method.value"
                  class="w-4 h-4 text-gold-500"
                />
                <div class="ml-3 flex-1">
                  <p class="font-medium">{{ method.label }}</p>
                  <p class="text-sm text-gray-500">{{ method.description }}</p>
                </div>
                <span class="ml-3 flex items-center justify-center px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider" :class="method.badgeClass">{{ method.short }}</span>
              </label>
            </div>
            <p class="text-xs text-gray-400 mt-4">
              Payments are processed securely. You will be redirected to {{ paymentMethod === 'khalti' ? 'Khalti' : paymentMethod === 'esewa' ? 'eSewa' : 'complete your order on delivery' }} to finish.
            </p>
          </div>
        </div>

        <!-- Order Summary -->
        <div class="lg:col-span-1">
          <div class="bg-white rounded-lg shadow-sm p-6 sticky top-24">
            <h2 class="text-xl font-semibold mb-6">Order Summary</h2>

            <!-- Cart Items -->
            <div class="space-y-4 mb-6">
              <div v-for="item in cartStore.items" :key="item.id" class="flex gap-3 pb-4 border-b">
                <div class="w-16 h-16 flex-shrink-0">
                  <img
                    :src="item.product.thumbnail"
                    :alt="item.product.name"
                    class="w-full h-full object-cover rounded-lg"
                  />
                </div>
                <div class="flex-1">
                  <p class="font-medium text-sm">{{ item.product.name }}</p>
                  <p class="text-sm text-gray-500">Qty: {{ item.quantity }}</p>
                  <p class="text-sm font-semibold">Rs. {{ (item.product.final_price * item.quantity).toLocaleString() }}</p>
                </div>
              </div>
            </div>

            <div class="space-y-3 mb-6">
              <div class="flex justify-between">
                <span class="text-gray-600">Subtotal</span>
                <span class="font-medium">Rs. {{ cartStore.subtotal.toLocaleString() }}</span>
              </div>
        
              <div class="flex justify-between">
                <span class="text-gray-600">Shipping</span>
                <span class="font-medium">Calculated later</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Tax</span>
                <span class="font-medium">Calculated later</span>
              </div>
            </div>

            <div class="border-t pt-4 mb-6">
              <div class="flex justify-between text-lg font-semibold">
                <span>Total</span>
                <span>Rs. {{ cartStore.subtotal.toLocaleString() }}</span>
              </div>
            </div>

            <button
              @click="placeOrder"
              :disabled="isPlacingOrder"
              class="w-full bg-luxury-black text-white py-3 rounded-lg hover:bg-gray-800 transition-colors disabled:bg-gray-400 disabled:cursor-not-allowed"
            >
              {{ isPlacingOrder ? 'Placing Order...' : 'Place Order' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useCartStore } from '~/stores/cart'
import { useAuthStore } from '~/stores/auth'
import { useApi } from '~/composables/useApi'

const route = useRoute()
const router = useRouter()
const cartStore = useCartStore()
const authStore = useAuthStore()
const api = useApi()

const shippingForm = ref({
  full_name: '',
  phone: '',
  district: '',
  municipality: '',
  street: ''
})

const paymentMethod = ref('cod')
const isPlacingOrder = ref(false)
const districts = ref<string[]>([])

const paymentMethods = [
  {
    value: 'cod',
    label: 'Cash on Delivery',
    description: 'Pay with cash when your order is delivered',
    short: 'COD',
    badgeClass: 'bg-gray-100 text-gray-500',
  },
  {
    value: 'khalti',
    label: 'Khalti',
    description: 'Pay instantly using your Khalti wallet, cards and banks',
    short: 'Khalti',
    badgeClass: 'bg-purple-100 text-purple-700',
  },
  {
    value: 'esewa',
    label: 'eSewa',
    description: 'Pay securely using your eSewa wallet',
    short: 'eSewa',
    badgeClass: 'bg-green-100 text-green-700',
  },
]

onMounted(() => {
  // Redirect to login if not authenticated
  if (!authStore.isAuthenticated) {
    router.push('/auth/login?redirect=/checkout')
    return
  }

  // Pre-fill user data
  if (authStore.user) {
    shippingForm.value.full_name = authStore.user.name
    shippingForm.value.phone = authStore.user.phone
  }

  // Load districts
  loadDistricts()
})

async function loadDistricts() {
  try {
    const response = await api.get<any>('/v1/shipping/districts')
    districts.value = (response.data || []).map((d: any) => d.name)
  } catch (error) {
    console.error('Failed to load districts:', error)
  }
}

async function createOrder() {
  const orderData = {
    shipping_name: shippingForm.value.full_name,
    shipping_phone: shippingForm.value.phone,
    shipping_district: shippingForm.value.district,
    shipping_municipality: shippingForm.value.municipality,
    shipping_street: shippingForm.value.street,
    shipping_ward: '',
    payment_method: paymentMethod.value,
    // Only product_id and quantity are trusted — the backend always
    // re-derives price/name/total from the product record itself.
    items: cartStore.items.map(item => ({
      product_id: item.product_id,
      quantity: item.quantity,
    }))
  }
  const response = await api.post<any>('/v1/orders', orderData)
  return response.data
}

async function placeOrder() {
  if (!authStore.isAuthenticated) {
    router.push('/auth/login?redirect=/checkout')
    return
  }

  isPlacingOrder.value = true

  try {
    const order = await createOrder()

    if (paymentMethod.value === 'cod') {
      cartStore.clearCart()
      alert('Order placed successfully!')
      router.push('/account')
    } else {
      // eSewa/Khalti: hand off to the gateway. The backend performs the
      // actual payment verification on its own callback route once the
      // gateway redirects back — this page never marks anything paid itself.
      await startGatewayPayment(order, paymentMethod.value as 'esewa' | 'khalti')
    }
  } catch (error: any) {
    alert(error.message || 'Failed to place order. Please try again.')
  } finally {
    isPlacingOrder.value = false
  }
}

async function startGatewayPayment(order: any, provider: 'esewa' | 'khalti') {
  const response = await api.post<any>('/v1/payments/initiate', {
    order_id: order.id,
    provider,
  })

  const data = response.data

  if (provider === 'khalti') {
    if (!data?.payment_url) {
      throw new Error('Khalti could not be initialized.')
    }
    window.location.href = data.payment_url
    return
  }

  if (!data?.action || !data?.fields) {
    throw new Error('eSewa could not be initialized.')
  }

  // Build and submit a hidden form to the eSewa gateway.
  const form = document.createElement('form')
  form.method = data.method || 'POST'
  form.action = data.action
  Object.entries(data.fields).forEach(([key, value]) => {
    const input = document.createElement('input')
    input.type = 'hidden'
    input.name = key
    input.value = String(value)
    form.appendChild(input)
  })
  document.body.appendChild(form)
  form.submit()
}
</script>