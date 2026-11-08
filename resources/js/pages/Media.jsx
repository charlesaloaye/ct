import PageShell, { Paragraphs } from '../components/PageShell.jsx';
import { useSite } from '../components/Site.jsx';

const kinds = { interview: 'Interview', podcast: 'Podcast', talk: 'Talk', article: 'Article', feature: 'Feature' };
const date = (d) => (d ? new Date(d).toLocaleDateString('en-GB', { month: 'long', year: 'numeric' }) : '');

function Bio({ title, text }) {
  if (!text) return null;
  const copy = () => navigator.clipboard?.writeText(text).then(() => window.dispatchEvent(new CustomEvent('site:toast', { detail: 'Bio copied' })));
  return (
    <div className="bio-card">
      <div className="bio-card-head"><h3>{title}</h3><button type="button" className="small-ghost" onClick={copy}>Copy</button></div>
      <Paragraphs text={text} />
    </div>
  );
}

export default function Media({ copy, appearances, about, portraitUrl, logoUrl }) {
  const { identity, contact } = useSite();
  return (
    <PageShell copy={copy}>
      {appearances.length > 0 && (
        <section className="container section-md">
          <h2 className="h-sub" style={{ marginBottom: 16 }}>Coverage</h2>
          <div className="coverage">
            {appearances.map((a) => (
              <a key={a.id} href={a.href} target="_blank" rel="noopener noreferrer" className="coverage-row">
                <span className="coverage-meta">{[kinds[a.kind], a.outlet, date(a.date)].filter(Boolean).join(' · ')}</span>
                <span className="coverage-title">{a.title} ↗</span>
                {a.summary && <span className="coverage-summary">{a.summary}</span>}
              </a>
            ))}
          </div>
        </section>
      )}
      <section className="container section-md">
        <h2 className="h-sub" style={{ marginBottom: 8 }}>Press kit</h2>
        <p className="muted" style={{ marginTop: 0 }}>For editors, event organisers and podcast hosts: approved bios and photos.</p>
        {about.nameNote && <p className="name-note">{about.nameNote}</p>}
        <div className="bio-grid">
          <Bio title="Short bio" text={about.bioShort} />
          <Bio title="Medium bio" text={about.bioMedium} />
        </div>
        <div className="press-photos">
          {portraitUrl && <a href={portraitUrl} download className="press-photo"><img src={portraitUrl} alt={`${identity.publicName} portrait`} loading="lazy" /><span>Portrait (download)</span></a>}
          {logoUrl && <a href={logoUrl} download className="press-photo"><img src={logoUrl} alt={`${identity.publicName} profile photo`} loading="lazy" /><span>Profile photo (download)</span></a>}
        </div>
        {contact.email && <p style={{ marginTop: 24 }}>Press enquiries: <a href={`mailto:${contact.email}`}>{contact.email}</a></p>}
      </section>
    </PageShell>
  );
}
