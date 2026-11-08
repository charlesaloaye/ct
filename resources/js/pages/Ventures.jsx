import { Link } from '@inertiajs/react';
import PageShell, { LogoTile } from '../components/PageShell.jsx';
import { linkProps } from '../components/Site.jsx';
import { trackOutbound } from '../lib/track.js';

export default function Ventures({ copy, ventures }) {
  return (
    <PageShell copy={copy}>
      <section className="container section-md venture-list">
        {ventures.length === 0 && <p className="empty">Ventures will appear here.</p>}
        {ventures.map((v) => (
          <article key={v.id} id={v.slug} className="venture">
            <div className="venture-head">
              <LogoTile url={v.logoUrl} name={v.name} size="lg" />
              <div className="venture-title">
                <h2>{v.name}</h2>
                <span className="venture-role">{v.role}{v.years ? ` · ${v.years}` : ''}</span>
              </div>
              {v.href && <a href={v.href} {...linkProps(v.href)} onClick={trackOutbound(v.href, v.name)} className="link-accent venture-link">{v.domain} ↗</a>}
            </div>
            {v.desc && <p className="venture-desc">{v.desc}</p>}
            {v.products.length > 0 && (
              <div className="venture-products">
                <span className="eyebrow">Products</span>
                <div className="mini-products">
                  {v.products.map((p) => (
                    <a key={p.id} href={p.href || '/products'} {...linkProps(p.href)} onClick={p.href ? trackOutbound(p.href, p.name) : undefined} className="mini-product">
                      <LogoTile url={p.logoUrl} name={p.name} size="sm" />
                      <span><b>{p.name}</b><span>{p.domain}</span></span>
                    </a>
                  ))}
                </div>
              </div>
            )}
          </article>
        ))}
      </section>
      <section className="container section-md"><Link href="/products" className="underline-link">See every product →</Link></section>
    </PageShell>
  );
}
