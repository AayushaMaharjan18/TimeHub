<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Page Header -->
    <div class="bg-luxury-black text-white py-12">
      <div class="container mx-auto px-4">
        <h1 class="text-4xl font-serif text-center mb-2">My Account</h1>
        <p class="text-center text-gray-400">Manage your account settings</p>
      </div>
    </div>

    <ClientOnly>
    <div v-if="!authStore.isAuthenticated" class="container mx-auto px-4 py-12 text-center">
      <p class="text-gray-500 mb-4">Please login to access your account</p>
      <NuxtLink to="/auth/login?redirect=/account" class="inline-block bg-luxury-black text-white px-6 py-2 rounded-lg hover:bg-gray-800 transition-colors">
        Login
      </NuxtLink>
    </div>

    <div v-else class="container mx-auto px-4 py-8">
      <div class="flex flex-col lg:flex-row gap-8">
        <!-- Sidebar Navigation -->
        <aside class="lg:w-64 flex-shrink-0">
          <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center gap-4 mb-6 pb-6 border-b">
              <div class="w-16 h-16 bg-gold-500 rounded-full flex items-center justify-center text-white text-2xl font-bold">
                {{ authStore.user?.name.charAt(0).toUpperCase() }}
              </div>
              <div>
                <h3 class="font-semibold">{{ authStore.user?.name }}</h3>
                <p class="text-sm text-gray-500">{{ authStore.user?.email }}</p>
              </div>
            </div>

            <nav class="space-y-2">
              <button
                v-for="tab in tabs"
                :key="tab.id"
                @click="activeTab = tab.id"
                :class="[
                  'w-full text-left px-4 py-2 rounded-lg transition-colors',
                  activeTab === tab.id ? 'bg-gold-500 text-white' : 'text-gray-700 hover:bg-gray-100'
                ]"
              >
                {{ tab.label }}
              </button>
            </nav>

            <button
              @click="handleLogout"
              class="w-full mt-6 text-left px-4 py-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors"
            >
              Logout
            </button>
          </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1">
          <!-- Profile Tab -->
          <div v-if="activeTab === 'profile'" class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-xl font-semibold mb-6">Profile Information</h2>
            <form @submit.prevent="updateProfile">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                  <input
                    v-model="profileForm.name"
                    type="text"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gold-500 focus:border-transparent"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                  <input
                    v-model="profileForm.email"
                    type="email"
                    disabled
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100 text-gray-500"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Phone</label>
                  <input
                    v-model="profileForm.phone"
                    type="tel"
                    minlength="10"
                    maxlength="10"
                    inputmode="numeric"
                    pattern="[0-9]{10}"
                    title="Phone number must be exactly 10 digits"
                    placeholder="9800000000"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gold-500 focus:border-transparent"
                  />
                </div>
              </div>
              <button
                type="submit"
                :disabled="loading"
                class="mt-6 bg-luxury-black text-white px-6 py-2 rounded-lg hover:bg-gray-800 transition-colors disabled:opacity-50"
              >
                {{ loading ? 'Saving...' : 'Save Changes' }}
              </button>

              <div v-if="profileMessage" class="mt-4 p-3 rounded-lg text-sm" :class="profileMessageType === 'success' ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-600'">
                {{ profileMessage }}
              </div>
            </form>
          </div>

          <!-- Orders Tab -->
          <div v-if="activeTab === 'orders'" class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-xl font-semibold mb-6">Order History</h2>
            <div v-if="orders.length === 0" class="text-center py-12 text-gray-500">
              No orders yet
            </div>
            <div v-else class="space-y-4">
              <div
                v-for="order in orders"
                :key="order.id"
                class="border rounded-lg p-4 hover:shadow-md transition-shadow"
              >
                <div class="flex justify-between items-start mb-4">
                  <div>
                    <h3 class="font-semibold">Order #{{ order.order_number }}</h3>
                    <p class="text-sm text-gray-500">{{ new Date(order.created_at).toLocaleDateString() }}</p>
                  </div>
                  <span
                    :class="[
                      'px-3 py-1 rounded-full text-sm',
                      order.status === 'completed' ? 'bg-green-100 text-green-800' :
                      order.status === 'pending' ? 'bg-yellow-100 text-yellow-800' :
                      order.status === 'cancelled' ? 'bg-red-100 text-red-800' :
                      'bg-gray-100 text-gray-800'
                    ]"
                  >
                    {{ order.status_label }}
                  </span>
                </div>

                <!-- WhatsApp order support -->
                <a
                  v-if="orderWhatsAppLink(order)"
                  :href="orderWhatsAppLink(order)"
                  target="_blank"
                  rel="noopener noreferrer"
                  class="inline-flex items-center gap-2 text-sm font-medium text-[#128C7E] hover:text-[#1fb958] mb-4 transition-colors"
                >
                  <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                  </svg>
                  Get Order Support on WhatsApp
                </a>

                <div class="flex justify-between items-center mb-4">
                  <span class="text-gray-600">{{ order.items.length }} items</span>
                  <span class="font-semibold">Rs. {{ order.total.toLocaleString() }}</span>
                </div>

                <div class="space-y-2 mb-4">
                  <div v-for="item in order.items" :key="item.id" class="text-sm text-gray-600">
                    • {{ item.product_name }} × {{ item.quantity }}
                  </div>
                </div>

                <div v-if="order.status === 'completed'" class="border-t pt-4">
                  <div class="flex items-center justify-between mb-3">
                    <h4 class="font-medium">Share your experience</h4>
                    <button
                      @click="toggleFeedbackForm(order.id)"
                      class="text-sm text-gold-500 hover:text-gold-600"
                    >
                      {{ feedbackFormOrderId === order.id ? 'Cancel' : 'Leave feedback' }}
                    </button>
                  </div>

                  <div v-if="feedbackFormOrderId === order.id" class="space-y-4">
                    <div v-for="item in order.items" :key="item.id" class="rounded-lg bg-gray-50 p-4">
                      <div class="flex items-center justify-between mb-3">
                        <p class="text-sm font-medium text-gray-700">{{ item.product_name }}</p>
                        <button
                          v-if="feedbackProductId !== item.product_id"
                          @click="openFeedback(item.product_id)"
                          class="text-sm text-gold-500 hover:text-gold-600"
                        >
                          Review
                        </button>
                      </div>

                      <div v-if="feedbackProductId === item.product_id" class="space-y-3">
                        <div>
                          <label class="block text-sm font-medium text-gray-700 mb-1">Rating</label>
                          <select v-model="feedbackForm.rating" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                            <option :value="5">5 - Excellent</option>
                            <option :value="4">4 - Very Good</option>
                            <option :value="3">3 - Good</option>
                            <option :value="2">2 - Fair</option>
                            <option :value="1">1 - Poor</option>
                          </select>
                        </div>
                        <div>
                          <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                          <input v-model="feedbackForm.title" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg" />
                        </div>
                        <div>
                          <label class="block text-sm font-medium text-gray-700 mb-1">Comment</label>
                          <textarea v-model="feedbackForm.comment" rows="3" minlength="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg"></textarea>
                        </div>
                        <div class="flex items-center gap-3">
                          <button
                            @click="submitFeedback(item.product_id)"
                            :disabled="feedbackSubmitting"
                            class="bg-luxury-black text-white px-4 py-2 rounded-lg hover:bg-gray-800 disabled:opacity-50"
                          >
                            {{ feedbackSubmitting ? 'Submitting...' : 'Submit review' }}
                          </button>
                        </div>
                        <p
                          v-if="feedbackMessage"
                          :class="feedbackMessageType === 'error' ? 'text-red-500' : 'text-green-600'"
                          class="text-sm"
                        >{{ feedbackMessage }}</p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Addresses Tab -->
          <div v-if="activeTab === 'addresses'" class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex justify-between items-center mb-6">
              <h2 class="text-xl font-semibold">Shipping Addresses</h2>
              <button
                @click="showAddressForm = true"
                class="bg-gold-500 text-white px-4 py-2 rounded-lg hover:bg-gold-600 transition-colors"
              >
                Add New
              </button>
            </div>
            
            <div v-if="showAddressForm" class="mb-6 p-4 bg-gray-50 rounded-lg">
              <form @submit.prevent="saveAddress">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Label</label>
                    <input
                      v-model="addressForm.label"
                      type="text"
                      placeholder="Home, Office, etc."
                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gold-500 focus:border-transparent"
                    />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                    <input
                      v-model="addressForm.full_name"
                      type="text"
                      required
                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gold-500 focus:border-transparent"
                    />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Phone</label>
                    <input
                      v-model="addressForm.phone"
                      type="tel"
                      required
                      inputmode="numeric"
                      pattern="[0-9]{10}"
                      title="10-digit phone number"
                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gold-500 focus:border-transparent"
                    />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">District</label>
                    <select
                      v-model="addressForm.district"
                      required
                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gold-500 focus:border-transparent"
                    >
                      <option value="">Select district</option>
                      <option v-for="d in districts" :key="d.id" :value="d.name">{{ d.name }}</option>
                    </select>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Municipality</label>
                    <input
                      v-model="addressForm.municipality"
                      type="text"
                      required
                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gold-500 focus:border-transparent"
                    />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Ward</label>
                    <input
                      v-model="addressForm.ward"
                      type="text"
                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gold-500 focus:border-transparent"
                    />
                  </div>
                  <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Street Address</label>
                    <input
                      v-model="addressForm.street"
                      type="text"
                      required
                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gold-500 focus:border-transparent"
                    />
                  </div>
                </div>
                <div class="flex gap-2 mt-4">
                  <button
                    type="submit"
                    :disabled="savingAddress"
                    class="bg-luxury-black text-white px-4 py-2 rounded-lg hover:bg-gray-800 transition-colors disabled:opacity-50"
                  >
                    {{ savingAddress ? 'Saving…' : 'Save' }}
                  </button>
                  <button
                    type="button"
                    @click="showAddressForm = false"
                    class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-100 transition-colors"
                  >
                    Cancel
                  </button>
                </div>
              </form>
            </div>

            <div v-if="addresses.length === 0" class="text-center py-12 text-gray-500">
              No addresses saved
            </div>
            <div v-else class="space-y-4">
              <div
                v-for="address in addresses"
                :key="address.id"
                class="border rounded-lg p-4 hover:shadow-md transition-shadow"
              >
                <div class="flex justify-between items-start">
                  <div>
                    <h3 class="font-semibold">
                      {{ address.label || 'Address' }}
                      <span v-if="address.is_default" class="ml-2 text-xs bg-gold-50 text-gold-700 px-2 py-0.5 rounded-full">Default</span>
                    </h3>
                    <p class="text-gray-600 mt-1">
                      {{ address.full_name }}<br>
                      {{ address.phone }}<br>
                      {{ address.street }}<br>
                      {{ address.municipality }}<span v-if="address.ward">, Ward {{ address.ward }}</span><br>
                      {{ address.district }}
                    </p>
                  </div>
                  <button
                    @click="deleteAddress(address.id)"
                    class="text-gray-400 hover:text-red-500 transition-colors"
                    aria-label="Delete address"
                  >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </main>
      </div>
    </div>
    </ClientOnly>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '~/stores/auth'
