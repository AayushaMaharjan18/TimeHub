<template>
  <div>
    <!-- Hero Banner (Admin → Homepage → Hero Sliders) -->
    <section class="relative h-[calc(100vh-3rem)] min-h-[560px] max-h-[860px] bg-luxury-black overflow-hidden">
      <Swiper
        v-if="heroSlides.length"
        :modules="[SwiperAutoplay, SwiperEffectFade, SwiperPagination, SwiperNavigation]"
        :autoplay="{ delay: 6000, disableOnInteraction: false }"
        effect="fade"
        :fade-effect="{ crossFade: true }"
        :pagination="{ clickable: true }"
        :navigation="heroSlides.length > 1"
        :loop="heroSlides.length > 1"
        class="h-full hero-swiper"
        @swiper="onHeroInit"
        @slide-change-transition-start="onHeroChange"
      >
        <SwiperSlide v-for="(slide, index) in heroSlides" :key="slide.id">
          <div class="relative h-full overflow-hidden">
            <img
              :src="slide.image"
              :alt="slide.title"
              class="hero-image absolute inset-0 w-full h-full object-cover"
              :loading="index === 0 ? 'eager' : 'lazy'"
            />
            <div class="absolute inset-0 bg-gradient-to-r from-black/75 via-black/40 to-transparent" />
            <div class="absolute inset-0 flex items-center">
              <div class="container-premium w-full">
                <div class="hero-content max-w-2xl">
                  <p v-if="slide.subtitle" class="hero-subtitle text-gold-500 text-sm md:text-base font-medium mb-4 tracking-[0.3em] uppercase">
                    {{ slide.subtitle }}
                  </p>
                  <h1 class="hero-title text-4xl md:text-6xl lg:text-7xl font-display font-bold text-white mb-6 leading-tight">
                    {{ slide.title }}
                  </h1>
                  <p v-if="slide.description" class="hero-desc text-gray-200 text-lg md:text-xl mb-8">{{ slide.description }}</p>
                  <NuxtLink v-if="slide.button_url" :to="slide.button_url" class="hero-cta btn-gold text-lg px-10 py-4">
                    {{ slide.button_text || 'Shop Now' }}
                  </NuxtLink>
                </div>
              </div>
            </div>
          </div>
        </SwiperSlide>
      </Swiper>
      <div v-else class="h-full skeleton-shimmer rounded-none" />

      <!-- Scroll cue -->
      <div class="absolute bottom-8 left-1/2 -translate-x-1/2 z-10 hidden md:flex flex-col items-center text-white/60 text-xs tracking-widest uppercase pointer-events-none">
        <span>Scroll</span>
        <span class="scroll-cue mt-2 block w-px h-10 bg-gradient-to-b from-gold-500 to-transparent" />
      </div>
    </section>

    <!-- Value props -->
    <section class="border-b border-gray-100 bg-white">
      <div v-reveal-stagger class="container-premium grid grid-cols-2 lg:grid-cols-4 gap-6 py-8">
        <div v-for="usp in usps" :key="usp.title" class="flex items-center gap-3">
          <span class="w-11 h-11 flex-shrink-0 rounded-full bg-gold-50 text-gold-600 flex items-center justify-center text-lg">{{ usp.icon }}</span>
          <div>
            <p class="font-semibold text-sm">{{ usp.title }}</p>
            <p class="text-xs text-gray-500">{{ usp.text }}</p>
          </div>
        </div>
      </div>
    </section>

    <HomeProductSection
      v-if="pending || home.featured_products.length"
      eyebrow="Curated Collection"
      title="Featured Collection"
      subtitle="Discover our handpicked selection of premium timepieces"
      :products="home.featured_products"
      :loading="pending"
      link="/shop?is_featured=1"
    />

    <HomeProductSection
      v-if="pending || home.new_arrivals.length"
      eyebrow="Latest"
      title="New Arrivals"
      :products="home.new_arrivals"
      :loading="pending"
      link="/shop?is_new=1"
      alt
    />

    <HomeProductSection
      v-if="pending || home.best_sellers.length"
      eyebrow="Popular"
      title="Best Sellers"
      subtitle="Most loved watches by our customers"
      :products="home.best_sellers"
      :loading="pending"
      link="/shop?is_best_seller=1"
    />

    <!-- Promo banner (Admin → Homepage → Promo Banners) -->
    <section v-for="offer in home.offers" :key="offer.id" class="relative py-24 md:py-32 overflow-hidden bg-luxury-black">
      <img
        v-if="offer.image"
        v-parallax="0.25"
        :src="offer.image"
        :alt="offer.title"
        class="absolute inset-0 w-full h-full object-cover opacity-40"
      />
      <div v-else class="absolute inset-0 bg-gradient-to-r from-luxury-black via-gray-900 to-luxury-black" />
      <div class="absolute inset-0 opacity-20 pointer-events-none">
        <div class="orb absolute top-10 left-10 w-72 h-72 bg-gold-500 rounded-full blur-3xl" />
        <div class="orb orb-2 absolute bottom-10 right-10 w-96 h-96 bg-gold-500 rounded-full blur-3xl" />
      </div>
      <div class="container-premium relative z-10 text-center">
        <span v-if="offer.label" v-reveal="'fade'" class="text-gold-500 text-sm font-medium tracking-[0.3em] uppercase">{{ offer.label }}</span>
        <h2 v-reveal="{ preset: 'zoom', delay: 100 }" class="text-4xl md:text-6xl font-display font-bold text-white mt-4 mb-4">{{ offer.title }}</h2>
        <p v-if="offer.description" v-reveal="{ delay: 200 }" class="text-gray-300 text-lg max-w-2xl mx-auto mb-8">{{ offer.description }}</p>
        <div v-if="offer.button_url" v-reveal="{ delay: 300 }">
          <NuxtLink :to="offer.button_url" class="btn-gold text-lg px-10 py-4">{{ offer.button_text || 'Shop Now' }}</NuxtLink>
        </div>
      </div>
    </section>

    <HomeProductSection
      v-if="pending || home.limited_edition.length"
      eyebrow="Exclusive"
      title="Limited Edition"
      :products="home.limited_edition"
      :loading="pending"
      link="/shop?is_limited_edition=1"
      alt
    />

    <!-- Top Brands -->
    <section v-if="home.brands.length" class="section-padding">
      <div class="container-premium">
        <div v-reveal class="text-center mb-12">
          <span class="text-gold-500 text-sm font-medium tracking-widest uppercase">Brands</span>
          <h2 class="text-3xl md:text-4xl font-display font-bold mt-2">Top Brands</h2>
        </div>
        <div v-reveal="{ delay: 150 }">
          <BrandCarousel :brands="home.brands" />
        </div>
      </div>
    </section>

    <!-- Customer Reviews (Admin → Reviews → "show on homepage") -->
    <section class="section-padding bg-luxury-gray">
      <div class="container-premium">
        <div v-reveal class="text-center mb-12">
          <span class="text-gold-500 text-sm font-medium tracking-widest uppercase">Testimonials</span>
          <h2 class="text-3xl md:text-4xl font-display font-bold mt-2">What Our Customers Say</h2>
        </div>
        <div v-reveal="{ delay: 150 }">
          <ReviewCarousel />
        </div>
      </div>
    </section>

    <!-- Latest Blog (Admin → Content → Blog Posts) -->
    <section v-if="home.latest_blogs.length" class="section-padding">
      <div class="container-premium">
        <div v-reveal class="flex items-center justify-between mb-12">
          <div>
            <span class="text-gold-500 text-sm font-medium tracking-widest uppercase">Journal</span>
            <h2 class="text-3xl md:text-4xl font-display font-bold mt-2">Latest from Our Blog</h2>
          </div>
          <NuxtLink to="/blog" class="btn-outline text-sm">View All</NuxtLink>
        </div>
        <div v-reveal-stagger class="grid grid-cols-1 md:grid-cols-3 gap-8">
          <BlogCard v-for="blog in home.latest_blogs" :key="blog.id" :blog="blog" />
        </div>
      </div>
    </section>

    <!-- Newsletter (Admin → Settings → Site Settings → Homepage) -->
    <section class="section-padding bg-luxury-black relative overflow-hidden">
      <div class="absolute -top-24 -right-24 w-96 h-96 rounded-full border border-gold-500/20" />
      <div class="absolute -bottom-32 -left-16 w-80 h-80 rounded-full border border-gold-500/10" />
      <div v-reveal class="container-premium text-center relative">
        <h2 class="text-3xl md:text-4xl font-display font-bold text-white mb-4">{{ footer.newsletter_title || 'Join Our Newsletter' }}</h2>
        <p v-if="footer.newsletter_text" class="text-gray-400 max-w-xl mx-auto mb-8">{{ footer.newsletter_text }}</p>
        <form class="max-w-md mx-auto flex flex-col sm:flex-row gap-4" @submit.prevent="subscribeNewsletter">
          <input
            v-model="email"
            type="email"
            placeholder="Your email address"
            class="flex-1 px-4 py-3 rounded-lg border-2 border-gray-700 bg-transparent text-white placeholder-gray-500 focus:border-gold-500 focus:outline-none"
            required
          />
          <button type="submit" class="btn-gold whitespace-nowrap" :disabled="subscribing">
            {{ subscribing ? 'Subscribing…' : 'Subscribe' }}
          </button>
        </form>
      </div>
    </section>

    <!-- Instagram -->
    <section v-if="footer.instagram_url && instagramImages.length" class="py-16">
      <div class="container-premium">
        <div v-reveal class="text-center mb-12">
          <span class="text-gold-500 text-sm font-medium tracking-widest uppercase">Follow Us</span>
          <h2 class="text-3xl md:text-4xl font-display font-bold mt-2">
            <a :href="footer.instagram_url" target="_blank" rel="noopener" class="link-underline hover:text-gold-500 transition-colors">@{{ instagramHandle }}</a>
          </h2>
        </div>
        <div v-reveal-stagger="{ preset: 'zoom', stagger: 60 }" class="grid grid-cols-2 md:grid-cols-4 gap-4">
          <a
            v-for="(img, i) in instagramImages"
            :key="i"
            :href="footer.instagram_url"
            target="_blank"
            rel="noopener"
            class="aspect-square overflow-hidden rounded-xl group relative"
          >
            <img :src="img" alt="" loading="lazy" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700" />
            <span class="absolute inset-0 bg-black/0 group-hover:bg-black/40 transition-colors duration-500 flex items-center justify-center text-white opacity-0 group-hover:opacity-100 text-sm tracking-widest uppercase">View</span>
          </a>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onBeforeUnmount, onMounted } from 'vue'
