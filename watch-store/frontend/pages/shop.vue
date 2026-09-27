<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Page header -->
    <div class="bg-luxury-black text-white py-14 relative overflow-hidden">
      <div class="absolute -right-20 -top-20 w-80 h-80 rounded-full border border-gold-500/20" />
      <div v-reveal class="container-premium relative">
        <p class="text-gold-500 text-xs tracking-[0.3em] uppercase mb-2">{{ activeBrand ? 'Brand' : 'Shop' }}</p>
        <h1 class="text-4xl md:text-5xl font-display font-bold">{{ pageTitle }}</h1>
        <p v-if="activeBrand?.description" class="text-gray-400 mt-3 max-w-2xl">{{ activeBrand.description }}</p>
      </div>
    </div>

    <div class="container-premium py-8">
      <!-- Mobile filter toggle -->
      <button class="lg:hidden mb-4 w-full btn-outline py-2" @click="filtersOpen = !filtersOpen">
        {{ filtersOpen ? 'Hide filters' : 'Show filters' }}
        <span v-if="activeFilterCount" class="ml-2 bg-gold-500 text-white text-xs rounded-full px-2 py-0.5">{{ activeFilterCount }}</span>
      </button>

      <div class="flex flex-col lg:flex-row gap-8">
        <!-- Filters -->
        <aside class="lg:w-64 flex-shrink-0" :class="{ 'hidden lg:block': !filtersOpen }">
          <form class="bg-white rounded-2xl shadow-premium p-6 lg:sticky lg:top-20 space-y-5" @submit.prevent="applyFilters">
            <div class="flex items-center justify-between">
              <h2 class="text-lg font-semibold">Filters</h2>
              <button type="button" class="text-sm text-gold-600 hover:text-gold-700" @click="clearFilters">Clear all</button>
            </div>

            <div>
              <label class="filter-label" for="f-search">Search</label>
              <input id="f-search" v-model="form.search" type="search" placeholder="Search watches…" class="filter-input" />
            </div>

            <div>
              <label class="filter-label" for="f-brand">Brand</label>
              <select id="f-brand" v-model="form.brand" class="filter-input">
                <option value="">All brands</option>
                <option v-for="b in brands" :key="b.id" :value="b.slug">{{ b.name }}</option>
              </select>
            </div>

            <div v-if="categories.length">
              <label class="filter-label" for="f-category">Category</label>
              <select id="f-category" v-model="form.category" class="filter-input">
                <option value="">All categories</option>
                <option v-for="c in categories" :key="c.id" :value="c.slug">{{ c.name }}</option>
              </select>
            </div>

            <div>
              <label class="filter-label" for="f-gender">Gender</label>
              <select id="f-gender" v-model="form.gender" class="filter-input">
                <option value="">All</option>
                <option value="men">Men</option>
                <option value="women">Women</option>
                <option value="unisex">Unisex</option>
              </select>
            </div>

            <div>
              <span class="filter-label">Price range (Rs.)</span>
              <div class="flex gap-2">
                <input v-model="form.min_price" type="number" min="0" placeholder="Min" aria-label="Minimum price" class="filter-input" />
                <input v-model="form.max_price" type="number" min="0" placeholder="Max" aria-label="Maximum price" class="filter-input" />
              </div>
            </div>

            <div class="space-y-2">
              <label v-for="flag in flagOptions" :key="flag.key" class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                <input v-model="form[flag.key]" type="checkbox" class="rounded text-gold-500 focus:ring-gold-500" />
                {{ flag.label }}
              </label>
            </div>

            <button type="submit" class="w-full btn-primary py-2.5">Apply filters</button>
          </form>
        </aside>

        <!-- Results -->
        <section class="flex-1 min-w-0">
          <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
            <p class="text-gray-600">
              <span v-if="!pending">{{ meta.total }} {{ meta.total === 1 ? 'watch' : 'watches' }}</span>
              <span v-else>Loading…</span>
            </p>
            <div class="flex items-center gap-2">
              <label for="f-sort" class="text-sm text-gray-500">Sort</label>
              <select id="f-sort" :value="query.sort" class="filter-input !w-auto" @change="setQuery({ sort: ($event.target as HTMLSelectElement).value, page: undefined })">
                <option value="newest">Newest</option>
                <option value="popular">Most popular</option>
                <option value="price_low">Price: low to high</option>
                <option value="price_high">Price: high to low</option>
              </select>
            </div>
          </div>

          <!-- Active filter chips -->
          <div v-if="chips.length" class="flex flex-wrap gap-2 mb-6">
            <button
              v-for="chip in chips"
              :key="chip.key"
              class="inline-flex items-center gap-1.5 bg-white border border-gray-200 rounded-full px-3 py-1 text-sm hover:border-gold-500 transition-colors"
              @click="setQuery({ [chip.key]: undefined, page: undefined })"
            >
              {{ chip.label }} <span class="text-gray-400">✕</span>
            </button>
          </div>

          <div v-if="pending" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6">
            <div v-for="i in 6" :key="i">
              <div class="aspect-square skeleton-shimmer" />
              <div class="h-4 w-1/3 skeleton-shimmer mt-4" />
              <div class="h-4 w-2/3 skeleton-shimmer mt-2" />
            </div>
          </div>

          <div v-else-if="error" class="text-center py-16 bg-white rounded-2xl">
            <p class="text-gray-500 mb-4">We couldn't load products right now.</p>
            <button class="btn-outline text-sm" @click="refresh()">Try again</button>
          </div>

          <div v-else-if="products.length === 0" class="text-center py-16 bg-white rounded-2xl">
            <p class="text-gray-500 mb-4">No watches match these filters.</p>
            <button class="btn-outline text-sm" @click="clearFilters">Clear filters</button>
          </div>

          <div v-else :key="resultsKey" v-reveal-stagger="{ stagger: 60 }" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6">
            <ProductCard v-for="product in products" :key="product.id" :product="product" fluid />
          </div>

          <!-- Pagination -->
          <nav v-if="meta.last_page > 1" class="flex justify-center items-center mt-10 gap-2" aria-label="Pagination">
            <button class="page-btn" :disabled="meta.current_page <= 1" @click="goToPage(meta.current_page - 1)">‹</button>
            <button
              v-for="page in meta.last_page"
              :key="page"
              class="page-btn"
              :class="{ '!bg-luxury-black !text-white': meta.current_page === page }"
              :aria-current="meta.current_page === page ? 'page' : undefined"
              @click="goToPage(page)"
            >
              {{ page }}
            </button>
            <button class="page-btn" :disabled="meta.current_page >= meta.last_page" @click="goToPage(meta.current_page + 1)">›</button>
          </nav>
        </section>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, reactive, ref, watch } from 'vue'
