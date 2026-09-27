<template>
  <div class="fixed top-16 right-4 z-[100] flex flex-col gap-3 w-[calc(100%-2rem)] max-w-sm pointer-events-none" aria-live="polite">
    <TransitionGroup :css="false" @enter="onEnter" @leave="onLeave">
      <div
        v-for="toast in toasts"
        :key="toast.id"
        class="pointer-events-auto flex items-start gap-3 bg-white rounded-xl shadow-premium-xl border-l-4 px-4 py-3"
        :class="{
          'border-green-500': toast.type === 'success',
          'border-red-500': toast.type === 'error',
          'border-gold-500': toast.type === 'info',
        }"
        role="status"
      >
        <span
          class="mt-0.5 flex-shrink-0 w-5 h-5 rounded-full flex items-center justify-center text-white text-xs"
          :class="{ 'bg-green-500': toast.type === 'success', 'bg-red-500': toast.type === 'error', 'bg-gold-500': toast.type === 'info' }"
        >
          {{ toast.type === 'success' ? '✓' : toast.type === 'error' ? '!' : 'i' }}
        </span>
        <div class="flex-1 text-sm">
          <p class="text-luxury-black">{{ toast.message }}</p>
          <NuxtLink v-if="toast.action" :to="toast.action.to" class="text-gold-600 font-medium hover:underline" @click="dismiss(toast.id)">
            {{ toast.action.label }} →
          </NuxtLink>
        </div>
        <button class="text-gray-400 hover:text-gray-600" aria-label="Dismiss" @click="dismiss(toast.id)">✕</button>
      </div>
    </TransitionGroup>
  </div>
</template>

<script setup lang="ts">
import { animate } from 'animejs'
import { useToast } from '~/composables/useToast'

const { toasts, dismiss } = useToast()

function onEnter(el: Element, done: () => void) {
  animate(el, {
    opacity: [0, 1],
    translateX: [80, 0],
    scale: [0.9, 1],
    duration: 600,
    ease: 'outElastic(1, .7)',
    onComplete: done,
  })
}

function onLeave(el: Element, done: () => void) {
  animate(el, {
    opacity: [1, 0],
    translateX: [0, 80],
    duration: 300,
    ease: 'inQuad',
    onComplete: done,
  })
}
</script>