import { Swiper, SwiperSlide } from 'swiper/vue'
import { Autoplay, EffectFade, Pagination, Navigation } from 'swiper/modules'
import type { Swiper as SwiperInstance } from 'swiper'
import 'swiper/css'
import 'swiper/css/effect-fade'
import 'swiper/css/pagination'
import 'swiper/css/navigation'
import type { HomepageData } from '~/types'
import { useApi } from '~/composables/useApi'
import { useToast } from '~/composables/useToast'
import { useSiteSettings } from '~/composables/useSiteSettings'

const SwiperAutoplay = Autoplay
const SwiperEffectFade = EffectFade
const SwiperPagination = Pagination
const SwiperNavigation = Navigation

useSeoMeta({
  title: 'Premium Luxury Watches',
  description: 'Shop authentic luxury watches in Nepal. Fast delivery across Kathmandu, Pokhara and all of Nepal.',
})

const api = useApi()
const toast = useToast()
const { footer, fetchSettings } = useSiteSettings()

const emptyHome: HomepageData = {
  hero_sliders: [], featured_products: [], new_arrivals: [], best_sellers: [],
  limited_edition: [], brands: [], offers: [], latest_blogs: [],
}

const { data, pending } = await useAsyncData('homepage', () => api.get<HomepageData>('/v1/homepage'), {
  default: () => emptyHome,
})
const home = computed(() => data.value ?? emptyHome)
const heroSlides = computed(() => home.value.hero_sliders)

