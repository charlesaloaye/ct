import { Link } from '@inertiajs/react';
import { ArrowUpRight } from './Icons.jsx';
import { linkProps } from './Site.jsx';
import { trackOutbound } from '../lib/track.js';

/** A project screenshot; without one, a clean tile with the site's domain. */
export function Shot({ url, alt, domain, dark = false }) {
  if (url) return <img src={url} alt={alt || ''} className="media-fill" loading="lazy" />;
  return <span className={`shot-fallback${dark ? ' dark' : ''}`}>{domain || alt}</span>;
}

export function ProjectCard({ p }) {
  return (
    <article className={`project${p.showOnMobile === false ? ' hide-mobile' : ''}`}>
      <div className="project-frame">
        <div className="project-screen"><Shot url={p.screenshotUrl} alt={p.title} domain={p.domain} /></div>
      </div>
      <div className="project-row">
        <div className="project-text">
          <h3>{p.title}</h3>
          {p.desc && <p>{p.desc}</p>}
        </div>
        {p.tag && <span className="tag">{p.tag}</span>}
      </div>
      <div className="project-links">
        {p.caseStudy && <Link href={`/work/${p.caseStudy}`} className="cs-link">Read case study</Link>}
        {p.href && <a href={p.href} {...linkProps(p.href)} onClick={trackOutbound(p.href, p.title)} className="link-accent" style={{ fontSize: 15, padding: '4px 0' }}>{p.domain} ↗</a>}
      </div>
    </article>
  );
}

export function FeaturedProject({ featured }) {
  return (
    <article className="featured">
      <div className="featured-copy">
        <span className="kicker">Featured{featured.kicker ? ` · ${featured.kicker}` : ''}</span>
        <h3>{featured.title}</h3>
        {featured.desc && <p>{featured.desc}</p>}
        {(featured.role || featured.since) && (
          <div className="meta-grid">
            {featured.role && <div className="meta-item"><span className="meta-label">Role</span><span className="meta-value">{featured.role}</span></div>}
            {featured.since && <div className="meta-item"><span className="meta-label">Since</span><span className="meta-value">{featured.since}</span></div>}
          </div>
        )}
        <div className="featured-actions">
          {featured.caseStudy && <Link href={`/work/${featured.caseStudy}`} className="btn btn-ink" style={{ padding: '14px 20px', fontSize: 15, borderRadius: 12 }}>Read the case study</Link>}
          {featured.href && <a href={featured.href} {...linkProps(featured.href)} onClick={trackOutbound(featured.href, featured.title)} className="link-accent" style={{ display: 'inline-flex', alignItems: 'center', gap: 8, padding: '10px 0' }}>Visit {featured.domain} <ArrowUpRight /></a>}
        </div>
      </div>
      <div className="featured-media">
        <div className="featured-shot"><Shot url={featured.screenshotUrl} alt={featured.title} domain={featured.domain} /></div>
      </div>
    </article>
  );
}
