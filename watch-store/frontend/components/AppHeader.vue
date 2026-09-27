<template>
  <header
    class="fixed top-0 left-0 right-0 z-50 transition-all duration-500 bg-white"
    :class="isScrolled ? 'shadow-premium-lg bg-white/95 backdrop-blur-md' : 'shadow-premium'"
  >
    <div class="container-premium">
      <div class="flex items-center justify-between h-10 md:h-12">
        <!-- Logo -->
        <NuxtLink to="/" class="flex items-center space-x-2">
          <span class="text-base md:text-lg font-display font-bold tracking-wider">
            <BrandWordmark :name="footer.brand_name" />
          </span>
        </NuxtLink>

        <!-- Desktop Navigation -->
        <nav class="hidden lg:flex items-center space-x-8">
          <NuxtLink to="/" class="nav-link" exact-active-class="text-gold-500">Home</NuxtLink>
          <NuxtLink to="/shop" class="nav-link" active-class="text-gold-500">Shop</NuxtLink>
          <NuxtLink to="/brands" class="nav-link" active-class="text-gold-500">Brands</NuxtLink>
          <NuxtLink to="/blog" class="nav-link" active-class="text-gold-500">Blog</NuxtLink>
          <NuxtLink to="/about" class="nav-link" active-class="text-gold-500">About</NuxtLink>
          <NuxtLink to="/contact" class="nav-link" active-class="text-gold-500">Contact</NuxtLink>
        </nav>

        <!-- Actions -->
        <div class="flex items-center space-x-4">
          <!-- Search -->
          <button @click="toggleSearch" class="p-2 hover:text-gold-500 transition-colors" aria-label="Search">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </button>

          <!-- Wishlist -->
          <NuxtLink to="/wishlist" class="p-2 hover:text-gold-500 transition-colors relative" aria-label="Wishlist">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
            </svg>
            <ClientOnly><span v-if="wishlistStore.count > 0" class="absolute -top-1 -right-1 bg-gold-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center font-medium">
              {{ wishlistStore.count }}
            </span></ClientOnly>
          </NuxtLink>

          <!-- Cart -->
          <NuxtLink to="/cart" class="p-2 hover:text-gold-500 transition-colors relative" aria-label="Cart">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
            </svg>
            <ClientOnly><span v-if="cartStore.totalItems > 0" ref="cartBadge" class="absolute -top-1 -right-1 bg-gold-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center font-medium">
              {{ cartStore.totalItems }}
            </span></ClientOnly>
          </NuxtLink>

          <!-- User Menu -->
          <ClientOnly>
          <template v-if="authStore.isAuthenticated">
            <NuxtLink to="/account" class="p-2 hover:text-gold-500 transition-colors" aria-label="Account">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
              </svg>
            </NuxtLink>
          </template>
          <template v-else>
            <NuxtLink to="/auth/login" class="hidden md:inline-flex btn-primary text-sm px-4 py-1.5">Sign In</NuxtLink>
          </template>
          </ClientOnly>

          <!-- Mobile Menu Toggle -->
          <button @click="mobileMenuOpen = !mobileMenuOpen" class="lg:hidden p-2" aria-label="Menu">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path v-if="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
              <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </div>
    </div>

    <!-- Mobile Menu -->
    <Transition name="slide-down">
      <div v-if="mobileMenuOpen" class="lg:hidden glass-effect border-t border-gray-100">
        <div class="container-premium py-4 space-y-3">
          <NuxtLink @click="mobileMenuOpen = false" to="/" class="block py-2 text-lg font-medium">Home</NuxtLink>
          <NuxtLink @click="mobileMenuOpen = false" to="/shop" class="block py-2 text-lg font-medium">Shop</NuxtLink>
          <NuxtLink @click="mobileMenuOpen = false" to="/brands" class="block py-2 text-lg font-medium">Brands</NuxtLink>
          <NuxtLink @click="mobileMenuOpen = false" to="/blog" class="block py-2 text-lg font-medium">Blog</NuxtLink>
          <NuxtLink @click="mobileMenuOpen = false" to="/about" class="block py-2 text-lg font-medium">About</NuxtLink>
          <NuxtLink @click="mobileMenuOpen = false" to="/contact" class="block py-2 text-lg font-medium">Contact</NuxtLink>
          <hr class="border-gray-200">
          <NuxtLink @click="mobileMenuOpen = false" v-if="!authStore.isAuthenticated" to="/auth/login" class="block py-2 text-lg font-medium">Sign In</NuxtLink>
          <NuxtLink @click="mobileMenuOpen = false" v-if="authStore.isAuthenticated" to="/account" class="block py-2 text-lg font-medium">My Account</NuxtLink>
          <button v-if="authStore.isAuthenticated" @click="handleLogout" class="block py-2 text-lg font-medium text-red-500">Logout</button>
        </div>
      </div>
    </Transition>

    <!-- Search Overlay -->
    <Transition name="fade">
      <div v-if="searchOpen" class="absolute top-full left-0 right-0 glass-effect shadow-premium-lg">
        <div class="container-premium py-4">
          <div class="relative">
            <input
              ref="searchInput"
              v-model="searchQuery"
              type="search"
              placeholder="Search watches..."
              class="w-full px-4 py-3 pr-12 border-2 border-gray-200 rounded-xl focus:border-gold-500 focus:outline-none text-lg"
              @keyup.enter="handleSearch"
            />
            <button @click="handleSearch" class="absolute right-3 top-1/2 -translate-y-1/2 p-2 text-gray-400 hover:text-gold-500">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </header>
