import { Link } from '@inertiajs/react';
import SiteNav from '../components/SiteNav.jsx';
import { SimpleFooter } from '../components/Footers.jsx';
import { FlashToast, Seo, linkProps, useSite } from '../components/Site.jsx';
import { Shot } from '../components/Projects.jsx';
import { trackOutbound } from '../lib/track.js';

export default function CaseStudy({ study: p, sections }) {
  const { primaryCta } = useSite();
  const copy = sections.caseStudy;
  const t = p.testimonial;
  const meta = [
    p.client && { l: 'Client', v: p.client },
    p.role && { l: 'My role', v: p.role },
    p.years && { l: 'Timeline', v: p.years },
  ].filter(Boolean);

  return (
    <>
      <Seo />
      <SiteNav />
      <main>
        <section className="container cs-hero">
          <Link href="/work" className="back-link">← All work</Link>
          <span className="cat-row"><span className="cat-pill" style={{ padding: '6px 12px' }}>Case study</span>{p.cat && <span className="muted" style={{ padding: '6px 0' }}>{p.cat}</span>}</span>
          <h1 className="cs-title">{p.title}</h1>
          {p.summary && <p className="cs-summary">{p.summary}</p>}
          <div className="cs-meta">
            {meta.map((m) => <div key={m.l}><span className="l">{m.l}</span><span className="v">{m.v}</span></div>)}
            {p.href && <div><span className="l">Live site</span><a href={p.href} {...linkProps(p.href)} onClick={trackOutbound(p.href, p.client || p.title)} className="v link-accent">{p.domain} ↗</a></div>}
          </div>
        </section>

        {p.screenshotUrl && (
          <section className="container" style={{ paddingTop: 16 }}>
            <div className="cs-cover-wrap">
              <div className="cs-cover"><Shot url={p.screenshotUrl} alt={p.title} domain={p.domain} /></div>
            </div>
          </section>
        )}

        <section className="container cs-story">
          {p.challenge && (
            <div className="cs-block">
              <h2>The challenge</h2>
              <div className="cs-block-body"><p>{p.challenge}</p></div>
            </div>
          )}
          {(p.built || p.features.length > 0) && (
            <div className="cs-block">
              <h2>What I built</h2>
              <div className="cs-block-body">
                {p.built && <p>{p.built}</p>}
                {p.features.length > 0 && (
                  <div className="feature-grid">
                    {p.features.map((f, i) => (
                      <div key={f.t + i} className="feature">
                        {f.n && <span className="n">{f.n}</span>}
                        <span className="t">{f.t}</span>
                        {f.d && <span className="d">{f.d}</span>}
                      </div>
                    ))}
                  </div>
                )}
              </div>
            </div>
          )}
          {p.techStack.length > 0 && (
            <div className="cs-block">
              <h2>Built with</h2>
              <div className="cs-block-body"><div className="chips">{p.techStack.map((s) => <span key={s} className="chip">{s}</span>)}</div></div>
            </div>
          )}
        </section>

        {p.gallery.length > 0 && (
          <section className="container" style={{ paddingTop: 72, paddingBottom: 24 }}>
            <div className="gallery">
              {p.gallery.map((url, i) => (
                <div key={url} style={{ background: i % 2 ? '#111214' : 'var(--soft)' }}><img src={url} alt={`${p.title} screen ${i + 1}`} className="media-fill" loading="lazy" /></div>
              ))}
            </div>
          </section>
        )}

        {(p.outcome || p.results.length > 0) && (
          <section className="container" style={{ paddingTop: 96, paddingBottom: 24 }}>
            <div className="outcome">
              <div className="outcome-head">
                <h2>The outcome</h2>
                {p.outcome && <p>{p.outcome}</p>}
              </div>
              {p.results.length > 0 && (
                <div className="results">
                  {p.results.map((r) => (
                    <div key={r.l} className="result"><span className="v">{r.v}</span><span className="l">{r.l}</span></div>
                  ))}
                </div>
              )}
            </div>
          </section>
        )}

        {t && (
          <section className="container cs-quote" style={{ paddingTop: 96, paddingBottom: 24 }}>
            <figure>
              <blockquote>{t.quote}</blockquote>
              <figcaption><strong>{t.name}</strong>{[t.role, t.company || p.client].filter(Boolean).length > 0 && ` · ${[t.role, t.company || p.client].filter(Boolean).join(', ')}`}</figcaption>
            </figure>
          </section>
        )}

        <section className="container cs-next">
          {p.next ? (
            <Link href={`/work/${p.next.slug}`} className="next-card">
              <span className="k">Next case study →</span>
              <span className="t">{p.next.title}</span>
              {p.next.cat && <span className="c">{p.next.cat}</span>}
            </Link>
          ) : (
            <Link href="/work" className="next-card">
              <span className="k">More work →</span>
              <span className="t">See every project</span>
            </Link>
          )}
          {primaryCta && (
            <a href={primaryCta.href} {...linkProps(primaryCta.href)} className="cs-cta">
              <span className="t">{copy.ctaTitle}</span>
              <span className="a">{copy.ctaButton || primaryCta.label}</span>
            </a>
          )}
        </section>
      </main>
      <SimpleFooter />
      <FlashToast />
    </>
  );
}
