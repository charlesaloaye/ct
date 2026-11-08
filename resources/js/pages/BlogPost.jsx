import { Link } from '@inertiajs/react';
import SiteNav from '../components/SiteNav.jsx';
import { SimpleFooter } from '../components/Footers.jsx';
import { FlashToast, Seo, linkProps, useSite } from '../components/Site.jsx';
import { imageOnly, renderInline } from '../lib/inline.jsx';

function Block({ b }) {
  switch (b.type) {
    case 'h2': return <h2 id={b.id}>{b.text}</h2>;
    case 'code': return <pre><code>{b.text}</code></pre>;
    case 'quote': return <blockquote>{renderInline(b.text)}</blockquote>;
    default: {
      const img = imageOnly(b.text);
      if (img) return <img src={img.src} alt={img.alt} loading="lazy" className="prose-img" />;
      return <p>{renderInline(b.text)}</p>;
    }
  }
}

const date = (iso) => (iso ? new Date(iso).toLocaleDateString('en-GB', { day: 'numeric', month: 'long', year: 'numeric' }) : '');

export default function BlogPost({ post, related, sections }) {
  const { identity, primaryCta } = useSite();
  const copy = sections.article;
  const body = post.body || [];
  const toc = body.filter((b) => b.type === 'h2');

  return (
    <>
      <Seo />
      <SiteNav />
      <main>
        <article>
          <header className="article-head">
            <Link href="/blog" className="back-link">← All articles</Link>
            <span className="cat-row">{post.cat && <span className="cat-pill">{post.cat}</span>}<span className="muted" style={{ padding: '5px 0' }}>{[date(post.publishedAt), post.read].filter(Boolean).join(' · ')}</span></span>
            <h1 className="article-title">{post.title}</h1>
            {post.excerpt && <p className="article-dek">{post.excerpt}</p>}
            <div className="author">
              <span className="author-mark">{identity.initials}</span>
              <span style={{ display: 'flex', flexDirection: 'column', gap: 2 }}>
                <b>{identity.name}</b>
                {identity.authorTitle && <span className="muted" style={{ fontSize: 14 }}>{identity.authorTitle}</span>}
              </span>
            </div>
          </header>

          {post.coverUrl && (
            <div className="container">
              <div className="cover"><img src={post.coverUrl} alt="" className="media-fill" /></div>
            </div>
          )}

          <div className="container article-layout">
            {toc.length > 0 && (
              <aside className="toc">
                <span className="toc-label">On this page</span>
                {toc.map((h) => <a key={h.id} href={`#${h.id}`}>{h.toc || h.text}</a>)}
              </aside>
            )}
            <div className="prose">
              {body.map((b, i) => <Block key={i} b={b} />)}
              {post.tags && (
                <div className="article-tags">{post.tags.map((t) => <span key={t}>{t}</span>)}</div>
              )}
            </div>
          </div>
        </article>

        {primaryCta && copy.ctaTitle && (
          <section className="container" style={{ paddingTop: 24, paddingBottom: 24 }}>
            <div className="cta-dark">
              <div className="cta-dark-copy">
                <h2>{copy.ctaTitle}</h2>
                {copy.ctaText && <p>{copy.ctaText}</p>}
              </div>
              <a href={primaryCta.href} {...linkProps(primaryCta.href)} className="btn btn-accent" style={{ fontWeight: 700 }}>{copy.ctaButton || primaryCta.label}</a>
            </div>
          </section>
        )}

        {related.length > 0 && (
          <section className="container related">
            <h2>Keep reading</h2>
            <div className="related-grid">
              {related.map((r) => (
                <Link key={r.slug} href={`/blog/${r.slug}`} className="related-card">
                  <span className="k">{[r.cat, r.read.replace(' read', '')].filter(Boolean).join(' · ')}</span>
                  <span className="t">{r.title}</span>
                </Link>
              ))}
            </div>
          </section>
        )}
      </main>
      <SimpleFooter />
      <FlashToast />
    </>
  );
}
