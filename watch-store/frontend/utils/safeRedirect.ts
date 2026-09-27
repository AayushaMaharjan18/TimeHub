/**
 * Only allow same-site relative paths as post-login redirects, so a crafted
 * link like /auth/login?redirect=https://evil.example can't bounce users off-site.
 */
export function safeRedirect(value: unknown, fallback = '/'): string {
  return typeof value === 'string' && value.startsWith('/') && !value.startsWith('//') ? value : fallback
}
