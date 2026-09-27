<template>
  <div class="min-h-screen bg-gray-50">
    <div v-if="pending" class="flex justify-center items-center py-24">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-gold-500" />
    </div>

    <div v-else-if="!blog" class="container-premium py-24 text-center">
      <h1 class="text-3xl font-display font-bold mb-3">Post not found</h1>
      <p class="text-gray-500 mb-6">This article may have been moved or unpublished.</p>
      <NuxtLink to="/blog" class="btn-primary">Back to the blog</NuxtLink>
    </div>

    <template v-else>
      <!-- Hero -->
      <header class="relative h-[50vh] min-h-[320px] max-h-[520px] overflow-hidden bg-luxury-black">
        <img v-if="blog.featured_image" v-parallax="0.3" :src="blog.featured_image" :alt="blog.title" class="absolute inset-0 w-full h-full object-cover opacity-60" />
        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/30 to-transparent" />
        <div class="absolute inset-x-0 bottom-0">
          <div v-reveal class="container-premium max-w-4xl pb-10">
            <nav class="text-sm mb-4 text-white/70">
              <NuxtLink to="/" class="hover:text-gold-500">Home</NuxtLink>
              <span class="mx-2">/</span>
              <NuxtLink to="/blog" class="hover:text-gold-500">Blog</NuxtLink>
            </nav>
            <h1 class="text-3xl md:text-5xl font-display font-bold text-white mb-4">{{ blog.title }}</h1>
            <p class="text-white/70 text-sm tracking-wide">
              <span v-if="blog.author">{{ blog.author }} · </span>{{ formatDate(blog.published_at) }} · {{ readingTime }} min read
            </p>
          </div>
        </div>
      </header>

      <article class="container-premium max-w-3xl py-12">
        <p v-if="blog.excerpt" v-reveal class="text-xl text-gray-600 leading-relaxed mb-8 font-display italic">{{ blog.excerpt }}</p>
        <div v-reveal="{ delay: 100 }" class="rich-content text-lg" v-html="blog.content" />

        <div class="mt-12 pt-8 border-t">
          <NuxtLink to="/blog" class="inline-flex items-center text-gold-600 hover:text-gold-700 font-medium group">
            <span class="mr-2 group-hover:-translate-x-1 transition-transform">←</span> Back to Blog
          </NuxtLink>
        </div>
      </article>

      <section v-if="related.length" class="bg-white py-16">
        <div class="container-premium">
          <h2 v-reveal class="text-2xl md:text-3xl font-display font-bold mb-8">Keep reading</h2>
          <div v-reveal-stagger class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <BlogCard v-for="post in related" :key="post.id" :blog="post" />
          </div>
        </div>
      </section>
    </template>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import type { Blog } from '~/types'
import { useApi } from '~/composables/useApi'

const api = useApi()
const route = useRoute()
const slug = computed(() => route.params.slug as string)

const { data, pending } = await useAsyncData(
  () => `blog-${slug.value}`,
  () => api.get<{ data: Blog; related: Blog[] }>(`/v1/blogs/${slug.value}`).catch(() => null),
)
const blog = computed(() => data.value?.data ?? null)
const related = computed(() => data.value?.related ?? [])

const readingTime = computed(() => {
  const words = (blog.value?.content ?? '').replace(/<[^>]+>/g, ' ').trim().split(/\s+/).length
  return Math.max(1, Math.round(words / 200))
})

function formatDate(value?: string | null) {
  if (!value) return ''
  const d = new Date(value)
  return Number.isNaN(d.getTime()) ? value : d.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })
}

useSeoMeta({
  title: () => blog.value?.meta_title || blog.value?.title || 'Blog',
  description: () => blog.value?.meta_description || blog.value?.excerpt || '',
  ogImage: () => blog.value?.featured_image || undefined,
})
</script>
