<template>
  <div class="min-h-screen flex flex-col">
    <AppHeader />
    <main class="flex-1">
      <slot />
    </main>
    <AppFooter />
    <AppMobileNav />
    <BackToTop />
    <WhatsAppFloatButton />
  </div>
</template>

<script setup lang="ts">
import { provide } from 'vue'
import { useScroll } from '@vueuse/core'

const { y } = useScroll(window)

provide('scrollY', y)
</script>