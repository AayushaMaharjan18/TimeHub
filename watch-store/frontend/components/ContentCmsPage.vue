<template>
  <div class="min-h-screen bg-gray-50">
    <div class="container mx-auto px-4 py-12">
      <div v-if="loading" class="max-w-3xl mx-auto space-y-4 animate-pulse">
        <div class="h-8 bg-gray-200 rounded w-1/3 mb-6"></div>
        <div class="h-32 bg-white rounded-lg shadow-sm"></div>
        <div class="h-32 bg-white rounded-lg shadow-sm"></div>
      </div>

      <div v-else-if="error" class="max-w-3xl mx-auto text-center py-16">
        <h1 class="text-2xl font-serif mb-3">{{ fallbackTitle }}</h1>
        <p class="text-gray-600 mb-6">{{ error }}</p>
        <button
          @click="load"
          class="inline-block bg-luxury-black text-white px-6 py-2 rounded-lg hover:bg-gray-800 transition-colors"
        >
          Try Again
        </button>
      </div>

      <template v-else-if="page">
        <h1 class="text-3xl font-serif mb-8">{{ page.title }}</h1>
        <div
          class="max-w-3xl mx-auto space-y-6 cms-content bg-white rounded-lg shadow-sm p-6 md:p-8"
          v-html="page.content"
        />
      </template>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'

const props = defineProps<{ slug: string; fallbackTitle: string }>()

interface CmsPage {
  slug: string
  title: string
  content: string
  updated_at: string
}

const api = useApi()
const page = ref<CmsPage | null>(null)
const loading = ref(true)
const error = ref('')

async function load() {
  loading.value = true
  error.value = ''
  try {
    const response = await api.get<{ success: boolean; data: CmsPage }>(`/v1/content/${props.slug}`)
    page.value = response.data
  } catch (e: any) {
    error.value = e?.message === 'Page not found'
      ? 'This page is not available right now. Please check back later.'
      : 'We could not load this page. Please check your connection and try again.'
  } finally {
    loading.value = false
  }
}

onMounted(load)
</script>

<style scoped>
.cms-content :deep(h2) {
  font-size: 1.25rem;
  font-weight: 600;
  margin-top: 1.5rem;
  margin-bottom: 0.75rem;
}
.cms-content :deep(h2:first-child) {
  margin-top: 0;
}
.cms-content :deep(p) {
  color: #4b5563;
  margin-bottom: 0.75rem;
}
.cms-content :deep(ul),
.cms-content :deep(ol) {
  color: #4b5563;
  margin-bottom: 0.75rem;
  padding-left: 1.5rem;
}
.cms-content :deep(ul) {
  list-style: disc;
}
.cms-content :deep(ol) {
  list-style: decimal;
}
.cms-content :deep(li) {
  margin-bottom: 0.4rem;
}
.cms-content :deep(table) {
  width: 100%;
  text-align: left;
  border-collapse: collapse;
  margin-bottom: 0.75rem;
}
.cms-content :deep(th),
.cms-content :deep(td) {
  padding: 0.5rem 0;
  border-bottom: 1px solid #e5e7eb;
}
</style>
