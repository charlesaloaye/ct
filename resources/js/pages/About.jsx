import { Link } from '@inertiajs/react';
import PageShell, { LogoTile, Paragraphs } from '../components/PageShell.jsx';
import { linkProps, useSite } from '../components/Site.jsx';

export default function About({ copy, about, portraitUrl, portraitAlt, ventures, roles }) {
  const { identity, socials, contact } = useSite();
  return (
    <PageShell
      copy={copy}
      aside={portraitUrl && <div className="about-portrait"><img src={portraitUrl} alt={portraitAlt} fetchPriority="high" /></div>}
    >
      <section className="container about-body">
        <div className="about-main prose">
          <Paragraphs text={about.body} />
        </div>
        <aside className="about-facts" aria-label="At a glance">
          {identity.jobTitles?.length > 0 && (
            <div className="fact"><span className="fact-l">Known for</span><ul>{identity.jobTitles.map((t) => <li key={t}>{t}</li>)}</ul></div>
          )}
          {identity.location && <div className="fact"><span className="fact-l">Based in</span><span>{identity.location}</span></div>}
          {identity.alternateNames?.length > 0 && <div className="fact"><span className="fact-l">Also known as</span><span>{identity.alternateNames.join(' · ')}</span></div>}
          {contact.email && <div className="fact"><span className="fact-l">Email</span><a href={`mailto:${contact.email}`}>{contact.email}</a></div>}
          {socials.length > 0 && (
            <div className="fact"><span className="fact-l">Profiles</span><span className="fact-links">{socials.map((s) => <a key={s.href} href={s.href} {...linkProps(s.href)} rel="me noopener noreferrer">{s.label}</a>)}</span></div>
          )}
        </aside>
      </section>

      {ventures.length > 0 && (
        <section className="container section-md">
          <div className="section-head"><div className="section-head-copy"><span className="eyebrow">Ventures</span><h2 className="h-sub">Companies</h2></div><Link href="/ventures" className="underline-link">All ventures</Link></div>
          <div className="venture-list compact">
            {ventures.map((v) => (
              <Link key={v.id} href={`/ventures#${v.slug}`} className="venture-row">
                <LogoTile url={v.logoUrl} name={v.name} size="sm" />
                <span className="venture-row-text"><b>{v.name}</b><span>{v.role}</span></span>
              </Link>
            ))}
          </div>
        </section>
      )}

      {roles.length > 0 && (
        <section className="container section-md">
          <div className="section-head"><div className="section-head-copy"><span className="eyebrow">Experience</span><h2 className="h-sub">Recent roles</h2></div><Link href="/experience" className="underline-link">Full experience</Link></div>
          <div>{roles.map((r) => (
            <div key={r.id} className="role"><span className="role-years">{r.years}</span><div className="role-body"><span className="role-org">{r.org}</span><span className="role-desc">{[r.role, r.place].filter(Boolean).join(' · ')}{r.desc ? ` - ${r.desc}` : ''}</span></div></div>
          ))}</div>
        </section>
      )}

      <section className="container section-md">
        <div className="cta-dark">
          <div className="cta-dark-copy"><h2>Work with {identity.publicName || identity.shortName}</h2><p>Projects, codebase rescues, mentorship and speaking.</p></div>
          <Link href="/contact" className="btn btn-accent" style={{ fontWeight: 700 }}>Get in touch</Link>
        </div>
      </section>
    </PageShell>
  );
}
