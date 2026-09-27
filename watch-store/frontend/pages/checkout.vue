<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Page Header -->
    <div class="bg-luxury-black text-white py-12">
      <div class="container mx-auto px-4">
        <h1 class="text-4xl font-serif text-center mb-2">Checkout</h1>
        <p class="text-center text-gray-400">Complete your order</p>
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

      <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Checkout Form -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Shipping Address -->
          <div v-reveal class="bg-white rounded-2xl shadow-premium p-6">
            <h2 class="text-xl font-semibold mb-4">Shipping Address</h2>

            <!-- Saved addresses -->
            <div v-if="savedAddresses.length" class="grid sm:grid-cols-2 gap-3 mb-6">
              <button
                v-for="addr in savedAddresses"
                :key="addr.id"
                type="button"
                class="text-left p-4 border rounded-xl transition-all"
                :class="selectedAddressId === addr.id ? 'border-gold-500 bg-gold-500/5 ring-1 ring-gold-500' : 'border-gray-200 hover:border-gray-400'"
                @click="useAddress(addr)"
              >
                <p class="font-medium text-sm">{{ addr.label || 'Address' }} <span v-if="addr.is_default" class="text-xs text-gold-600">· Default</span></p>
                <p class="text-sm text-gray-600">{{ addr.full_name }} · {{ addr.phone }}</p>
                <p class="text-sm text-gray-500">{{ addr.street }}, {{ addr.municipality }}<span v-if="addr.ward">-{{ addr.ward }}</span>, {{ addr.district }}</p>
              </button>
              <button
                type="button"
                class="p-4 border border-dashed rounded-xl text-sm text-gray-500 hover:border-gold-500 hover:text-gold-600 transition-colors"
                :class="{ 'border-gold-500 text-gold-600': selectedAddressId === null }"
                @click="useNewAddress"
              >
                + Use a new address
              </button>
            </div>

            <form id="checkout-form" @submit.prevent="placeOrder">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="checkout-label" for="c-name">Full Name</label>
                  <input id="c-name" v-model="shippingForm.full_name" type="text" required class="checkout-input" />
                </div>
                <div>
                  <label class="checkout-label" for="c-phone">Phone Number</label>
                  <input id="c-phone" v-model="shippingForm.phone" type="tel" inputmode="numeric" pattern="[0-9]{10}" title="10-digit phone number" required class="checkout-input" />
                </div>
                <div>
                  <label class="checkout-label" for="c-district">District</label>
                  <select id="c-district" v-model="shippingForm.district" required class="checkout-input">
                    <option value="">Select district</option>
                    <option v-for="district in districts" :key="district.id" :value="district.name">
                      {{ district.name }} — Rs. {{ district.cost.toLocaleString() }}{{ district.delivery_days ? ` (${district.delivery_days})` : '' }}
                    </option>
                  </select>
                </div>
                <div>
                  <label class="checkout-label" for="c-municipality">Municipality</label>
                  <input id="c-municipality" v-model="shippingForm.municipality" type="text" required class="checkout-input" />
                </div>
                <div>
                  <label class="checkout-label" for="c-ward">Ward No. <span class="text-gray-400 font-normal">(optional)</span></label>
                  <input id="c-ward" v-model="shippingForm.ward" type="text" class="checkout-input" />
                </div>
                <div>
                  <label class="checkout-label" for="c-street">Street / Landmark</label>
                  <input id="c-street" v-model="shippingForm.street" type="text" required class="checkout-input" />
                </div>
              </div>
              <label v-if="selectedAddressId === null" class="flex items-center gap-2 mt-4 text-sm text-gray-600">
                <input v-model="saveAddress" type="checkbox" class="rounded text-gold-500 focus:ring-gold-500" />
                Save this address to my account
              </label>
            </form>
          </div>

          <!-- Payment Method -->
          <div v-reveal="{ delay: 100 }" class="bg-white rounded-2xl shadow-premium p-6">
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
          <div v-reveal="{ preset: 'right', delay: 150 }" class="bg-white rounded-2xl shadow-premium p-6 sticky top-20">
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
                <span class="font-medium">{{ selectedDistrict ? `Rs. ${shippingCost.toLocaleString()}` : 'Select district' }}</span>
              </div>
              <p v-if="selectedDistrict?.delivery_days" class="text-xs text-gray-400 -mt-2">Estimated delivery: {{ selectedDistrict.delivery_days }}</p>
            </div>

            <div class="border-t pt-4 mb-6">
              <div class="flex justify-between text-lg font-semibold">
                <span>Total</span>
                <span>Rs. {{ total.toLocaleString() }}</span>
              </div>
            </div>

            <button
              type="submit"
              form="checkout-form"
              :disabled="isPlacingOrder"
              class="w-full bg-luxury-black text-white py-3 rounded-lg hover:bg-gray-800 transition-colors disabled:bg-gray-400 disabled:cursor-not-allowed"
            >
              {{ isPlacingOrder ? 'Placing Order...' : 'Place Order' }}
            </button>
          </div>
        </div>
      </div>
    </div>
    </ClientOnly>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useCartStore } from '~/stores/cart'
