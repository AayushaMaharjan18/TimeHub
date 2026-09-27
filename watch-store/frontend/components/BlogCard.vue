<template>
  <article class="card-premium overflow-hidden group hover:-translate-y-1 transition-transform duration-500">
    <NuxtLink :to="`/blog/${blog.slug}`" class="block">
      <div class="aspect-[16/10] overflow-hidden bg-gray-100">
        <img
          v-if="blog.featured_image"
          :src="blog.featured_image"
          :alt="blog.title"
          loading="lazy"
          class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700"
        />
      </div>
      <div class="p-6">
        <p class="text-xs text-gray-400 mb-2 uppercase tracking-wider">
          {{ formatDate(blog.published_at) }}<span v-if="blog.author"> · {{ blog.author }}</span>
        </p>
        <h3 class="text-lg font-display font-semibold mb-2 group-hover:text-gold-500 transition-colors">{{ blog.title }}</h3>
        <p v-if="blog.excerpt" class="text-gray-500 text-sm line-clamp-2">{{ blog.excerpt }}</p>
        <span class="inline-block mt-4 text-sm font-medium text-gold-600 link-underline">Read more</span>
      </div>
    </NuxtLink>
  </article>
</template>

<script setup lang="ts">
import type { Blog } from '~/types'

defineProps<{ blog: Blog }>()

function formatDate(value?: string | null) {
  if (!value) return ''
  const d = new Date(value)
  return Number.isNaN(d.getTime()) ? value : d.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })
}
</script>
