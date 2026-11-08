import { useEffect, useState } from 'react';
import { Head, usePage } from '@inertiajs/react';

/** The shared site data (identity, contact, socials, availability, appearance). */
export function useSite() {
  return usePage().props.site;
}

/** <title> and meta description from the page's `meta` prop (also rendered on the server for crawlers). */
export function Seo() {
  const { meta } = usePage().props;
  if (!meta) return null;
  return (
    <Head>
      <title>{meta.title}</title>
      {meta.description && <meta head-key="description" name="description" content={meta.description} />}
    </Head>
  );
}

/** Shows flash.toast (e.g. "please try again" after an expired form) for a few seconds. */
export function FlashToast() {
  const { flash } = usePage();
  const [msg, setMsg] = useState('');
  useEffect(() => { if (flash?.toast) setMsg(flash.toast); }, [flash]);
  useEffect(() => {
    const on = (e) => setMsg(e.detail);
    window.addEventListener('site:toast', on);
    return () => window.removeEventListener('site:toast', on);
  }, []);
  useEffect(() => {
    if (!msg) return undefined;
    const t = setTimeout(() => setMsg(''), 4000);
    return () => clearTimeout(t);
  }, [msg]);
  return msg ? <div className="toast" role="status">{msg}</div> : null;
}

/** Outbound links open in a new tab; site links stay in it. */
export function linkProps(href) {
  return /^https?:\/\//i.test(href || '') ? { target: '_blank', rel: 'noopener noreferrer' } : {};
}
