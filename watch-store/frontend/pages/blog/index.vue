<template>
  <div class="min-h-screen bg-gray-50">
    <div class="bg-luxury-black text-white py-16 text-center relative overflow-hidden">
      <div class="absolute -right-24 -top-24 w-80 h-80 rounded-full border border-gold-500/20" />
      <div v-reveal class="container-premium relative">
        <p class="text-gold-500 text-xs tracking-[0.3em] uppercase mb-2">Journal</p>
        <h1 class="text-4xl md:text-5xl font-display font-bold">Stories & Guides</h1>
        <p class="text-gray-400 mt-3 max-w-xl mx-auto">Watch knowledge, buying guides and news from our team.</p>
      </div>
    </div>

    <div class="container-premium py-12">
      <div v-if="pending" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <div v-for="i in 6" :key="i" class="h-80 skeleton-shimmer rounded-2xl" />
      </div>

      <div v-else-if="posts.length === 0" class="text-center py-16 text-gray-500">No posts yet — check back soon.</div>

      <template v-else>
        <!-- Featured (latest) post on the first page -->
        <NuxtLink
          v-if="page === 1 && posts[0]"
          v-reveal
          :to="`/blog/${posts[0].slug}`"
          class="grid md:grid-cols-2 bg-white rounded-3xl shadow-premium overflow-hidden mb-12 group"
        >
          <div class="aspect-[16/10] md:aspect-auto overflow-hidden bg-gray-100">
            <img v-if="posts[0].featured_image" :src="posts[0].featured_image" :alt="posts[0].title" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700" />
          </div>
          <div class="p-8 md:p-12 flex flex-col justify-center">
            <p class="text-xs text-gold-600 uppercase tracking-widest mb-3">Latest post</p>
            <h2 class="text-2xl md:text-3xl font-display font-bold mb-4 group-hover:text-gold-600 transition-colors">{{ posts[0].title }}</h2>
            <p v-if="posts[0].excerpt" class="text-gray-500 mb-6">{{ posts[0].excerpt }}</p>
            <span class="text-sm font-medium link-underline self-start">Read article</span>
          </div>
        </NuxtLink>

        <div v-reveal-stagger class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
          <BlogCard v-for="post in (page === 1 ? posts.slice(1) : posts)" :key="post.id" :blog="post" />
        </div>

        <nav v-if="lastPage > 1" class="flex justify-center gap-2 mt-12" aria-label="Pagination">
          <NuxtLink
            v-for="p in lastPage"
            :key="p"
            :to="{ query: p === 1 ? {} : { page: p } }"
            class="min-w-[2.5rem] h-10 px-3 rounded-lg flex items-center justify-center shadow-sm transition-colors"
            :class="p === page ? 'bg-luxury-black text-white' : 'bg-white hover:bg-gray-100'"
          >
            {{ p }}
          </NuxtLink>
        </nav>
      </template>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import type { Blog } from '~/types'
import { useApi } from '~/composables/useApi'

useSeoMeta({ title: 'Blog', description: 'Watch guides, stories and news from WatchStore Nepal.' })

const api = useApi()
const route = useRoute()
const page = computed(() => Number(route.query.page) || 1)

const { data, pending } = await useAsyncData(
  'blogs',
  () => api.get<{ data: Blog[]; meta: { last_page: number } }>('/v1/blogs', { page: String(page.value), per_page: '10' }),
  { watch: [page] },
)
const posts = computed(() => data.value?.data ?? [])
const lastPage = computed(() => data.value?.meta?.last_page ?? 1)
</script>
