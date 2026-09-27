import { defineNuxtConfig } from 'nuxt/config'

export default defineNuxtConfig({
  ssr: true,
  devtools: { enabled: true },
  compatibilityDate: '2026-07-19',

  modules: [
    '@nuxtjs/tailwindcss',
    '@pinia/nuxt',
    '@vueuse/nuxt',
    'nuxt-swiper',
    '@nuxt/image',
  ],

  app: {
    head: {
      title: 'WatchStore Nepal - Premium Luxury Watches',
      meta: [
        { charset: 'utf-8' },
        { name: 'viewport', content: 'width=device-width, initial-scale=1' },
        { name: 'description', content: 'Premium luxury watch store in Nepal. Shop authentic watches from top brands. Fast delivery across Kathmandu, Pokhara, and all Nepal.' },
        { name: 'keywords', content: 'watches, luxury watches, nepal, watch store, premium watches, kathmandu' },
        { property: 'og:title', content: 'WatchStore Nepal - Premium Luxury Watches' },
        { property: 'og:description', content: 'Premium luxury watch store in Nepal. Shop authentic watches from top brands.' },
        { property: 'og:type', content: 'website' },
        { name: 'robots', content: 'index, follow' },
      ],
      link: [
        { rel: 'canonical', href: 'https://watchstore.com.np' },
        { rel: 'icon', type: 'image/x-icon', href: '/favicon.ico' },
        { rel: 'preconnect', href: 'https://fonts.googleapis.com' },
        { rel: 'preconnect', href: 'https://fonts.gstatic.com', crossorigin: '' },
        { rel: 'stylesheet', href: 'https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap' },
      ],
      // Flag JS support before first paint so scroll-reveal elements start
      // hidden (see main.css) instead of flashing visible then animating.
      script: [
        { innerHTML: "document.documentElement.classList.add('js')", tagPosition: 'head' },
      ],
    },
    pageTransition: { name: 'page', mode: 'out-in' },
    layoutTransition: { name: 'layout', mode: 'out-in' },
  },

  css: [
    '~/assets/css/main.css',
  ],



  runtimeConfig: {
    public: {
      // 127.0.0.1 rather than localhost: Node resolves localhost to ::1 first,
      // while `php artisan serve` only listens on IPv4 — SSR fetches would fail.
      apiBaseUrl: process.env.NUXT_PUBLIC_API_BASE_URL || 'http://127.0.0.1:8000/api',
      siteUrl: 'https://watchstore.com.np',
      khaltiPublicKey: process.env.NUXT_PUBLIC_KHALTI_PUBLIC_KEY || 'test_public_key_dc74e0fd57cb46cd93832aee0a507256',
    },
  },


  devServer: {
    host: '127.0.0.1',
    port: 3000,
  },

  vite: {
    server: {
      hmr: {
        protocol: 'ws',
        host: '127.0.0.1',
        port: 24678,
      },
    },
  },

  typescript: {
    strict: false,
    typeCheck: false,
    tsConfig: {
      compilerOptions: {
        skipLibCheck: true
      }
    }
  },

  vueCompilerOptions: {
    isCustomElement: (tag) => tag.includes('-')
  },
})