import type { Brand, Category, Product, PaginatedResponse } from '~/types'
import { useApi } from '~/composables/useApi'

const route = useRoute()
const router = useRouter()
const api = useApi()

type FlagKey = 'in_stock' | 'is_new' | 'is_featured' | 'is_best_seller' | 'is_limited_edition'
const flagOptions: { key: FlagKey; label: string }[] = [
  { key: 'in_stock', label: 'In stock only' },
  { key: 'is_new', label: 'New arrivals' },
  { key: 'is_featured', label: 'Featured' },
  { key: 'is_best_seller', label: 'Best sellers' },
  { key: 'is_limited_edition', label: 'Limited edition' },
]
const TEXT_KEYS = ['search', 'brand', 'category', 'gender', 'min_price', 'max_price'] as const

// The URL is the single source of truth, so header search, brand links and
// "View All" links (/shop?is_new=1, /shop?brand=rolex …) all just work.
const query = computed(() => {
  const q = route.query
  const str = (k: string) => (typeof q[k] === 'string' ? (q[k] as string) : '')
  return {
    search: str('search'),
    brand: str('brand'),
    category: str('category'),
    gender: str('gender'),
    min_price: str('min_price'),
    max_price: str('max_price'),
    sort: str('sort') || 'newest',
    page: Number(str('page')) || 1,
    in_stock: str('in_stock') === '1',
    is_new: str('is_new') === '1',
    is_featured: str('is_featured') === '1',
    is_best_seller: str('is_best_seller') === '1',
    is_limited_edition: str('is_limited_edition') === '1',
  }
})

const apiParams = computed(() => {
  const p: Record<string, string> = { per_page: '12', sort: query.value.sort, page: String(query.value.page) }
  for (const k of TEXT_KEYS) if (query.value[k]) p[k] = query.value[k]
  for (const f of flagOptions) if (query.value[f.key]) p[f.key] = '1'
  return p
})

