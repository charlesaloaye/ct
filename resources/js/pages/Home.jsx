import { Link } from '@inertiajs/react';
import ContactForm from '../components/ContactForm.jsx';
import { LogoTile } from '../components/PageShell.jsx';
import SiteNav from '../components/SiteNav.jsx';
import { Footer } from '../components/Footers.jsx';
import { ArrowRight, Quote } from '../components/Icons.jsx';
import { FeaturedProject, ProjectCard } from '../components/Projects.jsx';
import { FlashToast, Seo, linkProps, useSite } from '../components/Site.jsx';
import { fill, splitHeadline } from '../lib/text.js';
import { trackOutbound } from '../lib/track.js';

function Hero({ hero, stats }) {
  const { availability, contact, identity, primaryCta } = useSite();
  const [before, highlight, after] = splitHeadline(hero.headline);
  return (
    <section id="top" className="container hero">
      <div className="hero-row">
        <div className="hero-copy">
          {availability && <div className="badge"><span className="badge-dot" />{availability}</div>}
          <h1 className="hero-title">
            {before}
            {/* One pill per word, so wrapped lines fit their text and never paint over each other. */}
            {highlight.trim().split(/\s+/).map((word, i) => <span key={i}>{i > 0 && ' '}<span className="highlight">{word}</span></span>)}
            {after}
          </h1>
          {hero.intro && <p className="hero-lead">{hero.intro}</p>}
          <div className="btn-row">
            {primaryCta && <a href={primaryCta.href} {...linkProps(primaryCta.href)} className="btn btn-ink">{primaryCta.label} <ArrowRight /></a>}
            {contact.cv && hero.cvLabel && <a href={contact.cv} className="btn btn-outline" target="_blank" rel="noopener">{hero.cvLabel}</a>}
          </div>
        </div>
        <div className="hero-media">
          <div className="portrait">
            {hero.portraitUrl
              ? <img src={hero.portraitUrl} alt={hero.portraitAlt || identity.name} fetchPriority="high" />
              : <span className="portrait-mark" aria-hidden="true">{identity.initials}</span>}
          </div>
          {hero.shippedTitle && (
            <div className="shipped-card">
              <span className="shipped-k">Last shipped</span>
              {hero.shippedUrl
                ? <a className="shipped-t" href={hero.shippedUrl} {...linkProps(hero.shippedUrl)} onClick={trackOutbound(hero.shippedUrl, hero.shippedTitle)}>{hero.shippedTitle}</a>
                : <span className="shipped-t">{hero.shippedTitle}</span>}
              {hero.shippedNote && <span className="shipped-s">{hero.shippedNote}</span>}
            </div>
          )}
        </div>
      </div>
      {stats.length > 0 && (
        <div className="stats">
          {stats.map((s) => (
            <div key={s.l} className="stat">
              <span className="stat-v">{s.v}</span>
              <span className="stat-l">{s.l}</span>
            </div>
          ))}
        </div>
      )}
    </section>
  );
}

function Clients({ clients, label }) {
  if (clients.length === 0) return null;
  return (
    <section aria-label={label || 'Clients'} className="container clients">
      <div className="clients-box">
        {label && <span className="clients-label">{label}</span>}
        {clients.map((c) => (
          <span key={c.name} className="client-tile" title={c.name}>
            {c.logoUrl ? <img src={c.logoUrl} alt={c.name} loading="lazy" /> : <span className="client">{c.name}</span>}
          </span>
        ))}
      </div>
    </section>
  );
}

function Work({ projects, total, copy }) {
  if (projects.length === 0) return null;
  const featured = projects.find((p) => p.featured);
  const grid = projects.filter((p) => p !== featured);
  return (
    <section id="work" className="container section-md">
      <div className="section-head">
        <div className="section-head-copy">
          {copy.eyebrow && <span className="eyebrow">{copy.eyebrow}</span>}
          {copy.heading && <h2 className="h-section">{copy.heading}</h2>}
        </div>
        <Link href="/work" className="underline-link">{fill(copy.linkLabel || 'All {count} projects', { count: total })}</Link>
      </div>

      {featured && <FeaturedProject featured={featured} />}

      {grid.length > 0 && <div className="project-grid">{grid.map((p) => <ProjectCard key={p.id} p={p} />)}</div>}
    </section>
  );
}

function Products({ products, copy }) {
  if (products.length === 0) return null;
  return (
    <section id="products" className="container section">
      <div className="section-head" style={{ marginBottom: 48 }}>
        <div className="section-head-copy">
          {copy.eyebrow && <span className="eyebrow">{copy.eyebrow}</span>}
          {copy.heading && <h2 className="h-section">{copy.heading}</h2>}
        </div>
        {copy.note && <p className="section-head-note">{copy.note}</p>}
      </div>
      <div className="products-grid">
        {products.map((p) => (
          <article key={p.id} className="product">
            <div className="product-top">
              {p.logoUrl ? <LogoTile url={p.logoUrl} name={p.name} size="md" /> : <span className="mono-tile" aria-hidden="true">{p.mono}</span>}
              {p.role && <span className="role-pill">{p.role}</span>}
            </div>
            <div style={{ display: 'flex', flexDirection: 'column', gap: 8 }}>
              <h3>{p.name}</h3>
              {p.cat && <span className="product-cat">{p.cat}</span>}
            </div>
            {p.desc && <p>{p.desc}</p>}
            <div className="product-foot">
              {p.venture ? <Link href={`/ventures#${p.venture.slug}`} className="product-proof">By {p.venture.name}</Link> : p.proof && <span className="product-proof">{p.proof}</span>}
              {p.href && <a href={p.href} {...linkProps(p.href)} onClick={trackOutbound(p.href, p.name)} className="link-accent" style={{ fontSize: 15, padding: '6px 0' }}>{p.domain} ↗</a>}
            </div>
          </article>
        ))}
      </div>
    </section>
  );
}

