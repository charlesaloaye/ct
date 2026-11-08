import { useEffect, useState } from 'react';
import { Link, usePage } from '@inertiajs/react';
import { Menu, Close, ChevronRight, Sun, Moon } from './Icons.jsx';
import Mark from './Mark.jsx';
import SocialLinks from './SocialLinks.jsx';
import { linkProps, useSite } from './Site.jsx';
import useTheme, { setTheme } from '../lib/theme.js';

// The same menu on every page. "Work" and "Blog" light up on their own pages.
const navLinks = [
  { label: 'About', href: '/about', section: '/about' },
  { label: 'Work', href: '/work', section: '/work' },
  { label: 'Ventures', href: '/ventures', section: '/ventures' },
  { label: 'Products', href: '/products', section: '/products' },
  { label: 'Blog', href: '/blog', section: '/blog' },
];

const drawerLinks = [
  { label: 'About', href: '/about' },
  { label: 'Work', href: '/work' },
  { label: 'Ventures', href: '/ventures' },
  { label: 'Products', href: '/products' },
  { label: 'Expertise', href: '/expertise' },
  { label: 'Blog', href: '/blog' },
  { label: 'Contact', href: '/contact' },
];

export default function SiteNav() {
  const path = usePage().url.split(/[?#]/)[0];
  const isActive = (l) => l.section && (path === l.section || path.startsWith(`${l.section}/`));
  const [open, setOpen] = useState(false);
  const { identity, availabilityShort, contact, socials, appearance, primaryCta } = useSite();
  const theme = useTheme(appearance?.theme);
  const nextTheme = theme === 'dark' ? 'light' : 'dark';

  // Light by default; dark when the visitor (or the site default) chooses it.
  useEffect(() => {
    if (theme === 'dark') document.documentElement.dataset.theme = 'dark';
    else delete document.documentElement.dataset.theme;
  }, [theme]);

  // A section on the page we are already on: close the menu (which unlocks scrolling) and then scroll.
  const goTo = (e, href) => {
    const [path, hash] = href.split('#');
    setOpen(false);
    if (hash && (path || '/') === window.location.pathname) {
      e.preventDefault();
      window.history.replaceState(window.history.state, '', href);
      setTimeout(() => document.getElementById(hash)?.scrollIntoView({ behavior: 'smooth' }), 30);
    }
  };

  useEffect(() => {
    if (!open) return undefined;
    const onKey = (e) => e.key === 'Escape' && setOpen(false);
    document.addEventListener('keydown', onKey);
    const prev = document.body.style.overflow;
    document.body.style.overflow = 'hidden';
    return () => {
      document.removeEventListener('keydown', onKey);
      document.body.style.overflow = prev;
    };
  }, [open]);

  return (
    <header className="site-header">
      <nav aria-label="Main" className="container nav">
        <Link href="/" className="brand">
          <Mark identity={identity} className="brand-mark" />
          <span className="brand-text">
            <span className="brand-name">{identity.shortName}</span>
            {identity.role && <span className="brand-sub">{identity.role}</span>}
          </span>
        </Link>

        <div className="nav-links">
          {navLinks.map((l) => (
            <Link key={l.label} href={l.href} className={`nav-link${isActive(l) ? ' active' : ''}`} aria-current={isActive(l) ? 'page' : undefined}>
              {l.label}
            </Link>
          ))}
          <Link href="/contact" className="nav-cta">Start a project</Link>
        </div>

        <div className="nav-tools">
          <button type="button" className="icon-btn" aria-label={`Switch to ${nextTheme} mode`} title={`Switch to ${nextTheme} mode`} onClick={() => setTheme(nextTheme)}>
            {theme === 'dark' ? <Sun /> : <Moon />}
          </button>
          <button type="button" className="icon-btn nav-burger" aria-label="Open menu" aria-expanded={open} onClick={() => setOpen(true)}>
            <Menu />
          </button>
        </div>
      </nav>

      {open && (
        <>
          <button type="button" className="drawer-overlay" aria-label="Close menu" onClick={() => setOpen(false)} />
          <div className="drawer" role="dialog" aria-modal="true" aria-label="Menu">
            <div className="drawer-head">
              <span className="brand">
                <Mark identity={identity} className="brand-mark" style={{ width: 36, height: 36, borderRadius: 10, fontSize: 15 }} />
                <span style={{ fontWeight: 700, fontSize: 16 }}>Menu</span>
              </span>
              <button type="button" className="icon-btn" aria-label="Close menu" onClick={() => setOpen(false)}>
                <Close />
              </button>
            </div>

            <div className="drawer-links">
              {drawerLinks.map((l, i) => (
                <Link key={l.label} href={l.href} className="drawer-link" onClick={(e) => goTo(e, l.href)}>
                  <span style={{ display: 'flex', alignItems: 'baseline', gap: 14 }}>
                    <span className="drawer-link-n">{String(i + 1).padStart(2, '0')}</span>
                    <span className="drawer-link-l">{l.label}</span>
                  </span>
                  <span style={{ color: 'var(--muted)', display: 'flex' }}><ChevronRight /></span>
                </Link>
              ))}
            </div>

            <div className="drawer-foot">
              {availabilityShort && <div className="drawer-avail"><span className="badge-dot" style={{ width: 9, height: 9, boxShadow: 'none' }} />{availabilityShort}</div>}
              {primaryCta && <a href={primaryCta.href} {...linkProps(primaryCta.href)} className="btn btn-ink" style={{ padding: 17 }}>{primaryCta.label}</a>}
              {contact.email && <a href={`mailto:${contact.email}`} className="btn btn-outline" style={{ padding: 15, fontSize: 15 }}>{contact.email}</a>}
              {socials.length > 0 && (
                <div className="drawer-socials">
                  <SocialLinks socials={socials} />
                </div>
              )}
            </div>
          </div>
        </>
      )}
    </header>
  );
}
