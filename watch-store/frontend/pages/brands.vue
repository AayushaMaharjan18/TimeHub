<template>
  <div class="min-h-screen bg-gray-50">
    <div class="bg-luxury-black text-white py-16 text-center relative overflow-hidden">
      <div class="absolute -left-24 -bottom-24 w-80 h-80 rounded-full border border-gold-500/20" />
      <div v-reveal class="container-premium relative">
        <p class="text-gold-500 text-xs tracking-[0.3em] uppercase mb-2">Our Brands</p>
        <h1 class="text-4xl md:text-5xl font-display font-bold">The Houses We Carry</h1>
        <p class="text-gray-400 mt-3 max-w-xl mx-auto">Authentic timepieces from the world's most respected watchmakers — and Nepal's finest.</p>
      </div>
    </div>

    <div class="container-premium py-12">
      <div v-if="pending" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <div v-for="i in 8" :key="i" class="h-72 skeleton-shimmer rounded-2xl" />
      </div>

      <div v-else-if="brands.length === 0" class="text-center py-12 text-gray-500">No brands available yet.</div>

      <div v-else v-reveal-stagger="{ preset: 'zoom', stagger: 70 }" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <NuxtLink
          v-for="brand in brands"
          :key="brand.id"
          :to="`/shop?brand=${brand.slug}`"
          class="bg-white rounded-2xl shadow-premium overflow-hidden group hover:shadow-premium-xl hover:-translate-y-1.5 transition-all duration-500"
        >
          <div class="aspect-[4/3] bg-gradient-to-br from-gray-50 to-gray-100 flex items-center justify-center p-10">
            <img
              v-if="brand.logo"
              :src="brand.logo"
              :alt="brand.name"
              loading="lazy"
              class="max-w-full max-h-full object-contain grayscale group-hover:grayscale-0 group-hover:scale-110 transition-all duration-700"
            />
            <span v-else class="font-display text-3xl font-bold text-gray-300 group-hover:text-gold-500 transition-colors">{{ brand.name }}</span>
          </div>
          <div class="p-6">
            <h2 class="text-xl font-display font-semibold mb-2 group-hover:text-gold-600 transition-colors">{{ brand.name }}</h2>
            <p v-if="brand.description" class="text-gray-500 text-sm mb-4 line-clamp-2">{{ brand.description }}</p>
            <div class="flex items-center justify-between text-sm">
              <span class="text-gray-400">{{ brand.products_count ?? 0 }} {{ brand.products_count === 1 ? 'watch' : 'watches' }}</span>
              <span class="text-gold-600 font-medium group-hover:translate-x-1 transition-transform">Shop →</span>
            </div>
          </div>
        </NuxtLink>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import type { Brand } from '~/types'
import { useApi } from '~/composables/useApi'

useSeoMeta({ title: 'Brands', description: 'Shop authentic watches by brand at WatchStore Nepal.' })

const api = useApi()
const { data, pending } = await useAsyncData('brands', () => api.get<{ data: Brand[] }>('/v1/brands'))
const brands = computed(() => data.value?.data ?? [])
</script>
