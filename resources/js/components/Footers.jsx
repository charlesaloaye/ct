import { Link } from '@inertiajs/react';
import SocialLinks from './SocialLinks.jsx';
import { useSite } from './Site.jsx';

const year = new Date().getFullYear();

/** Every page, linked from every footer: helps visitors and search engines find them. */
const pages = [
  ['About', '/about'], ['Experience', '/experience'], ['Ventures', '/ventures'], ['Products', '/products'],
  ['Work', '/work'], ['Expertise', '/expertise'], ['Blog', '/blog'], ['Media & press', '/media'], ['Contact', '/contact'],
];

function PageLinks() {
  return (
    <nav aria-label="Site" className="footer-pages">
      {pages.map(([label, href]) => <Link key={href} href={href}>{label}</Link>)}
    </nav>
  );
}

/** The homepage footer: wordmark, page links, copyright and social links. */
export function Footer() {
  const { identity, socials } = useSite();
  return (
    <footer className="container footer">
      <div className="wordmark">{identity.wordmark}</div>
      <PageLinks />
      <div className="footer-bar">
        <span>© {year} {[identity.name, identity.location].filter(Boolean).join(' · ')}</span>
        <div className="footer-links">
          <SocialLinks socials={socials} rss />
        </div>
      </div>
    </footer>
  );
}

/** The slim footer on inner pages. */
export function SimpleFooter() {
  const { identity, socials } = useSite();
  return (
    <footer className="footer-simple">
      <div className="container">
        <PageLinks />
        <div className="footer-simple-inner">
          <span>© {year} {identity.name}</span>
          <div className="footer-links"><SocialLinks socials={socials} rss /></div>
        </div>
      </div>
    </footer>
  );
}
