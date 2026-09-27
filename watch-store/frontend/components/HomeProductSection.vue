<template>
  <section class="section-padding" :class="{ 'bg-luxury-gray': alt }">
    <div class="container-premium">
      <div v-reveal class="mb-12" :class="subtitle ? 'text-center' : 'flex items-end justify-between gap-4'">
        <div>
          <span class="text-gold-500 text-sm font-medium tracking-widest uppercase">{{ eyebrow }}</span>
          <h2 class="text-3xl md:text-4xl font-display font-bold mt-2">{{ title }}</h2>
          <p v-if="subtitle" class="text-gray-500 mt-3 max-w-xl mx-auto">{{ subtitle }}</p>
        </div>
        <NuxtLink v-if="link && !subtitle" :to="link" class="btn-outline text-sm flex-shrink-0">View All</NuxtLink>
      </div>

      <div v-if="loading" class="flex gap-6 overflow-hidden">
        <div v-for="i in 4" :key="i" class="w-[280px] sm:w-[300px] flex-shrink-0">
          <div class="aspect-square skeleton-shimmer" />
          <div class="h-4 w-1/3 skeleton-shimmer mt-4" />
          <div class="h-4 w-2/3 skeleton-shimmer mt-2" />
        </div>
      </div>
      <div v-else v-reveal="{ delay: 120 }">
        <ProductCarousel :products="products" />
      </div>

      <div v-if="link && subtitle" v-reveal class="text-center mt-10">
        <NuxtLink :to="link" class="btn-outline text-sm">View All</NuxtLink>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import type { Product } from '~/types'

defineProps<{
  eyebrow: string
  title: string
  subtitle?: string
  products: Product[]
  loading?: boolean
  link?: string
  alt?: boolean
}>()
</script>
