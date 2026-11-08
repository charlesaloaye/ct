// Admin content types. The key is the URL segment under /admin (and the route default on the server).
export const collections = {
  projects: { title: 'Projects', single: 'project', addLabel: 'Add project', metaLabel: 'Domain' },
  products: { title: 'Products', single: 'product', addLabel: 'Add product', metaLabel: 'Link' },
  articles: { title: 'Articles', single: 'article', addLabel: 'New article', metaLabel: 'Category', ai: true },
  services: { title: 'Services', single: 'service', addLabel: 'Add service', metaLabel: 'Number' },
  experiences: { title: 'Experience', single: 'role', addLabel: 'Add role', metaLabel: 'Years' },
  testimonials: { title: 'Testimonials', single: 'testimonial', addLabel: 'Add testimonial', metaLabel: 'Client' },
  ventures: { title: 'Ventures', single: 'venture', addLabel: 'Add venture', metaLabel: 'Role' },
  appearances: { title: 'Media & press', single: 'coverage', addLabel: 'Add coverage', metaLabel: 'Outlet' },
  writings: { title: 'Writing elsewhere', single: 'article link', addLabel: 'Add article link', metaLabel: 'Published on' },
};

// Sidebar. `count` reads a badge from the shared adminCounts prop.
export const nav = [
  { label: 'General', items: [
    { href: '/admin', label: 'Overview', exact: true },
    { href: '/admin/messages', label: 'Messages', count: (c) => c.unreadMessages || null },
    { href: '/admin/subscribers', label: 'Subscribers', count: (c) => c.confirmedSubscribers },
  ] },
  { label: 'Content', items: Object.entries(collections).map(([key, col]) => (
    { href: `/admin/${key}`, label: col.title, count: (c) => c.content[key]?.total }
  )) },
  { label: 'Site', items: [
    { href: '/admin/settings', label: 'Site settings' },
    { href: '/admin/pages', label: 'Pages & SEO' },
    { href: '/admin/homepage', label: 'Homepage lists' },
    { href: '/admin/integrations', label: 'Integrations' },
    { href: '/admin/account', label: 'Account & security' },
  ] },
];