const usps = [
  { icon: '✓', title: '100% Authentic', text: 'Certified & warrantied' },
  { icon: '⛟', title: 'Nationwide Delivery', text: 'Across all of Nepal' },
  { icon: '₨', title: 'Cash on Delivery', text: 'Or eSewa / Khalti' },
  { icon: '↺', title: 'Easy Returns', text: 'Hassle-free exchanges' },
]

const instagramHandle = computed(() => {
  const url = footer.value.instagram_url || ''
  return url.replace(/\/+$/, '').split('/').pop() || 'instagram'
})

const instagramImages = computed(() => {
  const seen = new Set<string>()
  return [...home.value.featured_products, ...home.value.new_arrivals, ...home.value.best_sellers]
    .map(p => p.thumbnail)
    .filter(src => src && !seen.has(src) && seen.add(src))
    .slice(0, 8)
})

// ---- Newsletter -----------------------------------------------------------
const email = ref('')
const subscribing = ref(false)

async function subscribeNewsletter() {
  subscribing.value = true
  try {
    const res = await api.post<{ message: string }>('/v1/newsletter', { email: email.value })
    toast.success(res.message || 'Thanks for subscribing!')
    email.value = ''
  } catch (e: any) {
    toast.error(e.message || 'Could not subscribe. Please try again.')
  } finally {
    subscribing.value = false
  }
}

