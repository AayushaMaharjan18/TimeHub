<template>
  <Swiper
    :modules="[SwiperAutoplay]"
    :slides-per-view="'auto'"
    :space-between="32"
    :autoplay="{ delay: 3000, disableOnInteraction: true }"
    :loop="brands.length > 5"
    class="brand-carousel"
  >
    <SwiperSlide v-for="brand in brands" :key="brand.id" class="!w-[180px]">
      <NuxtLink :to="`/shop?brand=${brand.slug}`" class="block">
        <div class="h-24 bg-white rounded-2xl shadow-premium flex items-center justify-center p-6 hover:shadow-premium-lg hover:-translate-y-1 transition-all duration-500 group">
          <img
            v-if="brand.logo"
            :src="brand.logo"
            :alt="brand.name"
            class="max-h-full max-w-full object-contain grayscale group-hover:grayscale-0 transition-all duration-300"
          />
          <span v-else class="font-display text-lg font-semibold text-gray-500 group-hover:text-gold-500 transition-colors text-center">{{ brand.name }}</span>
        </div>
        <p class="text-center text-sm font-medium mt-3 text-gray-600">{{ brand.name }}</p>
      </NuxtLink>
    </SwiperSlide>
  </Swiper>
</template>

<script setup lang="ts">
import { Swiper, SwiperSlide } from 'swiper/vue'
import { Autoplay } from 'swiper/modules'
import 'swiper/css'
import type { Brand } from '~/types'

const SwiperAutoplay = Autoplay

defineProps<{
  brands: Brand[]
}>()
</script>