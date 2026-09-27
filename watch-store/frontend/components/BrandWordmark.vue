<template>
  <!-- "WATCHSTORE" → WATCH + gold STORE; any other brand name renders as-is. -->
  <span>
    <span :class="dark ? 'text-white' : 'text-luxury-black'">{{ parts[0] }}</span><span class="text-gold-500">{{ parts[1] }}</span>
  </span>
</template>

<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{ name?: string | null; dark?: boolean }>()

const parts = computed(() => {
  const name = (props.name || 'WATCHSTORE').trim()
  const i = name.toUpperCase().lastIndexOf('STORE')
  return i > 0 ? [name.slice(0, i), name.slice(i)] : [name, '']
})
</script>
