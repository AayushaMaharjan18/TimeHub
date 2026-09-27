import { useAuthStore } from '~/stores/auth'
import { useCartStore } from '~/stores/cart'
import { useWishlistStore } from '~/stores/wishlist'

/**
 * Restore persisted client state (auth, cart, wishlist) before any page mounts.
 *
 * This used to run in app.vue's onMounted, but child components mount before
 * their parent — so pages like /checkout and /account saw a logged-out store on
 * a hard refresh and bounced the user to the login page.
 */
export default defineNuxtPlugin(() => {
  const authStore = useAuthStore()
  const cartStore = useCartStore()
  const wishlistStore = useWishlistStore()

  authStore.loadFromStorage()
  cartStore.hydrate()

  if (authStore.isAuthenticated) {
    wishlistStore.hydrateFromStorage()
    wishlistStore.load()
  }
})
