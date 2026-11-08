import { Link, router, usePage } from '@inertiajs/react';
import { PageHead } from '../../admin/ui.jsx';

function greeting() {
  const h = new Date().getHours();
  if (h < 12) return 'Good morning';
  if (h < 17) return 'Good afternoon';
  return 'Good evening';
}

function breakdown(c) {
  if (!c) return '';
  const parts = [];
  if (c.featured) parts.push(`${c.featured} featured`);
  if (c.scheduled) parts.push(`${c.scheduled} scheduled`);
  if (c.draft) parts.push(`${c.draft} draft${c.draft === 1 ? '' : 's'}`);
  if (c.hidden) parts.push(`${c.hidden} hidden`);
  return parts.length ? parts.join(' · ') : 'All published';
}

export default function Overview({ counts, latestMessages, stats, availability, setup }) {
  const { auth } = usePage().props;
  const today = new Date().toLocaleDateString('en-GB', { weekday: 'long', day: 'numeric', month: 'long' });
  const avail = Boolean(availability.enabled);

  const kpis = [
    { l: 'Projects', v: counts.content.projects.total, s: breakdown(counts.content.projects), href: '/admin/projects' },
    { l: 'Articles', v: counts.content.articles.total, s: breakdown(counts.content.articles), href: '/admin/articles' },
    { l: 'New enquiries', v: counts.unreadMessages, s: `${counts.inboxMessages} in inbox`, href: '/admin/messages' },
    { l: 'CV downloads', v: stats.cvDownloads, s: `Last 30 days · ${stats.pageViews} page views`, href: null },
  ];

  const toggleAvailability = () => router.patch('/admin/availability', { enabled: !avail }, { preserveScroll: true });

  return (
    <>
      <PageHead kicker={today} title={`${greeting()}, ${(auth.user?.name || '').split(' ')[0]}`}>
        <Link href="/admin/messages" className="a-btn a-outline">Open inbox</Link>
        <Link href="/admin/articles/create" className="a-btn a-green">Write with AI</Link>
        <Link href="/admin/projects/create" className="a-btn a-dark">+ Add project</Link>
      </PageHead>

      {setup.length > 0 && (
        <section className="panel setup-panel">
          <h2>Finish setting up</h2>
          {setup.map((s) => <Link key={s.label} href={s.href} className="quick">{s.label}</Link>)}
        </section>
      )}

      <div className="kpis">
        {kpis.map((k) => {
          const inner = <><span className="l">{k.l}</span><span className="v">{k.v}</span><span className="s">{k.s}</span></>;
          return k.href
            ? <Link key={k.l} href={k.href} className="kpi kpi-link">{inner}</Link>
            : <div key={k.l} className="kpi">{inner}</div>;
        })}
      </div>

      <div className="a-row">
        <section className="panel" style={{ flex: '1.5 1 420px', gap: 4 }}>
          <div className="panel-head" style={{ marginBottom: 8 }}>
            <h2>Latest enquiries</h2>
            <Link href="/admin/messages" className="text-link">View all</Link>
          </div>
          {latestMessages.map((m) => (
            <Link key={m.id} href={`/admin/messages?open=${m.id}`} className="msg-row" style={{ textDecoration: 'none', color: 'inherit' }}>
              <span className="msg-av">{m.initials}</span>
              <span className="msg-body"><b>{m.read ? m.name : `● ${m.name}`}</b><span>{m.preview}</span></span>
              <span className="msg-type">{m.type}</span>
            </Link>
          ))}
          {latestMessages.length === 0 && (
            <span className="sample-note" style={{ paddingTop: 8 }}>No enquiries yet - they arrive here from the contact form.</span>
          )}
        </section>

        <section className="side-col">
          <div className="avail">
            <div className="panel-head">
              <h2>Availability</h2>
              <button type="button" role="switch" aria-checked={avail} aria-label="Show availability badge" className={`switch${avail ? ' on' : ''}`} onClick={toggleAvailability}><span /></button>
            </div>
            <span className="sub">Badge on the hero and menu</span>
            <span className="val">{avail ? (availability.text || 'Add the badge text in Site settings') : 'Hidden - fully booked'}</span>
          </div>
          <div className="panel">
            <h2 style={{ marginBottom: 6 }}>Quick edits</h2>
            <Link href="/admin/settings" className="quick">Edit hero headline &amp; CV</Link>
            <Link href="/admin/pages" className="quick">Edit section copy &amp; SEO</Link>
            <Link href="/admin/products" className="quick">Update products</Link>
            <Link href="/admin/testimonials/create" className="quick">Add a testimonial</Link>
          </div>
        </section>
      </div>
    </>
  );
}