import { useApi } from '~/composables/useApi'
import { useSiteSettings } from '~/composables/useSiteSettings'
import { useWishlistStore } from '~/stores/wishlist'
import { useToast } from '~/composables/useToast'
import type { Order, Address, ShippingDistrict } from '~/types'

useSeoMeta({ title: 'My Account', robots: 'noindex' })

const router = useRouter()
const route = useRoute()
const toast = useToast()
const wishlistStore = useWishlistStore()
const districts = ref<ShippingDistrict[]>([])
const savingAddress = ref(false)
const authStore = useAuthStore()
const api = useApi()
const { fetchSettings, buildWhatsAppLink } = useSiteSettings()

const activeTab = ref(['profile', 'orders', 'addresses'].includes(route.query.tab as string) ? (route.query.tab as string) : 'profile')
const loading = ref(false)
const showAddressForm = ref(false)
const profileMessage = ref('')
const profileMessageType = ref<'success' | 'error'>('success')

const tabs = [
  { id: 'profile', label: 'Profile' },
  { id: 'orders', label: 'Orders' },
  { id: 'addresses', label: 'Addresses' }
]

const profileForm = ref({
  name: '',
  email: '',
  phone: ''
})

const orders = ref<Order[]>([])
const addresses = ref<Address[]>([])
const feedbackFormOrderId = ref<number | null>(null)
const feedbackProductId = ref<number | null>(null)
const feedbackSubmitting = ref(false)
const feedbackMessage = ref('')
const feedbackMessageType = ref<'success' | 'error'>('success')
const feedbackForm = ref({
  rating: 5,
  title: '',
  comment: ''
})

