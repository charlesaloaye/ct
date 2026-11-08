import { useMemo, useState } from 'react';
import { Link, useForm, usePage } from '@inertiajs/react';
import SiteNav from '../components/SiteNav.jsx';
import { SimpleFooter } from '../components/Footers.jsx';
import { FlashToast, Seo } from '../components/Site.jsx';
import { trackOutbound } from '../lib/track.js';

function Newsletter({ copy, linkNote }) {
  const { flash } = usePage();
  const form = useForm({ email: '', website: '' });
  const done = flash?.subscribe || (linkNote && !form.isDirty ? linkNote : null);

  const submit = (e) => {
    e.preventDefault();
    form.post('/subscribe', { preserveScroll: true, onSuccess: () => form.reset() });
  };

  return (
    <div className="newsletter" id="newsletter">
      <div className="newsletter-copy">
        {copy.newsletterTitle && <h2>{copy.newsletterTitle}</h2>}
        {copy.newsletterText && <p>{copy.newsletterText}</p>}
      </div>
      {done ? (
        <p style={{ fontWeight: 700 }} role="status">{done}</p>
      ) : (
        <form onSubmit={submit} noValidate>
          <label>Email address
            <input type="email" autoComplete="email" placeholder="you@company.com" value={form.data.email} onChange={(e) => form.setData('email', e.target.value)} required />
          </label>
          <input type="text" name="website" className="hp-field" tabIndex={-1} autoComplete="off" aria-hidden="true" value={form.data.website} onChange={(e) => form.setData('website', e.target.value)} />
          <button type="submit" disabled={form.processing}>{form.processing ? 'Subscribing...' : 'Subscribe'}</button>
          {form.errors.email && <p role="alert" style={{ margin: 0, fontWeight: 700, width: '100%' }}>{form.errors.email}</p>}
        </form>
      )}
    </div>
  );
}

export default function Blog({ posts, categories, writings = [], sections, subscriptionNote }) {
  const copy = sections.blog;
  const [cat, setCat] = useState('All');
  const [query, setQuery] = useState('');
  const tints = Object.fromEntries(categories.map((c) => [c.name, c.tint]));

  const featured = posts.find((p) => p.featured);
  const q = query.trim().toLowerCase();
  const list = useMemo(() => posts.filter((p) => p !== featured)
    .filter((p) => cat === 'All' || p.cat === cat)
    .filter((p) => !q || `${p.title} ${p.excerpt} ${p.cat}`.toLowerCase().includes(q)), [posts, featured, cat, q]);
  const showFeatured = featured && (!q || `${featured.title} ${featured.excerpt} ${featured.cat}`.toLowerCase().includes(q)) && (cat === 'All' || cat === featured.cat);

  return (
    <>
      <Seo />
      <SiteNav />
      <main>
        <section className="container blog-hero">
          <div className="blog-hero-copy">
            {copy.eyebrow && <span className="eyebrow">{copy.eyebrow}</span>}
            {copy.title && <h1 className="blog-title">{copy.title}</h1>}
            {copy.lead && <p className="blog-lead">{copy.lead}</p>}
          </div>
          {posts.length > 1 && (
            <label className="search-field">Search articles
              <input type="search" className="input-light" placeholder={copy.searchHint || 'Search'} value={query} onChange={(e) => setQuery(e.target.value)} />
            </label>
          )}
        </section>

        {categories.length > 1 && (
          <div className="container chip-row" role="group" aria-label="Filter by category">
            {['All', ...categories.map((c) => c.name)].map((c) => (
              <button key={c} type="button" className={`chip-btn${c === cat ? ' on' : ''}`} aria-pressed={c === cat} onClick={() => setCat(c)}>{c}</button>
            ))}
          </div>
        )}

        {showFeatured && (
          <section className="container" style={{ paddingBottom: 32 }}>
            <Link href={`/blog/${featured.slug}`} className="blog-featured">
              <div className="blog-featured-copy">
                <span className="blog-featured-meta"><span className="pill">Featured</span><span style={{ color: '#B7B9B4' }}>{[featured.cat, featured.read].filter(Boolean).join(' · ')}</span></span>
                <h2>{featured.title}</h2>
                {featured.excerpt && <p>{featured.excerpt}</p>}
                <span className="blog-featured-cta">Read article →</span>
              </div>
              <div className="blog-featured-cover" style={featured.coverUrl ? undefined : { background: featured.tint || 'var(--soft)' }}>
                {featured.coverUrl ? <img src={featured.coverUrl} alt="" className="media-fill" /> : <span className="cover-cat">{featured.cat}</span>}
              </div>
            </Link>
          </section>
        )}

        <section className="container" style={{ paddingTop: 8, paddingBottom: 40 }}>
          {list.length > 0 && (
            <div className="post-grid">
              {list.map((p) => (
                <Link key={p.slug} href={`/blog/${p.slug}`} className="post-card">
                  <div className="post-thumb" style={p.coverUrl ? undefined : { background: tints[p.cat] || '#EEECE6' }}>
                    {p.coverUrl ? <img src={p.coverUrl} alt="" className="media-fill" loading="lazy" /> : <span>{p.cat}</span>}
                  </div>
                  <span className="post-meta">{p.cat && <span className="post-cat">{p.cat}</span>}<span>{p.read}</span></span>
                  <h3>{p.title}</h3>
                  {p.excerpt && <p>{p.excerpt}</p>}
                </Link>
              ))}
            </div>
          )}
          {list.length === 0 && !showFeatured && (
            <p className="empty">{posts.length === 0 ? 'The first articles are on their way. Subscribe below to get them first.' : 'No articles match that yet.'}</p>
          )}
        </section>

        {writings.length > 0 && (
          <section className="container" id="elsewhere" style={{ paddingTop: 40 }}>
            <div className="section-head-copy" style={{ marginBottom: 8 }}>
              <span className="eyebrow">Writing elsewhere</span>
              <h2 className="h-sub">On Medium, DEV and Hashnode</h2>
            </div>
            <div className="coverage">
              {writings.map((w) => (
                <a key={w.id} href={w.href} target="_blank" rel="noopener noreferrer" className="coverage-row" onClick={trackOutbound(w.href, w.title)}>
                  <span className="coverage-meta">{[w.publication, w.date && new Date(w.date).toLocaleDateString('en-GB', { month: 'short', year: 'numeric' })].filter(Boolean).join(' · ')}</span>
                  <span className="coverage-title">{w.title} ↗</span>
                  {w.summary && <span className="coverage-summary">{w.summary}</span>}
                </a>
              ))}
            </div>
          </section>
        )}

        <section className="container" style={{ paddingTop: 40, paddingBottom: 96 }}>
          <Newsletter copy={copy} linkNote={subscriptionNote} />
        </section>
      </main>
      <SimpleFooter />
      <FlashToast />
    </>
  );
}
