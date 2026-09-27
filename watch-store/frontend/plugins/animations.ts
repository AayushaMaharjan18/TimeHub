import type { Directive, DirectiveBinding } from 'vue'

/**
 * Scroll-driven animations powered by anime.js.
 *
 *   v-reveal                 fade + slide up when the element scrolls into view
 *   v-reveal="'left'"        presets: up | down | left | right | zoom | fade
 *   v-reveal="{ preset: 'zoom', delay: 200 }"
 *   v-reveal-stagger         reveal each direct child in turn (also children
 *                            added later, e.g. after an API response)
 *   v-count-up               count the number inside the text up from 0
 *                            ("10,000+" → 0…10,000+) when it scrolls into view
 *   v-parallax="0.2"         move at a fraction of scroll speed (images/backgrounds)
 *
 * Elements are hidden via CSS ([data-reveal] in main.css) only once the
 * html.js class is present, so content stays visible without JS, and
 * everything is skipped for users who prefer reduced motion.
 */

type Preset = 'up' | 'down' | 'left' | 'right' | 'zoom' | 'fade'
interface RevealOptions { preset?: Preset; delay?: number; duration?: number; stagger?: number }

const PRESETS: Record<Preset, Record<string, [number, number] | [string, string]>> = {
  up: { opacity: [0, 1], translateY: [40, 0] },
  down: { opacity: [0, 1], translateY: [-40, 0] },
  left: { opacity: [0, 1], translateX: [-60, 0] },
  right: { opacity: [0, 1], translateX: [60, 0] },
  zoom: { opacity: [0, 1], scale: [0.9, 1] },
  fade: { opacity: [0, 1] },
}

function options(binding: DirectiveBinding): RevealOptions {
  const v = binding.value
  if (typeof v === 'string') return { preset: v as Preset }
  return v && typeof v === 'object' ? v : {}
}

const reducedMotion = () =>
  import.meta.client && window.matchMedia('(prefers-reduced-motion: reduce)').matches

function showInstantly(el: HTMLElement) {
  el.style.opacity = '1'
  el.style.transform = 'none'
}

/** Run `cb` once, the first time `el` scrolls into view. */
function onEnter(el: Element, cb: () => void, rootMargin = '0px 0px -10% 0px') {
  const io = new IntersectionObserver((entries) => {
    if (entries.some(e => e.isIntersecting)) {
      io.disconnect()
      cb()
    }
  }, { rootMargin })
  io.observe(el)
  return io
}

export default defineNuxtPlugin(async (nuxtApp) => {
  // anime.js touches `window`, so only load it in the browser.
  const anime = import.meta.client ? await import('animejs') : null

  const reveal: Directive<HTMLElement, any> = {
    getSSRProps: () => ({ 'data-reveal': '' }),
    mounted(el, binding) {
      el.setAttribute('data-reveal', '')
      if (!anime || reducedMotion()) return showInstantly(el)
      const o = options(binding)
      ;(el as any)._revealIO = onEnter(el, () => {
        anime.animate(el, {
          ...PRESETS[o.preset ?? 'up'],
          duration: o.duration ?? 900,
          delay: o.delay ?? 0,
          ease: 'outExpo',
        })
      })
    },
    unmounted(el) {
      ;(el as any)._revealIO?.disconnect()
    },
  }

  const revealStagger: Directive<HTMLElement, any> = {
    getSSRProps: () => ({ 'data-reveal-stagger': '' }),
    mounted(el, binding) {
      el.setAttribute('data-reveal-stagger', '')
      const pending = () => Array.from(el.children).filter(c => !(c as HTMLElement).dataset.revealed) as HTMLElement[]

      if (!anime || reducedMotion()) {
        const showAll = () => pending().forEach((c) => { c.dataset.revealed = '1'; showInstantly(c) })
        showAll()
        const mo = new MutationObserver(showAll)
        mo.observe(el, { childList: true })
        ;(el as any)._staggerMO = mo
        return
      }

      const o = options(binding)
      let visible = false
      const play = () => {
        const targets = pending()
        if (!targets.length) return
        targets.forEach(c => (c.dataset.revealed = '1'))
        anime.animate(targets, {
          ...PRESETS[o.preset ?? 'up'],
          duration: o.duration ?? 800,
          delay: anime.stagger(o.stagger ?? 90, { start: o.delay ?? 0 }),
          ease: 'outExpo',
        })
      }

      ;(el as any)._staggerIO = onEnter(el, () => { visible = true; play() })
      // Children rendered later (API data, filters, pagination) animate in too.
      const mo = new MutationObserver(() => { if (visible) play() })
      mo.observe(el, { childList: true })
      ;(el as any)._staggerMO = mo
    },
    unmounted(el) {
      ;(el as any)._staggerIO?.disconnect()
      ;(el as any)._staggerMO?.disconnect()
    },
  }

  const countUp: Directive<HTMLElement, any> = {
    mounted(el) {
      if (!anime || reducedMotion()) return
      const run = () => {
        const original = el.textContent ?? ''
        const match = original.match(/[\d,.]+/)
        if (!match) return
        const target = parseFloat(match[0].replace(/,/g, ''))
        if (!isFinite(target)) return
        const decimals = (match[0].split('.')[1] ?? '').length
        const counter = { value: 0 }
        anime.animate(counter, {
          value: target,
          duration: 1800,
          ease: 'outExpo',
          onUpdate: () => {
            const n = counter.value.toLocaleString('en-US', { minimumFractionDigits: decimals, maximumFractionDigits: decimals })
            el.textContent = original.replace(match[0], n)
          },
          onComplete: () => { el.textContent = original },
        })
      }
      ;(el as any)._countIO = onEnter(el, run)
    },
    unmounted(el) {
      ;(el as any)._countIO?.disconnect()
    },
  }

  const parallax: Directive<HTMLElement, number | undefined> = {
    mounted(el, binding) {
      if (!import.meta.client || reducedMotion()) return
      const speed = binding.value ?? 0.2
      let frame = 0
      const update = () => {
        frame = 0
        const rect = el.parentElement?.getBoundingClientRect() ?? el.getBoundingClientRect()
        if (rect.bottom < 0 || rect.top > window.innerHeight) return
        const offset = (rect.top + rect.height / 2 - window.innerHeight / 2) * -speed
        el.style.transform = `translate3d(0, ${offset.toFixed(1)}px, 0) scale(1.15)`
      }
      const onScroll = () => { if (!frame) frame = requestAnimationFrame(update) }
      window.addEventListener('scroll', onScroll, { passive: true })
      update()
      ;(el as any)._parallaxOff = () => window.removeEventListener('scroll', onScroll)
    },
    unmounted(el) {
      ;(el as any)._parallaxOff?.()
    },
  }

  nuxtApp.vueApp.directive('reveal', reveal)
  nuxtApp.vueApp.directive('reveal-stagger', revealStagger)
  nuxtApp.vueApp.directive('count-up', countUp)
  nuxtApp.vueApp.directive('parallax', parallax)
})
