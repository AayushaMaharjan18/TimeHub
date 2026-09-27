<template>
  <div class="min-h-[70vh] bg-gray-50 flex items-center justify-center px-4 py-16 relative overflow-hidden">
    <div ref="confetti" class="absolute inset-0 pointer-events-none" aria-hidden="true" />

    <div class="bg-white rounded-3xl shadow-premium-xl p-10 max-w-lg w-full text-center relative">
      <svg class="w-24 h-24 mx-auto mb-6" viewBox="0 0 52 52" aria-hidden="true">
        <circle ref="circle" cx="26" cy="26" r="24" fill="none" stroke="#C9A227" stroke-width="2" />
        <path ref="check" fill="none" stroke="#C9A227" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" d="M15 27l7 7 15-15" />
      </svg>

      <h1 class="success-line text-3xl font-display font-bold mb-3">Thank you for your order!</h1>
      <p class="success-line text-gray-600 mb-6">
        We've received your order and will contact you shortly to confirm delivery.
        You'll pay in cash when it arrives.
      </p>

      <div v-if="orderNumber" class="success-line bg-gold-50 rounded-xl py-4 px-6 mb-8">
        <p class="text-xs text-gray-500 uppercase tracking-widest">Order number</p>
        <p class="text-2xl font-semibold tracking-wider text-gold-700">{{ orderNumber }}</p>
      </div>

      <div class="success-line flex flex-col sm:flex-row gap-3 justify-center">
        <NuxtLink :to="trackLink" class="btn-gold">Track this order</NuxtLink>
        <NuxtLink to="/account?tab=orders" class="btn-outline">My orders</NuxtLink>
      </div>
      <NuxtLink to="/shop" class="success-line inline-block mt-6 text-sm text-gray-500 link-underline">Continue shopping</NuxtLink>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'

useSeoMeta({ title: 'Order Confirmed', robots: 'noindex' })

const route = useRoute()
const orderNumber = computed(() => (typeof route.query.order === 'string' ? route.query.order : ''))
const phone = computed(() => (typeof route.query.phone === 'string' ? route.query.phone : ''))
const trackLink = computed(() => ({ path: '/track-order', query: { order: orderNumber.value, phone: phone.value } }))

const circle = ref<SVGCircleElement | null>(null)
const check = ref<SVGPathElement | null>(null)
const confetti = ref<HTMLElement | null>(null)

onMounted(async () => {
  if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return
  const { animate, createTimeline, svg, stagger, utils } = await import('animejs')

  createTimeline({ defaults: { ease: 'outExpo' } })
    .add(svg.createDrawable(circle.value!), { draw: ['0 0', '0 1'], duration: 900, ease: 'inOutQuad' })
    .add(svg.createDrawable(check.value!), { draw: ['0 0', '0 1'], duration: 600, ease: 'outQuad' }, '-=200')
    .add('.success-line', { opacity: [0, 1], translateY: [20, 0], duration: 800, delay: stagger(100) }, '-=300')

  // Gold confetti burst
  const colors = ['#C9A227', '#FFDB4D', '#000000', '#A17E1F']
  const pieces = Array.from({ length: 40 }, () => {
    const el = document.createElement('span')
    el.className = 'absolute block w-2 h-3 rounded-sm'
    el.style.left = '50%'
    el.style.top = '35%'
    el.style.background = utils.randomPick(colors)
    confetti.value!.appendChild(el)
    return el
  })
  animate(pieces, {
    translateX: () => utils.random(-400, 400),
    translateY: () => [0, utils.random(-250, 350)],
    rotate: () => utils.random(-540, 540),
    opacity: [1, 0],
    duration: () => utils.random(1400, 2400),
    delay: stagger(10),
    ease: 'outCubic',
    onComplete: () => pieces.forEach(p => p.remove()),
  })
})
</script>

<style scoped>
@media (prefers-reduced-motion: no-preference) {
  .js .success-line {
    opacity: 0;
  }
}
</style>
