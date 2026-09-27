<template>
  <div v-if="loading" class="flex justify-center py-12">
    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-gold-500"></div>
  </div>

  <div v-else-if="reviews.length === 0" class="text-center py-12">
    <p class="text-gray-400">Featured customer reviews will appear here soon.</p>
  </div>

  <Swiper
    v-else
    :modules="[SwiperAutoplay, SwiperPagination]"
    :slides-per-view="1"
    :space-between="24"
    :autoplay="{ delay: 5000, disableOnInteraction: true }"
    :pagination="{ clickable: true }"
    :breakpoints="{
      768: { slidesPerView: 2 },
      1024: { slidesPerView: 3 },
    }"
    class="review-carousel pb-12"
  >
    <SwiperSlide v-for="review in reviews" :key="review.id">
      <div class="card-premium p-8 text-center h-full">
        <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gold-50 text-gold-600 flex items-center justify-center font-display text-xl font-semibold">
          {{ initials(review.user?.name) }}
        </div>
        <div class="flex justify-center gap-1 mb-4">
          <svg v-for="i in 5" :key="i" class="w-4 h-4" :class="i <= review.rating ? 'text-gold-500' : 'text-gray-200'" fill="currentColor" viewBox="0 0 20 20">
            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
          </svg>
        </div>
        <p class="text-gray-600 italic mb-4">&ldquo;{{ review.comment }}&rdquo;</p>
        <p class="font-semibold">{{ review.user?.name || 'Verified Customer' }}</p>
        <p class="text-sm text-gray-400">{{ review.product?.name || 'Verified Buyer' }}</p>
        <p class="text-xs text-gray-400 mt-1">{{ formatDate(review.created_at) }}</p>
      </div>
    </SwiperSlide>
  </Swiper>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { Swiper, SwiperSlide } from 'swiper/vue'
import { Autoplay, Pagination } from 'swiper/modules'
import 'swiper/css'
import 'swiper/css/pagination'
import { useApi } from '~/composables/useApi'
import type { Review } from '~/types'

const SwiperAutoplay = Autoplay
const SwiperPagination = Pagination
const api = useApi()

const reviews = ref<Review[]>([])
const loading = ref(true)

function initials(name?: string | null): string {
  return (name || 'Customer').split(/\s+/).map(w => w[0]).slice(0, 2).join('').toUpperCase()
}

function formatDate(value: string): string {
  if (!value) return ''
  const d = new Date(value)
  if (Number.isNaN(d.getTime())) return value
  return d.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })
}

async function fetchReviews() {
  loading.value = true
  try {
    const response = await api.get<{ data: Review[] }>('/v1/reviews')
    reviews.value = response.data || []
  } catch (error) {
    console.error('Failed to load reviews:', error)
    reviews.value = []
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchReviews()
})
</script>