const { data, pending, error, refresh } = await useAsyncData(
  'shop-products',
  () => api.get<PaginatedResponse<Product>>('/v1/products', apiParams.value),
  { watch: [apiParams] },
)
const products = computed(() => data.value?.data ?? [])
const meta = computed(() => data.value?.meta ?? { current_page: 1, last_page: 1, total: 0 })
const resultsKey = computed(() => JSON.stringify(apiParams.value))

const { data: brandData } = await useAsyncData('shop-brands', () => api.get<{ data: Brand[] }>('/v1/brands'))
const { data: categoryData } = await useAsyncData('shop-categories', () => api.get<{ data: Category[] }>('/v1/categories'))
const brands = computed(() => brandData.value?.data ?? [])
const categories = computed(() => categoryData.value?.data ?? [])

const activeBrand = computed(() => brands.value.find(b => b.slug === query.value.brand || String(b.id) === query.value.brand))
const activeCategory = computed(() => categories.value.find(c => c.slug === query.value.category || String(c.id) === query.value.category))

const pageTitle = computed(() => {
  if (activeBrand.value) return activeBrand.value.name
  if (query.value.search) return `Results for “${query.value.search}”`
  if (activeCategory.value) return activeCategory.value.name
  if (query.value.is_new) return 'New Arrivals'
  if (query.value.is_limited_edition) return 'Limited Edition'
  if (query.value.is_best_seller) return 'Best Sellers'
  if (query.value.is_featured) return 'Featured Collection'
  return 'All Watches'
})

useSeoMeta({ title: () => pageTitle.value })

// ---- Filter form (edits a draft; Apply writes it to the URL) ---------------
const filtersOpen = ref(false)
const form = reactive({ ...query.value })
watch(query, (q) => Object.assign(form, q))

const chips = computed(() => {
  const list: { key: string; label: string }[] = []
  if (query.value.search) list.push({ key: 'search', label: `“${query.value.search}”` })
  if (query.value.brand) list.push({ key: 'brand', label: activeBrand.value?.name ?? query.value.brand })
  if (query.value.category) list.push({ key: 'category', label: activeCategory.value?.name ?? query.value.category })
  if (query.value.gender) list.push({ key: 'gender', label: query.value.gender[0].toUpperCase() + query.value.gender.slice(1) })
  if (query.value.min_price) list.push({ key: 'min_price', label: `From Rs. ${Number(query.value.min_price).toLocaleString()}` })
  if (query.value.max_price) list.push({ key: 'max_price', label: `Up to Rs. ${Number(query.value.max_price).toLocaleString()}` })
  for (const f of flagOptions) if (query.value[f.key]) list.push({ key: f.key, label: f.label })
  return list
})
const activeFilterCount = computed(() => chips.value.length)

function setQuery(patch: Record<string, string | number | undefined>) {
  const next: Record<string, string> = {}
  for (const [k, v] of Object.entries({ ...route.query, ...patch })) {
    if (v !== undefined && v !== null && v !== '' && v !== 'newest' && !(k === 'page' && Number(v) === 1)) next[k] = String(v)
  }
  router.push({ query: next })
}

function applyFilters() {
  const patch: Record<string, string | undefined> = { page: undefined }
  for (const k of TEXT_KEYS) patch[k] = String(form[k] ?? '').trim() || undefined
  for (const f of flagOptions) patch[f.key] = form[f.key] ? '1' : undefined
  setQuery(patch)
  filtersOpen.value = false
}

function clearFilters() {
  router.push({ query: {} })
}

function goToPage(page: number) {
  setQuery({ page })
  if (import.meta.client) window.scrollTo({ top: 0, behavior: 'smooth' })
}
</script>

<style scoped>
.filter-label {
  @apply block text-sm font-medium text-gray-700 mb-2;
}
.filter-input {
  @apply w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-gold-500 focus:border-transparent;
}
.page-btn {
  @apply min-w-[2.5rem] h-10 px-3 rounded-lg bg-white text-gray-700 shadow-sm hover:bg-gray-100 transition-colors disabled:opacity-40 disabled:cursor-not-allowed;
}
</style>
