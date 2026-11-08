import { Link } from '@inertiajs/react';
import PageShell, { LogoTile } from '../components/PageShell.jsx';

export default function Experience({ copy, roles, ventures }) {
  return (
    <PageShell copy={copy}>
      {ventures.length > 0 && (
        <section className="container section-md">
          <h2 className="h-sub" style={{ marginBottom: 24 }}>Leadership</h2>
          <div className="venture-list compact">
            {ventures.map((v) => (
              <Link key={v.id} href={`/ventures#${v.slug}`} className="venture-row">
                <LogoTile url={v.logoUrl} name={v.name} size="sm" />
                <span className="venture-row-text"><b>{v.role}</b><span>{v.name}{v.years ? ` · ${v.years}` : ''}</span></span>
              </Link>
            ))}
          </div>
        </section>
      )}
      <section className="container section-md">
        <h2 className="h-sub" style={{ marginBottom: 8 }}>Roles</h2>
        {roles.length === 0 && <p className="empty">Roles will appear here.</p>}
        <ol className="timeline">
          {roles.map((r) => (
            <li key={r.id} className="role">
              <span className="role-years">{r.years}</span>
              <div className="role-body">
                <span className="role-org">{r.org}</span>
                <span className="role-desc">{[r.role, r.place].filter(Boolean).join(' · ')}</span>
                {r.desc && <span className="role-desc">{r.desc.charAt(0).toUpperCase() + r.desc.slice(1)}</span>}
              </div>
            </li>
          ))}
        </ol>
      </section>
    </PageShell>
  );
}
