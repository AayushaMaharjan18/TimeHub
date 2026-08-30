<template>
  <div class="min-h-screen bg-gray-50">
    <div class="container mx-auto px-4 py-12">
      <h1 class="text-3xl font-serif mb-8">Frequently Asked Questions</h1>

      <div class="max-w-3xl mx-auto">
        <div v-if="!loading && !error" class="mb-6">
          <input
            v-model="search"
            type="search"
            placeholder="Search FAQs..."
            class="w-full px-4 py-3 rounded-lg border border-gray-200 focus:outline-none focus:ring-2 focus:ring-gold-500"
          />
        </div>

        <div v-if="loading" class="space-y-4 animate-pulse">
          <div v-for="n in 4" :key="n" class="h-16 bg-white rounded-lg shadow-sm"></div>
        </div>

        <div v-else-if="error" class="text-center py-16">
          <p class="text-gray-600 mb-6">{{ error }}</p>
          <button
            @click="load"
            class="inline-block bg-luxury-black text-white px-6 py-2 rounded-lg hover:bg-gray-800 transition-colors"
          >
            Try Again
          </button>
        </div>

        <div v-else-if="filteredCategories.length === 0" class="text-center py-16 text-gray-600">
          No FAQs match your search.
        </div>

        <div v-else class="space-y-8">
          <div v-for="category in filteredCategories" :key="category.id ?? 'general'">
            <h2 v-if="filteredCategories.length > 1" class="text-lg font-semibold mb-3 text-gray-700">
              {{ category.name }}
            </h2>
            <div class="space-y-4">
              <div
                v-for="item in category.items"
                :key="item.id"
                class="bg-white rounded-lg shadow-sm overflow-hidden"
              >
                <button
                  @click="toggle(item.id)"
                  class="w-full px-6 py-4 flex items-center justify-between text-left hover:bg-gray-50 transition-colors"
                >
                  <span class="font-medium">{{ item.question }}</span>
                  <svg
                    class="w-5 h-5 transition-transform flex-shrink-0 ml-4"
                    :class="openId === item.id ? 'rotate-180' : ''"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                  </svg>
                </button>
                <div v-show="openId === item.id" class="px-6 pb-4 text-gray-600" v-html="item.answer" />
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'

interface FaqItem {
  id: number
  question: string
  answer: string
}
interface FaqCategory {
  id: number | null
  name: string
  items: FaqItem[]
}

const api = useApi()
const categories = ref<FaqCategory[]>([])
const loading = ref(true)
const error = ref('')
const search = ref('')
const openId = ref<number | null>(null)

async function load() {
  loading.value = true
  error.value = ''
  try {
    const response = await api.get<{ success: boolean; data: FaqCategory[] }>('/v1/faqs')
    categories.value = response.data
  } catch (e: any) {
    error.value = 'We could not load the FAQ right now. Please check your connection and try again.'
  } finally {
    loading.value = false
  }
}

function toggle(id: number) {
  openId.value = openId.value === id ? null : id
}

const filteredCategories = computed<FaqCategory[]>(() => {
  const q = search.value.trim().toLowerCase()
  if (!q) return categories.value

  return categories.value
    .map((c) => ({
      ...c,
      items: c.items.filter(
        (i) => i.question.toLowerCase().includes(q) || i.answer.toLowerCase().includes(q)
      ),
    }))
    .filter((c) => c.items.length > 0)
})

onMounted(load)
</script>
