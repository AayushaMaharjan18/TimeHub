<template>
  <div class="min-h-screen bg-gray-50">
    <!-- All content here is edited in Admin → Settings → Site Settings → About Page -->
    <div class="bg-luxury-black text-white py-16 text-center relative overflow-hidden">
      <div class="absolute -right-24 -top-24 w-80 h-80 rounded-full border border-gold-500/20" />
      <div v-reveal class="container-premium relative">
        <p class="text-gold-500 text-xs tracking-[0.3em] uppercase mb-2">About Us</p>
        <h1 class="text-4xl md:text-5xl font-display font-bold">{{ settings?.brand_name ? `About ${prettyName}` : 'About Us' }}</h1>
      </div>
    </div>

    <!-- Story -->
    <section v-if="about.content || about.image" class="container-premium py-16">
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
        <div v-reveal="'left'">
          <h2 class="text-3xl font-display font-bold mb-6">{{ about.title || 'Our Story' }}</h2>
          <div class="rich-content" v-html="about.content" />
        </div>
        <div v-if="about.image" v-reveal="{ preset: 'right', delay: 150 }" class="aspect-video rounded-2xl overflow-hidden shadow-premium-xl">
          <img :src="about.image" alt="" class="w-full h-full object-cover hover:scale-105 transition-transform duration-1000" />
        </div>
      </div>
    </section>

    <!-- Values -->
    <section v-if="about.values.length" class="bg-white py-16">
      <div class="container-premium">
        <h2 v-reveal class="text-3xl font-display font-bold text-center mb-12">Our Values</h2>
        <div v-reveal-stagger class="grid grid-cols-1 md:grid-cols-3 gap-8">
          <div v-for="(value, i) in about.values" :key="i" class="text-center p-8 rounded-2xl hover:bg-gray-50 transition-colors">
            <div class="w-16 h-16 bg-gold-50 text-gold-600 rounded-full flex items-center justify-center mx-auto mb-4 font-display text-2xl font-bold">
              {{ String(i + 1).padStart(2, '0') }}
            </div>
            <h3 class="text-xl font-semibold mb-2">{{ value.title }}</h3>
            <p v-if="value.description" class="text-gray-600">{{ value.description }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Stats -->
    <section v-if="about.stats.length" class="bg-luxury-black text-white py-16">
      <div v-reveal-stagger class="container-premium grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
        <div v-for="(stat, i) in about.stats" :key="i">
          <div v-count-up class="text-4xl md:text-5xl font-display font-bold text-gold-500 mb-2">{{ stat.value }}</div>
          <div class="text-gray-400">{{ stat.label }}</div>
        </div>
      </div>
    </section>

    <!-- Team -->
    <section v-if="about.team.length" class="container-premium py-16">
      <h2 v-reveal class="text-3xl font-display font-bold text-center mb-12">Meet Our Team</h2>
      <div v-reveal-stagger class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-8">
        <div v-for="(member, i) in about.team" :key="i" class="bg-white rounded-2xl shadow-premium overflow-hidden group">
          <div class="aspect-square bg-gray-100 overflow-hidden">
            <img v-if="member.photo" :src="member.photo" :alt="member.name" loading="lazy" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700" />
            <div v-else class="w-full h-full flex items-center justify-center font-display text-5xl text-gray-300">{{ member.name?.[0] }}</div>
          </div>
          <div class="p-6 text-center">
            <h3 class="font-semibold text-lg">{{ member.name }}</h3>
            <p v-if="member.role" class="text-gray-500">{{ member.role }}</p>
          </div>
        </div>
      </div>
    </section>

    <section class="container-premium pb-16 text-center">
      <div v-reveal class="bg-white rounded-3xl shadow-premium p-10">
        <h2 class="text-2xl md:text-3xl font-display font-bold mb-3">Find your next timepiece</h2>
        <p class="text-gray-500 mb-6">Browse the full collection or talk to our team.</p>
        <div class="flex flex-col sm:flex-row gap-3 justify-center">
          <NuxtLink to="/shop" class="btn-gold">Shop watches</NuxtLink>
          <NuxtLink to="/contact" class="btn-outline">Contact us</NuxtLink>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useApi } from '~/composables/useApi'
import type { SiteFooterData } from '~/composables/useSiteSettings'

useSeoMeta({ title: 'About Us' })

const api = useApi()
const { data: settings } = await useAsyncData('site-settings', () => api.get<SiteFooterData>('/v1/settings'))

const about = computed(() => ({
  title: settings.value?.about?.title ?? null,
  content: settings.value?.about?.content ?? null,
  image: settings.value?.about?.image ?? null,
  values: settings.value?.about?.values ?? [],
  stats: settings.value?.about?.stats ?? [],
  team: settings.value?.about?.team ?? [],
}))

const prettyName = computed(() => {
  const name = settings.value?.brand_name || ''
  return name === name.toUpperCase() ? name.charAt(0) + name.slice(1).toLowerCase().replace(/store$/, 'Store') : name
})
</script>
