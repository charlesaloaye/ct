// Lightweight analytics: page views and outbound clicks. Only the path (and
// the link target for clicks) is sent - no cookies, IPs or user agents stored.
export function track(type, extra = {}) {
  try {
    const body = JSON.stringify({ type, path: window.location.pathname, ...extra });
    fetch('/events', { method: 'POST', keepalive: true, headers: { 'Content-Type': 'application/json', Accept: 'application/json' }, body }).catch(() => {});
  } catch {
    // Never let analytics break the page.
  }
}

export const trackOutbound = (href, label) => () => track('outbound_click', { href, label });
