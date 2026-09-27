<template>
  <div class="min-h-screen flex flex-col">
    <AppHeader />
    <main class="flex-1 pt-10 md:pt-12">
      <slot />
    </main>
    <AppFooter />
    <AppMobileNav />
    <BackToTop />
    <WhatsAppFloatButton />
    <ClientOnly>
      <AppToaster />
    </ClientOnly>
  </div>
</template>

<script setup lang="ts">
import { provide } from 'vue'
import { useWindowScroll } from '@vueuse/core'

// useWindowScroll is SSR-safe (the previous useScroll(window) threw on the server).
const { y } = useWindowScroll()

provide('scrollY', y)
</script>