// ---- Hero text animation (anime.js) --------------------------------------
let anime: typeof import('animejs') | null = null
let splits: { revert: () => void }[] = []

async function animateSlide(swiper: SwiperInstance) {
  if (!import.meta.client || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return
  anime ??= await import('animejs')
  const slide = swiper.slides[swiper.activeIndex] as HTMLElement | undefined
  if (!slide) return

  const title = slide.querySelector('.hero-title') as HTMLElement | null
  if (title && !title.dataset.split) {
    splits.push(anime.splitText(title, { words: { class: 'hero-word' } }))
    title.dataset.split = '1'
  }

  const tl = anime.createTimeline({ defaults: { ease: 'outExpo' } })
  tl.add(slide.querySelectorAll('.hero-subtitle'), { opacity: [0, 1], translateX: [-40, 0], letterSpacing: ['0.6em', '0.3em'], duration: 900 })
    .add(slide.querySelectorAll('.hero-word'), { opacity: [0, 1], translateY: ['100%', '0%'], rotate: [6, 0], duration: 1000, delay: anime.stagger(70) }, '-=600')
    .add(slide.querySelectorAll('.hero-desc'), { opacity: [0, 1], translateY: [20, 0], duration: 800 }, '-=700')
    .add(slide.querySelectorAll('.hero-cta'), { opacity: [0, 1], scale: [0.85, 1], duration: 900, ease: 'outElastic(1, .6)' }, '-=600')

  // Slow Ken Burns zoom on the background image.
  anime.animate(slide.querySelectorAll('.hero-image'), { scale: [1.15, 1], duration: 7000, ease: 'outSine' })
}

const onHeroInit = (swiper: SwiperInstance) => animateSlide(swiper)
const onHeroChange = (swiper: SwiperInstance) => animateSlide(swiper)

onMounted(() => {
  fetchSettings()
  if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return
  import('animejs').then(({ animate }) => {
    animate('.scroll-cue', { scaleY: [0, 1], opacity: [1, 0], duration: 1800, loop: true, ease: 'inOutSine' })
    animate('.orb', { translateX: [0, 40], translateY: [0, -30], duration: 6000, alternate: true, loop: true, ease: 'inOutSine' })
  })
})

onBeforeUnmount(() => {
  splits.forEach(s => s.revert())
  splits = []
})
</script>

<style scoped>
.hero-swiper :deep(.swiper-pagination-bullet) {
  @apply bg-white/60 w-2.5 h-2.5 transition-all;
}
.hero-swiper :deep(.swiper-pagination-bullet-active) {
  @apply bg-gold-500 w-8 rounded-full;
}
.scroll-cue {
  transform-origin: top;
}
/* Hide hero copy until anime.js reveals it (JS only, and not for reduced motion). */
@media (prefers-reduced-motion: no-preference) {
  .js .hero-subtitle,
  .js .hero-desc,
  .js .hero-cta {
    opacity: 0;
  }
}
</style>
