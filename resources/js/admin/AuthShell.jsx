import { useEffect, useState } from 'react';
import { Head, usePage } from '@inertiajs/react';
import Mark from '../components/Mark.jsx';

/** The two-column sign-in layout, shared by login, two-factor, forgot and reset password. */
export default function AuthShell({ title, children }) {
  const page = usePage();
  const { site } = page.props;
  const { flash } = page;
  const [note, setNote] = useState('');
  useEffect(() => { if (flash?.toast) setNote(flash.toast); }, [flash]);
  useEffect(() => { delete document.documentElement.dataset.theme; }, []);

  return (
    <div className="login">
      <Head><title>{title}</title></Head>
      <div className="login-hero">
        <span className="brand-row">
          <Mark identity={site.identity} className="mark" />
          <span style={{ fontWeight: 800, fontSize: 17 }}>{site.identity.wordmark} admin</span>
        </span>
        <div style={{ display: 'flex', flexDirection: 'column', gap: 16, maxWidth: 520 }}>
          <h1>Everything on your site, in one place.</h1>
          <p>Projects, products, articles, enquiries and settings.</p>
        </div>
        <span style={{ fontSize: 14, color: '#A3A6A0' }}>© {new Date().getFullYear()} {site.identity.name}</span>
      </div>
      <div className="login-form-wrap">
        {note && <div className="toast" role="status" onClick={() => setNote('')}>{note}</div>}
        {children}
      </div>
    </div>
  );
}
