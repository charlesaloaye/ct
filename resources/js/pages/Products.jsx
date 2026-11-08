import { Link } from '@inertiajs/react';
import PageShell, { LogoTile } from '../components/PageShell.jsx';
import { linkProps } from '../components/Site.jsx';
import { trackOutbound } from '../lib/track.js';

export default function Products({ copy, products }) {
  return (
    <PageShell copy={copy}>
      <section className="container section-md">
        {products.length === 0 && <p className="empty">Products will appear here.</p>}
        <div className="product-cards">
          {products.map((p) => (
            <article key={p.id} className="product-card">
              <div className="product-card-top">
                <LogoTile url={p.logoUrl} name={p.name} size="md" />
                {p.role && <span className="role-pill">{p.role}</span>}
              </div>
              <h2>{p.name}</h2>
              {p.cat && <span className="product-cat">{p.cat}</span>}
              {p.desc && <p>{p.desc}</p>}
              <div className="product-foot">
                {p.venture ? <Link href={`/ventures#${p.venture.slug}`} className="product-proof">By {p.venture.name}</Link> : p.proof && <span className="product-proof">{p.proof}</span>}
                {p.href && <a href={p.href} {...linkProps(p.href)} onClick={trackOutbound(p.href, p.name)} className="link-accent">{p.domain} ↗</a>}
              </div>
            </article>
          ))}
        </div>
      </section>
    </PageShell>
  );
}
