<template>
  <div class="min-h-screen bg-gray-50">
    <div class="bg-luxury-black text-white py-16 text-center relative overflow-hidden">
      <div class="absolute -left-24 -top-24 w-80 h-80 rounded-full border border-gold-500/20" />
      <div v-reveal class="container-premium relative">
        <p class="text-gold-500 text-xs tracking-[0.3em] uppercase mb-2">Contact</p>
        <h1 class="text-4xl md:text-5xl font-display font-bold">We'd love to hear from you</h1>
      </div>
    </div>
    <div class="container mx-auto px-4 py-12">
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
        <!-- Contact Form (submissions appear in Admin → Customers → Contact Messages) -->
        <div v-reveal="'left'" class="bg-white rounded-2xl shadow-premium p-8">
          <h2 class="text-2xl font-serif mb-6">Send us a Message</h2>
          <form @submit.prevent="submitForm">
            <div class="mb-6">
              <label class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
              <input
                v-model="form.name"
                type="text"
                required
                placeholder="Your name"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gold-500 focus:border-transparent"
              />
            </div>

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
              <label class="block text-sm font-medium text-gray-700 mb-2">Phone</label>
              <input
                v-model="form.phone"
                type="tel"
                placeholder="+977 9800000000"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gold-500 focus:border-transparent"
              />
            </div>

            <div class="mb-6">
              <label class="block text-sm font-medium text-gray-700 mb-2">Subject</label>
              <select
                v-model="form.subject"
                required
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gold-500 focus:border-transparent"
              >
                <option value="">Select a subject</option>
                <option value="general">General Inquiry</option>
                <option value="order">Order Related</option>
                <option value="product">Product Information</option>
                <option value="service">Service & Repair</option>
                <option value="other">Other</option>
              </select>
            </div>

            <div class="mb-6">
              <label class="block text-sm font-medium text-gray-700 mb-2">Message</label>
              <textarea
                v-model="form.message"
                required
                rows="5"
                placeholder="Your message..."
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gold-500 focus:border-transparent resize-none"
              ></textarea>
            </div>

            <button
              type="submit"
              :disabled="loading"
              class="w-full bg-luxury-black text-white py-3 rounded-lg hover:bg-gray-800 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {{ loading ? 'Sending...' : 'Send Message' }}
            </button>

            <div v-if="success" class="mt-4 p-3 bg-green-50 text-green-600 rounded-lg text-sm">
              {{ success }}
            </div>

            <div v-if="error" class="mt-4 p-3 bg-red-50 text-red-600 rounded-lg text-sm">
              {{ error }}
            </div>
          </form>
        </div>

        <!-- Contact Information (Admin → Settings → Site Settings → Contact) -->
        <div v-reveal="{ preset: 'right', delay: 100 }">
          <div class="bg-white rounded-2xl shadow-premium p-8 mb-6">
            <h2 class="text-2xl font-display font-semibold mb-6">Contact Information</h2>
            <div class="space-y-6">
              <div v-if="footer.address" class="contact-row">
                <span class="contact-icon">⌖</span>
                <div>
                  <h3 class="font-semibold mb-1">Store Location</h3>
                  <p class="text-gray-600 whitespace-pre-line">{{ footer.address }}</p>
                </div>
              </div>
              <div v-if="footer.phone || footer.whatsapp_number" class="contact-row">
                <span class="contact-icon">☏</span>
                <div>
                  <h3 class="font-semibold mb-1">Phone</h3>
                  <p v-if="footer.phone" class="text-gray-600"><a :href="`tel:${footer.phone}`" class="hover:text-gold-600">{{ footer.phone }}</a></p>
                  <p v-if="whatsappLink" class="text-gray-600"><a :href="whatsappLink" target="_blank" rel="noopener" class="hover:text-gold-600">WhatsApp: {{ footer.whatsapp_number }}</a></p>
                </div>
              </div>
              <div v-if="footer.email" class="contact-row">
                <span class="contact-icon">✉</span>
                <div>
                  <h3 class="font-semibold mb-1">Email</h3>
                  <p class="text-gray-600"><a :href="`mailto:${footer.email}`" class="hover:text-gold-600">{{ footer.email }}</a></p>
                </div>
              </div>
              <div v-if="footer.business_hours" class="contact-row">
                <span class="contact-icon">◷</span>
                <div>
                  <h3 class="font-semibold mb-1">Business Hours</h3>
                  <p class="text-gray-600 whitespace-pre-line">{{ footer.business_hours }}</p>
                </div>
              </div>
            </div>
          </div>

          <div v-if="safeMapUrl" class="bg-white rounded-2xl shadow-premium overflow-hidden">
            <iframe
              :src="safeMapUrl"
              class="w-full aspect-video border-0"
              loading="lazy"
              referrerpolicy="no-referrer-when-downgrade"
              title="Store location map"
              allowfullscreen
            />
          </div>

          <div class="mt-6 flex gap-3">
            <a v-for="s in socials" :key="s.label" :href="s.url" target="_blank" rel="noopener" :aria-label="s.label"
               class="w-10 h-10 bg-luxury-black text-white rounded-full flex items-center justify-center text-sm font-semibold hover:bg-gold-500 hover:-translate-y-1 transition-all">
              {{ s.short }}
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useApi } from '~/composables/useApi'
import { useSiteSettings } from '~/composables/useSiteSettings'

useSeoMeta({ title: 'Contact Us' })

const api = useApi()
const { footer, fetchSettings, buildWhatsAppLink } = useSiteSettings()
onMounted(() => fetchSettings())

const whatsappLink = computed(() => buildWhatsAppLink('Hello! I have a question.'))

// The map URL is typed in by an admin; only embed Google Maps.
const safeMapUrl = computed(() => {
  try {
    const url = new URL(footer.value.map_embed_url || '')
    return url.protocol === 'https:' && /(^|\.)google\.[a-z.]+$/.test(url.hostname) && url.pathname.startsWith('/maps')
      ? url.toString()
      : null
  } catch {
    return null
  }
})

const socials = computed(() => [
  { label: 'Facebook', short: 'f', url: footer.value.facebook_url },
  { label: 'Instagram', short: 'ig', url: footer.value.instagram_url },
  { label: 'X / Twitter', short: 'x', url: footer.value.twitter_url },
].filter(s => s.url))

const form = ref({
  name: '',
  email: '',
  phone: '',
  subject: '',
  message: ''
})

const loading = ref(false)
const success = ref('')
const error = ref('')

async function submitForm() {
  loading.value = true
  success.value = ''
  error.value = ''
  
  try {
    const res = await api.post<{ message: string }>('/v1/contact', form.value)
    success.value = res.message || 'Message sent successfully! We will get back to you soon.'
    form.value = {
      name: '',
      email: '',
      phone: '',
      subject: '',
      message: ''
    }
  } catch (err: any) {
    error.value = err.message || 'Failed to send message. Please try again.'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.contact-row {
  @apply flex items-start gap-4;
}
.contact-icon {
  @apply w-12 h-12 bg-gold-50 text-gold-600 rounded-xl flex items-center justify-center flex-shrink-0 text-xl;
}
</style>
