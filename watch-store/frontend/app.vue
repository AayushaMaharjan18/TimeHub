<template>
  <NuxtLayout>
    <NuxtPage />
  </NuxtLayout>
</template>

<script setup lang="ts">
// Client state (auth, cart, wishlist) is restored in plugins/00.init.client.ts
// so it is ready before any page's onMounted runs.
useHead({
  titleTemplate: '%s | WatchStore Nepal',
  htmlAttrs: {
    lang: 'en',
  },
})
</script>
