import { useEffect, useRef, useState } from 'react';
import { Link, router } from '@inertiajs/react';
import { collections } from '../../admin/config.js';
import { PageHead } from '../../admin/ui.jsx';
import { Sparkle } from '../../components/Icons.jsx';

/** The table for one content type: search, status filter, reorder, hide/show. */
export default function Collection({ type, rows, total, filters, statuses }) {
  const col = collections[type];
  const [query, setQuery] = useState(filters.q);
  const first = useRef(true);

  // Search as you type (debounced); the server filters.
  useEffect(() => {
    if (first.current) { first.current = false; return undefined; }
    const t = setTimeout(() => router.get(`/admin/${type}`, clean({ q: query.trim(), status: filters.status }), { preserveState: true, preserveScroll: true, replace: true }), 300);
    return () => clearTimeout(t);
  }, [query]); // eslint-disable-line react-hooks/exhaustive-deps

  const clean = (params) => Object.fromEntries(Object.entries(params).filter(([, v]) => v && v !== 'All'));
  const setStatus = (status) => router.get(`/admin/${type}`, clean({ q: query.trim(), status }), { preserveState: true, preserveScroll: true, replace: true });

  const toggle = (row) => router.patch(`/admin/${type}/${row.id}/visibility`, {}, { preserveScroll: true });

  const move = (index, delta) => {
    const next = [...rows];
    [next[index], next[index + delta]] = [next[index + delta], next[index]];
    router.patch(`/admin/${type}/reorder`, { ids: next.map((r) => r.id) }, { preserveScroll: true });
  };

  const canReorder = !filters.q && filters.status === 'All' && rows.length === total;

  return (
    <>
      <PageHead kicker="Content" title={col.title}>
        {col.ai && <Link href="/admin/articles/create" className="a-btn a-green"><Sparkle />Draft with AI</Link>}
        <Link href={`/admin/${type}/create`} className="a-btn a-dark">+ {col.addLabel}</Link>
      </PageHead>

      <div className="filters">
        <label className="a-field" style={{ flex: '1 1 280px' }}>Search
          <input type="search" className="a-input white" placeholder={`Search ${total} ${col.title.toLowerCase()}`} value={query} onChange={(e) => setQuery(e.target.value)} />
        </label>
        <label className="a-field" style={{ flex: '0 1 200px' }}>Status
          <select className="a-input white" value={filters.status} onChange={(e) => setStatus(e.target.value)}>
            <option>All</option>
            {statuses.map((s) => <option key={s}>{s}</option>)}
            {type === 'articles' && <option>Featured</option>}
          </select>
        </label>
      </div>

      <div className="table-wrap">
        <table className="a-table">
          <thead>
            <tr><th>Title</th><th>{col.metaLabel}</th><th>Status</th><th className="act">Actions</th></tr>
          </thead>
          <tbody>
            {rows.map((r, i) => (
              <tr key={r.id}>
                <td className="t"><Link href={`/admin/${type}/${r.id}/edit`} className="row-link">{r.title}</Link></td>
                <td className="m">{r.meta || '-'}</td>
                <td><span className={`status ${r.status}`}>{r.status}</span></td>
                <td className="act">
                  {canReorder && (
                    <>
                      <button type="button" className="small-ghost" aria-label={`Move ${r.title} up`} onClick={() => move(i, -1)} disabled={i === 0}>↑</button>
                      <button type="button" className="small-ghost" aria-label={`Move ${r.title} down`} onClick={() => move(i, 1)} disabled={i === rows.length - 1}>↓</button>
                    </>
                  )}
                  <Link href={`/admin/${type}/${r.id}/edit`} className="small-btn">Edit</Link>
                  <button type="button" className="small-ghost" onClick={() => toggle(r)}>{r.status === 'Hidden' ? 'Show' : 'Hide'}</button>
                </td>
              </tr>
            ))}
            {rows.length === 0 && (
              <tr><td colSpan={4} className="m" style={{ padding: 28 }}>{total === 0 ? `No ${col.title.toLowerCase()} yet. Use "${col.addLabel}" to create the first one.` : 'Nothing matches those filters.'}</td></tr>
            )}
          </tbody>
        </table>
      </div>
    </>
  );
}