const addressForm = ref({
  label: '',
  full_name: '',
  phone: '',
  district: '',
  municipality: '',
  ward: '',
  street: ''
})

async function fetchProfile() {
  try {
    const user = await api.get('/v1/user/profile')
    profileForm.value = {
      name: user.name,
      email: user.email,
      phone: user.phone
    }
  } catch (error) {
    console.error('Failed to fetch profile:', error)
  }
}

async function updateProfile() {
  profileMessage.value = ''

  if (!/^\d{10}$/.test(profileForm.value.phone.trim())) {
    profileMessage.value = 'Phone number must be exactly 10 digits'
    profileMessageType.value = 'error'
    return
  }

  loading.value = true
  try {
    const updated = await api.put('/v1/user/profile', {
      ...profileForm.value,
      phone: profileForm.value.phone.trim(),
    })
    authStore.user!.name = updated.name
    authStore.user!.phone = updated.phone
    profileMessage.value = 'Profile updated successfully. Your admin panel will reflect the changes.'
    profileMessageType.value = 'success'
  } catch (error: any) {
    profileMessage.value = error.message || 'Failed to update profile.'
    profileMessageType.value = 'error'
  } finally {
    loading.value = false
  }
}

async function fetchOrders() {
  try {
    const response = await api.get<{ data: Order[] }>('/v1/orders')
    orders.value = response.data
  } catch (error) {
    console.error('Failed to fetch orders:', error)
  }
}

