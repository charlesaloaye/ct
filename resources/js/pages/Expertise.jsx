import { Link } from '@inertiajs/react';
import PageShell from '../components/PageShell.jsx';

export default function Expertise({ copy, stack, services }) {
  return (
    <PageShell copy={copy}>
      {stack.length > 0 && (
        <section className="container section-md">
          <h2 className="h-sub" style={{ marginBottom: 24 }}>Technical stack</h2>
          <div className="expertise-grid">
            {stack.map((g) => (
              <div key={g.label} className="expertise-group">
                <h3>{g.label}</h3>
                <div className="chips">{g.items.map((i) => <span key={i} className="chip">{i}</span>)}</div>
              </div>
            ))}
          </div>
        </section>
      )}
      {services.length > 0 && (
        <section className="container section-md">
          <h2 className="h-sub" style={{ marginBottom: 8 }}>What I do</h2>
          <div className="split-main">
            {services.map((s) => (
              <div key={s.id} className="service"><span className="service-n">{s.n}</span><h3>{s.title}</h3>{s.desc && <p>{s.desc}</p>}</div>
            ))}
          </div>
        </section>
      )}
      <section className="container section-md"><Link href="/work" className="underline-link">See it in shipped work →</Link></section>
    </PageShell>
  );
}
