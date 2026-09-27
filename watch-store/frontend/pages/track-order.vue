<template>
  <div class="min-h-screen bg-gray-50">
    <div class="container mx-auto px-4 py-12">
      <h1 class="text-3xl font-serif mb-2 text-center">Track Your Order</h1>
      <p class="text-gray-600 text-center mb-8">Enter your order number and the phone number used at checkout.</p>

      <form @submit.prevent="track" class="max-w-md mx-auto bg-white rounded-lg shadow-sm p-6 space-y-4 mb-10">
        <div>
          <label class="block text-sm font-medium mb-1">Order Number</label>
          <input
            v-model="form.order_number"
            type="text"
            placeholder="ORD-XXXXXXXX"
            required
            class="w-full px-4 py-2.5 rounded-lg border border-gray-200 focus:outline-none focus:ring-2 focus:ring-gold-500"
          />
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Phone Number</label>
          <input
            v-model="form.phone"
            type="tel"
            placeholder="98XXXXXXXX"
            required
            class="w-full px-4 py-2.5 rounded-lg border border-gray-200 focus:outline-none focus:ring-2 focus:ring-gold-500"
          />
        </div>
        <button
          type="submit"
          :disabled="loading"
          class="w-full bg-luxury-black text-white py-3 rounded-lg hover:bg-gray-800 transition-colors disabled:opacity-50"
        >
          {{ loading ? 'Searching...' : 'Track Order' }}
        </button>
        <p v-if="error" class="text-red-600 text-sm">{{ error }}</p>
      </form>

      <div v-if="order" class="max-w-2xl mx-auto bg-white rounded-lg shadow-sm p-6 md:p-8">
        <div class="flex items-center justify-between mb-6 flex-wrap gap-2">
          <div>
            <h2 class="text-xl font-semibold">Order #{{ order.order_number }}</h2>
            <p class="text-sm text-gray-500">Placed on {{ formatDate(order.created_at) }}</p>
          </div>
          <span class="px-3 py-1 rounded-full text-sm font-medium bg-gold-100 text-gold-700">
            {{ order.status_label }}
          </span>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8 text-sm">
          <div>
            <p class="text-gray-500">Payment</p>
            <p class="font-medium capitalize">{{ order.payment_status }}</p>
          </div>
          <div>
            <p class="text-gray-500">Total</p>
            <p class="font-medium">Rs. {{ Number(order.total).toLocaleString() }}</p>
          </div>
          <div v-if="order.tracking_number">
            <p class="text-gray-500">Tracking #</p>
            <p class="font-medium">{{ order.tracking_number }}</p>
          </div>
          <div v-if="order.courier_name">
            <p class="text-gray-500">Courier</p>
            <p class="font-medium">{{ order.courier_name }}</p>
          </div>
          <div v-if="order.estimated_delivery_date">
            <p class="text-gray-500">Est. Delivery</p>
            <p class="font-medium">{{ formatDate(order.estimated_delivery_date) }}</p>
          </div>
        </div>

        <h3 class="font-semibold mb-4">Status Timeline</h3>
        <ol class="space-y-4 mb-8">
          <li v-for="(step, i) in order.status_history" :key="i" class="flex gap-3">
            <div class="flex flex-col items-center">
              <span class="w-3 h-3 rounded-full bg-gold-500 mt-1.5"></span>
              <span v-if="i < order.status_history.length - 1" class="w-px flex-1 bg-gray-200"></span>
            </div>
            <div class="pb-2">
              <p class="font-medium">{{ step.label }}</p>
              <p v-if="step.note" class="text-sm text-gray-500">{{ step.note }}</p>
              <p class="text-xs text-gray-400">{{ formatDateTime(step.created_at) }}</p>
            </div>
          </li>
        </ol>

        <h3 class="font-semibold mb-3">Items</h3>
        <div class="space-y-2 text-sm">
          <div v-for="item in order.items" :key="item.id" class="flex justify-between text-gray-600">
            <span>{{ item.product_name }} × {{ item.quantity }}</span>
            <span>Rs. {{ Number(item.total).toLocaleString() }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'

interface StatusStep {
  status: string
  label: string
  note: string | null
  created_at: string
}
interface TrackedOrder {
  order_number: string
  status_label: string
  payment_status: string
  total: number
  tracking_number: string | null
  courier_name: string | null
  estimated_delivery_date: string | null
  created_at: string
  status_history: StatusStep[]
  items: { id: number; product_name: string; quantity: number; total: number }[]
}

useSeoMeta({ title: 'Track Your Order' })

const api = useApi()
const route = useRoute()
const form = reactive({
  order_number: typeof route.query.order === 'string' ? route.query.order : '',
  phone: typeof route.query.phone === 'string' ? route.query.phone : '',
})
const order = ref<TrackedOrder | null>(null)
const loading = ref(false)
const error = ref('')

async function track() {
  loading.value = true
  error.value = ''
  order.value = null
  try {
    const response = await api.post<{ success: boolean; data: TrackedOrder }>('/v1/orders/track', {
      order_number: form.order_number.trim(),
      phone: form.phone.trim(),
    })
    order.value = response.data
  } catch (e: any) {
    error.value = e?.message || 'No order found matching that order number and phone number.'
  } finally {
    loading.value = false
  }
}

function formatDate(value: string) {
  return new Date(value).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })
}
function formatDateTime(value: string) {
  return new Date(value).toLocaleString('en-US', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' })
}

// Arriving from the order confirmation page: look the order up straight away.
onMounted(() => {
  if (form.order_number && form.phone) track()
})
</script>