// Build a WhatsApp order-support link for a single order, using the WhatsApp
// number configured in the website settings. Returns null when unconfigured.
function orderWhatsAppLink(order: Order): string | null {
  const customerName = authStore.user?.name || '—'
  const orderNumber = order.order_number
  const orderStatus = order.status_label || order.status
  const message = [
    'Hello! I need support with my order:',
    '',
    `• Order Number: ${orderNumber}`,
    `• Customer Name: ${customerName}`,
    `• Order Status: ${orderStatus}`,
  ].join('\n')
  return buildWhatsAppLink(message)
}

function toggleFeedbackForm(orderId: number) {
  const isOpen = feedbackFormOrderId.value === orderId
  feedbackFormOrderId.value = isOpen ? null : orderId
  feedbackProductId.value = null
  feedbackForm.value = { rating: 5, title: '', comment: '' }
  feedbackMessage.value = ''
}

function openFeedback(productId: number) {
  feedbackProductId.value = productId
  feedbackForm.value = { rating: 5, title: '', comment: '' }
  feedbackMessage.value = ''
}

async function submitFeedback(productId?: number) {
  if (!productId) return
  if (!feedbackForm.value.comment || feedbackForm.value.comment.trim().length < 3) {
    feedbackMessage.value = 'Please write a review (at least 3 characters).'
    feedbackMessageType.value = 'error'
    return
  }

  feedbackSubmitting.value = true
  feedbackMessage.value = ''
  try {
    await api.post('/v1/reviews', {
      product_id: productId,
      rating: feedbackForm.value.rating,
      title: feedbackForm.value.title,
      comment: feedbackForm.value.comment,
    })

    feedbackForm.value = { rating: 5, title: '', comment: '' }
    feedbackProductId.value = null
    feedbackMessage.value = 'Thank you for your feedback! It is pending approval.'
    feedbackMessageType.value = 'success'
  } catch (error: any) {
    // Handle the "already reviewed" case (HTTP 409) gracefully.
    feedbackMessage.value = error?.message || 'Failed to submit feedback.'
    feedbackMessageType.value = 'error'
  } finally {
    feedbackSubmitting.value = false
  }
}

async function fetchAddresses() {
  try {
    const [addr, dist] = await Promise.all([
      api.get<{ data: Address[] }>('/v1/user/addresses'),
      districts.value.length ? Promise.resolve({ data: districts.value }) : api.get<{ data: ShippingDistrict[] }>('/v1/shipping/districts'),
    ])
    addresses.value = addr.data || []
    districts.value = dist.data || []
  } catch (error) {
    console.error('Failed to fetch addresses:', error)
  }
}

async function saveAddress() {
  savingAddress.value = true
  try {
    await api.post('/v1/user/addresses', { ...addressForm.value, label: addressForm.value.label || 'Home' })
    toast.success('Address saved')
    showAddressForm.value = false
    addressForm.value = {
      label: '',
      full_name: '',
      phone: '',
      district: '',
      municipality: '',
      ward: '',
      street: ''
    }
    fetchAddresses()
  } catch (error: any) {
    toast.error(error.message || 'Failed to save address')
  } finally {
    savingAddress.value = false
  }
}

async function deleteAddress(id: number) {
  try {
    await api.delete(`/v1/user/addresses/${id}`)
    // Refetch: deleting the default promotes another address server-side.
    await fetchAddresses()
    toast.success('Address deleted')
  } catch (error: any) {
    toast.error(error.message || 'Failed to delete address')
  }
}

async function handleLogout() {
  try {
    await api.post('/v1/auth/logout')
  } catch {}
  authStore.logout()
  wishlistStore.clear()
  router.push('/')
}

function loadTabData() {
  if (activeTab.value === 'profile') fetchProfile()
  if (activeTab.value === 'orders') fetchOrders()
  if (activeTab.value === 'addresses') fetchAddresses()
}

onMounted(() => {
  if (authStore.isAuthenticated) {
    loadTabData()
    if (activeTab.value !== 'profile') fetchProfile()
  }
  // Load site settings (WhatsApp number) so order-support buttons can render.
  fetchSettings()
})

watch(activeTab, loadTabData)
</script>