function Rescue({ rescue, copy }) {
  if (!copy.heading && rescue.length === 0) return null;
  return (
    <section className="container section-md">
      <div className="rescue">
        <div className="rescue-copy">
          {copy.eyebrow && <span className="eyebrow-green">{copy.eyebrow}</span>}
          {copy.heading && <h2>{copy.heading}</h2>}
          {copy.body && <p>{copy.body}</p>}
          {copy.cta && <a href="#contact" className="btn btn-accent">{copy.cta}</a>}
        </div>
        {rescue.length > 0 && (
          <div className="rescue-steps">
            {rescue.map((r) => (
              <div key={r.n + r.t} className="rescue-step">
                <span className="step-num">{r.n}</span>
                <div className="step-body">
                  <span className="step-t">{r.t}</span>
                  {r.d && <span className="step-d">{r.d}</span>}
                </div>
              </div>
            ))}
          </div>
        )}
      </div>
    </section>
  );
}

function Services({ services, copy }) {
  if (services.length === 0) return null;
  return (
    <section id="services" className="container section">
      <div className="split">
        <div className="split-aside">
          {copy.eyebrow && <span className="eyebrow">{copy.eyebrow}</span>}
          {copy.heading && <h2 className="h-section">{copy.heading}</h2>}
          {copy.lead && <p className="split-lead">{copy.lead}</p>}
        </div>
        <div className="split-main">
          {services.map((s) => (
            <div key={s.id} className="service">
              <span className="service-n">{s.n}</span>
              <h3>{s.title}</h3>
              {s.desc && <p>{s.desc}</p>}
            </div>
          ))}
        </div>
      </div>
    </section>
  );
}

function Process({ process, copy }) {
  if (process.length === 0) return null;
  return (
    <section id="process" className="container section">
      <div className="process-head">
        {copy.eyebrow && <span className="eyebrow">{copy.eyebrow}</span>}
        {copy.heading && <h2 className="h-section">{copy.heading}</h2>}
      </div>
      <div className="process-grid">
        {process.map((p) => (
          <div key={p.n + p.t} className="process-card">
            <span className="process-n">{p.n}</span>
            <h3>{p.t}</h3>
            {p.d && <p>{p.d}</p>}
          </div>
        ))}
      </div>
    </section>
  );
}

function Testimonial({ testimonial: t }) {
  if (!t) return null;
  return (
    <section className="container section">
      <figure className="quote">
        <Quote />
        <blockquote>{t.quote}</blockquote>
        <figcaption>
          {t.avatarUrl ? <img className="avatar" src={t.avatarUrl} alt="" loading="lazy" /> : <span className="avatar" aria-hidden="true">{(t.name || '').slice(0, 1)}</span>}
          <span className="quote-who"><b>{t.name}</b><span className="muted" style={{ fontSize: 14 }}>{[t.role, t.company].filter(Boolean).join(', ')}</span></span>
        </figcaption>
      </figure>
    </section>
  );
}

function ToolkitExperience({ stack, roles, toolkit, experience }) {
  if (stack.length === 0 && roles.length === 0) return null;
  return (
    <section id="experience" className="container section tx">
      {stack.length > 0 && (
        <div className="tx-stack">
          <div className="stack-sm">
            {toolkit.eyebrow && <span className="eyebrow">{toolkit.eyebrow}</span>}
            {toolkit.heading && <h2 className="h-sub">{toolkit.heading}</h2>}
          </div>
          {stack.map((g) => (
            <div key={g.label} className="stack-group">
              <span className="stack-label">{g.label}</span>
              <div className="chips">{g.items.map((i) => <span key={i} className="chip">{i}</span>)}</div>
            </div>
          ))}
        </div>
      )}
      {roles.length > 0 && (
        <div className="tx-exp">
          <div className="stack-sm">
            {experience.eyebrow && <span className="eyebrow">{experience.eyebrow}</span>}
            {experience.heading && <h2 className="h-sub">{experience.heading}</h2>}
          </div>
          <div>
            {roles.map((r) => (
              <div key={r.id} className="role">
                <span className="role-years">{r.years}</span>
                <div className="role-body">
                  <span className="role-org">{r.org}</span>
                  <span className="role-desc">{[r.role, r.place].filter(Boolean).join(' · ')}{r.desc ? ` - ${r.desc}` : ''}</span>
                </div>
              </div>
            ))}
          </div>
        </div>
      )}
    </section>
  );
}

export default function Home({ home }) {
  const { sections } = home;
  return (
    <>
      <Seo />
      <SiteNav />
      <main>
        <Hero hero={home.hero} stats={home.stats} />
        <Clients clients={home.clients} label={sections.clients.label} />
        <Work projects={home.projects} total={home.projectCount} copy={sections.work} />
        <Products products={home.products} copy={sections.products} />
        <Rescue rescue={home.rescue} copy={sections.rescue} />
        <Services services={home.services} copy={sections.services} />
        <Process process={home.process} copy={sections.process} />
        <Testimonial testimonial={home.testimonials[0]} />
        <ToolkitExperience stack={home.stack} roles={home.roles} toolkit={sections.toolkit} experience={sections.experience} />
        <ContactForm copy={sections.contact} />
      </main>
      <Footer />
      <FlashToast />
    </>
  );
}
