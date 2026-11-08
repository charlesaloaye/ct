import '../css/app.css';
import { createRoot } from 'react-dom/client';
import { createInertiaApp, router } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import AdminLayout from './layouts/AdminLayout.jsx';
import { track } from './lib/track.js';

// Admin screens share one persistent layout (sidebar, toasts); sign-in screens have their own.
const usesAdminLayout = (name) => name.startsWith('admin/') && !name.startsWith('admin/auth/');

createInertiaApp({
  resolve: (name) => resolvePageComponent(`./pages/${name}.jsx`, import.meta.glob('./pages/**/*.jsx')),
  layout: (name) => (usesAdminLayout(name) ? AdminLayout : undefined),
  setup({ el, App, props }) {
    createRoot(el).render(<App {...props} />);
  },
  progress: { color: '#3DDC97', delay: 150 },
});

// Page views for the public site (no cookies, no IPs; see lib/track.js).
router.on('navigate', (event) => {
  const { pathname } = new URL(event.detail.page.url, window.location.origin);
  if (!pathname.startsWith('/admin')) {
    track('page_view', { path: pathname });
    // Google Analytics, when an ID is set in Admin → Pages & SEO.
    if (typeof window.gtag === 'function') window.gtag('event', 'page_view', { page_location: window.location.href, page_title: document.title });
  }

  // Arriving at a section from another page: settle on it once the new page has laid out.
  const { hash } = window.location;
  if (hash.length > 1) setTimeout(() => document.getElementById(hash.slice(1))?.scrollIntoView(), 120);
});
