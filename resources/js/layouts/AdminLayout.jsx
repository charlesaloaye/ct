import { useEffect, useState } from 'react';
import { Link, router, usePage } from '@inertiajs/react';
import { Menu, Close } from '../components/Icons.jsx';
import { nav } from '../admin/config.js';
import Mark from '../components/Mark.jsx';

function NavGroups({ counts, onNavigate }) {
  const { url } = usePage();
  const path = url.split('?')[0];
  const isActive = (item) => (item.exact ? path === item.href : path === item.href || path.startsWith(`${item.href}/`));
  return nav.map((g) => (
    <div key={g.label} className="side-group">
      <span className="side-label">{g.label}</span>
      {g.items.map((i) => {
        const count = counts && i.count ? i.count(counts) : null;
        return (
          <Link key={i.href} href={i.href} onClick={onNavigate} className={`side-item${isActive(i) ? ' active' : ''}`} aria-current={isActive(i) ? 'page' : undefined}>
            <span>{i.label}</span>
            {count != null && <span className="side-count">{count}</span>}
          </Link>
        );
      })}
    </div>
  ));
}

/** Flash toasts from the server plus admin:toast events from the page. */
function Toasts() {
  const { flash } = usePage();
  const [msg, setMsg] = useState('');
  useEffect(() => { if (flash?.toast) setMsg(flash.toast); }, [flash]);
  useEffect(() => {
    const on = (e) => setMsg(e.detail);
    window.addEventListener('admin:toast', on);
    return () => window.removeEventListener('admin:toast', on);
  }, []);
  useEffect(() => {
    if (!msg) return undefined;
    const t = setTimeout(() => setMsg(''), 4200);
    return () => clearTimeout(t);
  }, [msg]);
  return msg ? <div className="toast" role="status" onClick={() => setMsg('')}>{msg}</div> : null;
}

export default function AdminLayout({ children }) {
  const page = usePage();
  const { auth, adminCounts, site } = page.props;
  const [menuOpen, setMenuOpen] = useState(false);
  const user = auth?.user;

  // The admin keeps its light design whatever the visitor theme is.
  useEffect(() => { delete document.documentElement.dataset.theme; }, []);
  useEffect(() => setMenuOpen(false), [page.url]);

  const signOut = () => router.post('/admin/logout');

  return (
    <div className="admin">
      {/* Phone / tablet top bar */}
      <div className="admin-top">
        <div className="admin-top-bar">
          <Link href="/admin" className="admin-brand" style={{ padding: 0 }}>
            <Mark identity={site.identity} className="mark" />
            <span className="txt"><b>Admin</b></span>
          </Link>
          <button type="button" className="icon-btn" aria-label="Admin menu" aria-expanded={menuOpen} onClick={() => setMenuOpen(!menuOpen)}>
            {menuOpen ? <Close /> : <Menu />}
          </button>
        </div>
        {menuOpen && (
          <nav aria-label="Admin">
            <NavGroups counts={adminCounts} onNavigate={() => setMenuOpen(false)} />
            <a href="/" className="side-live" target="_blank" rel="noopener">View live site ↗</a>
            <button type="button" className="side-live link-button" onClick={signOut}>Sign out</button>
          </nav>
        )}
      </div>

      {/* Desktop sidebar */}
      <aside className="admin-side" aria-label="Admin">
        <Link href="/admin" className="admin-brand">
          <Mark identity={site.identity} className="mark" />
          <span className="txt"><b>{site.identity.wordmark}</b><span>Admin</span></span>
        </Link>
        <NavGroups counts={adminCounts} />
        <div className="side-foot">
          <a href="/" className="side-live" target="_blank" rel="noopener">View live site ↗</a>
          <div className="side-user">
            <Link href="/admin/account" className="av" aria-label="Account">{user?.initials}</Link>
            <span className="txt"><b>{user?.name}</b><span>Owner · <button type="button" className="link-button underline" onClick={signOut}>Sign out</button></span></span>
          </div>
        </div>
      </aside>

      <main className="admin-main">{children}</main>
      <Toasts />
    </div>
  );
}