import { useAuthStore } from '~/stores/auth'
import { useApi } from '~/composables/useApi'
import { useToast } from '~/composables/useToast'
import type { Address, ShippingDistrict } from '~/types'

useSeoMeta({ title: 'Checkout' })

const router = useRouter()
const cartStore = useCartStore()
const authStore = useAuthStore()
const api = useApi()
const toast = useToast()

const shippingForm = ref({
  full_name: '',
  phone: '',
  district: '',
  municipality: '',
  ward: '',
  street: '',
})

const paymentMethod = ref('cod')
const isPlacingOrder = ref(false)
const districts = ref<ShippingDistrict[]>([])
const savedAddresses = ref<Address[]>([])
const selectedAddressId = ref<number | null>(null)
const saveAddress = ref(true)

const selectedDistrict = computed(() => districts.value.find(d => d.name === shippingForm.value.district))
const shippingCost = computed(() => selectedDistrict.value?.cost ?? 0)
const total = computed(() => cartStore.subtotal + shippingCost.value)

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
  if (!authStore.isAuthenticated) {
    router.replace('/auth/login?redirect=/checkout')
    return
  }

  if (authStore.user) {
    shippingForm.value.full_name = authStore.user.name
    shippingForm.value.phone = authStore.user.phone
  }

  loadDistricts()
  loadAddresses()
})

async function loadDistricts() {
  try {
    const response = await api.get<{ data: ShippingDistrict[] }>('/v1/shipping/districts')
    districts.value = response.data || []
  } catch {
    toast.error('Could not load delivery districts. Please refresh the page.')
  }
}

async function loadAddresses() {
  try {
    const response = await api.get<{ data: Address[] }>('/v1/user/addresses')
    savedAddresses.value = response.data || []
    const preferred = savedAddresses.value.find(a => a.is_default) ?? savedAddresses.value[0]
    if (preferred) useAddress(preferred)
  } catch {
    // Saved addresses are a convenience; the form still works without them.
  }
}

function useAddress(addr: Address) {
  selectedAddressId.value = addr.id
  shippingForm.value = {
    full_name: addr.full_name,
    phone: addr.phone,
    district: addr.district,
    municipality: addr.municipality,
    ward: addr.ward || '',
    street: addr.street,
  }
}

function useNewAddress() {
  selectedAddressId.value = null
  shippingForm.value = {
    full_name: authStore.user?.name ?? '',
    phone: authStore.user?.phone ?? '',
    district: '',
    municipality: '',
    ward: '',
    street: '',
  }
}

async function createOrder() {
  const response = await api.post<any>('/v1/orders', {
    shipping_name: shippingForm.value.full_name,
    shipping_phone: shippingForm.value.phone.trim(),
    shipping_district: shippingForm.value.district,
    shipping_municipality: shippingForm.value.municipality,
    shipping_street: shippingForm.value.street,
    shipping_ward: shippingForm.value.ward,
    payment_method: paymentMethod.value,
    // Only product_id and quantity are trusted — the backend always
    // re-derives price/name/total from the product record itself.
    items: cartStore.items.map(item => ({
      product_id: item.product_id,
      quantity: item.quantity,
    })),
  })
  return response.data
}

async function placeOrder() {
  if (!authStore.isAuthenticated) {
    router.push('/auth/login?redirect=/checkout')
    return
  }
  if (!/^\d{10}$/.test(shippingForm.value.phone.trim())) {
    toast.error('Phone number must be exactly 10 digits.')
    return
  }

  isPlacingOrder.value = true

  try {
    if (selectedAddressId.value === null && saveAddress.value) {
      // Best effort: failing to save the address must not block the order.
      api.post('/v1/user/addresses', { ...shippingForm.value, label: 'Home' }).catch(() => {})
    }

    const order = await createOrder()

    if (paymentMethod.value === 'cod') {
      cartStore.clearCart()
      router.push({ path: '/order-success', query: { order: order.order_number, phone: order.shipping_phone } })
    } else {
      // eSewa/Khalti: hand off to the gateway. The backend performs the
      // actual payment verification on its own callback route once the
      // gateway redirects back — this page never marks anything paid itself.
      await startGatewayPayment(order, paymentMethod.value as 'esewa' | 'khalti')
    }
  } catch (error: any) {
    toast.error(error.message || 'Failed to place order. Please try again.')
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

<style scoped>
.checkout-label {
  @apply block text-sm font-medium text-gray-700 mb-2;
}
.checkout-input {
  @apply w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gold-500 focus:border-transparent;
}
</style>