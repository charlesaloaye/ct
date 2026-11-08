import SiteNav from './SiteNav.jsx';
import { SimpleFooter } from './Footers.jsx';
import { FlashToast, Seo } from './Site.jsx';

/** The frame for every inner page: navbar, a page heading from Pages & SEO, content, footer. */
export default function PageShell({ copy, children, aside }) {
  return (
    <>
      <Seo />
      <SiteNav />
      <main>
        <section className="container page-hero">
          <div className="page-hero-copy">
            {copy?.eyebrow && <span className="eyebrow">{copy.eyebrow}</span>}
            {copy?.title && <h1 className="blog-title">{copy.title}</h1>}
            {copy?.lead && <p className="blog-lead">{copy.lead}</p>}
          </div>
          {aside}
        </section>
        {children}
      </main>
      <SimpleFooter />
      <FlashToast />
    </>
  );
}

/** Plain-text paragraphs (blank-line separated), safely rendered without HTML. */
export function Paragraphs({ text, className }) {
  return (text || '').split(/\n\s*\n/).map((p) => p.trim()).filter(Boolean).map((p, i) => <p key={i} className={className}>{p}</p>);
}

/** A logo on a white tile, or the name's initials when there is no logo yet. */
export function LogoTile({ url, name, size = 'md' }) {
  return (
    <span className={`logo-tile logo-tile-${size}`} aria-hidden={url ? undefined : true}>
      {url ? <img src={url} alt={`${name} logo`} loading="lazy" /> : <span>{(name || '').split(/\s+/).map((w) => w[0]).join('').slice(0, 2)}</span>}
    </span>
  );
}
