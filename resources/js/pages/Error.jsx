import { Head, Link } from '@inertiajs/react';
import SiteNav from '../components/SiteNav.jsx';
import { SimpleFooter } from '../components/Footers.jsx';

const copy = {
  403: ['403', "You don't have access to this page.", 'Sign in with an admin account, or head back to the site.'],
  404: ['404', "This page doesn't exist.", 'The link may be old, or the page may have moved.'],
  429: ['429', 'Too many requests.', 'Please wait a minute and try again.'],
  500: ['500', 'Something went wrong on our side.', 'It has been logged. Please try again in a moment.'],
  503: ['503', 'Back shortly.', 'The site is down for a quick update. Please check back in a few minutes.'],
};

export default function Error({ status }) {
  const [code, title, lead] = copy[status] || copy[500];
  return (
    <>
      <Head><title>{title}</title></Head>
      <SiteNav />
      <main className="container notfound">
        <span className="eyebrow">{code}</span>
        <h1 className="blog-title">{title}</h1>
        <p className="blog-lead">{lead}</p>
        <div className="btn-row">
          <Link href="/" className="btn btn-ink">Back to home</Link>
          <Link href="/blog" className="btn btn-outline">Read the blog</Link>
        </div>
      </main>
      <SimpleFooter />
    </>
  );
}