</template>

<script setup lang="ts">
import { ref, inject, watch, nextTick, onMounted } from 'vue'
import { useSiteSettings } from '~/composables/useSiteSettings'
import { useAuthStore } from '~/stores/auth'
import { useCartStore } from '~/stores/cart'
import { useWishlistStore } from '~/stores/wishlist'
import { useApi } from '~/composables/useApi'

const authStore = useAuthStore()
const cartStore = useCartStore()
const wishlistStore = useWishlistStore()

const { footer, fetchSettings } = useSiteSettings()

const isScrolled = ref(false)
const mobileMenuOpen = ref(false)
const searchOpen = ref(false)
const searchQuery = ref('')
const searchInput = ref<HTMLInputElement | null>(null)
const cartBadge = ref<HTMLElement | null>(null)

const scrollY = inject('scrollY', ref(0))

watch(scrollY, (y: number) => {
  isScrolled.value = y > 50
})

// Close menus on navigation.
const route = useRoute()
watch(() => route.fullPath, () => {
  mobileMenuOpen.value = false
  searchOpen.value = false
})

// Bounce the cart badge whenever an item is added.
watch(() => cartStore.totalItems, async (now, before) => {
  if (now <= before || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return
  await nextTick()
  if (!cartBadge.value) return
  const { animate } = await import('animejs')
  animate(cartBadge.value, { scale: [1, 1.6, 1], rotate: [0, -12, 0], duration: 700, ease: 'outElastic(1, .5)' })
})

onMounted(() => fetchSettings())

function toggleSearch() {
  searchOpen.value = !searchOpen.value
  if (searchOpen.value) {
    nextTick(() => searchInput.value?.focus())
  }
}

function handleSearch() {
  if (searchQuery.value.trim()) {
    navigateTo(`/shop?search=${encodeURIComponent(searchQuery.value.trim())}`)
    searchOpen.value = false
    searchQuery.value = ''
  }
}

async function handleLogout() {
  try {
    const api = useApi()
    await api.post('/v1/auth/logout')
  } catch {}
  authStore.logout()
  wishlistStore.clear()
  mobileMenuOpen.value = false
  navigateTo('/')
}
</script>

<style scoped>
.nav-link {
  @apply text-sm font-medium uppercase tracking-wider text-luxury-black hover:text-gold-500 transition-colors relative py-1;
}
.nav-link::after {
  content: '';
  @apply absolute bottom-0 left-0 w-0 h-0.5 bg-gold-500 transition-all duration-300;
}
.nav-link:hover::after {
  @apply w-full;
}
.nav-link.router-link-exact-active {
  @apply text-gold-500;
}
.nav-link.router-link-exact-active::after {
  @apply w-full;
}
</style>