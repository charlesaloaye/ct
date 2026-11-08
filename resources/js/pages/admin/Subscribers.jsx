import { useEffect, useRef, useState } from 'react';
import { Link, router } from '@inertiajs/react';
import { ConfirmButton, PageHead } from '../../admin/ui.jsx';

const statuses = [{ label: 'All', value: '' }, { label: 'Confirmed', value: 'confirmed' }, { label: 'Pending', value: 'pending' }];
const date = (iso) => (iso ? new Date(iso).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' }) : '-');

export default function Subscribers({ subscribers, pagination, confirmed, filters }) {
  const [query, setQuery] = useState(filters.q);
  const first = useRef(true);
  const params = (extra = {}) => Object.fromEntries(Object.entries({ q: query.trim(), status: filters.status, ...extra }).filter(([, v]) => v));

  useEffect(() => {
    if (first.current) { first.current = false; return undefined; }
    const t = setTimeout(() => router.get('/admin/subscribers', params(), { preserveState: true, replace: true }), 300);
    return () => clearTimeout(t);
  }, [query]); // eslint-disable-line react-hooks/exhaustive-deps

  return (
    <>
      <PageHead kicker="Newsletter" title="Subscribers">
        <span className="sample-note" style={{ fontSize: 13 }}>{confirmed} confirmed</span>
        <a className="a-btn a-dark" href="/admin/subscribers/export">Export CSV</a>
      </PageHead>

      <div className="filters">
        <label className="a-field" style={{ flex: '1 1 280px' }}>Search
          <input type="search" className="a-input white" placeholder={`Search ${pagination.total} subscribers`} value={query} onChange={(e) => setQuery(e.target.value)} />
        </label>
        <label className="a-field" style={{ flex: '0 1 200px' }}>Status
          <select className="a-input white" value={filters.status} onChange={(e) => router.get('/admin/subscribers', params({ status: e.target.value }), { preserveState: true, replace: true })}>
            {statuses.map((s) => <option key={s.value} value={s.value}>{s.label}</option>)}
          </select>
        </label>
      </div>

      <div className="table-wrap">
        <table className="a-table">
          <thead>
            <tr><th>Email</th><th>Signed up</th><th>Status</th><th className="act">Actions</th></tr>
          </thead>
          <tbody>
            {subscribers.map((s) => (
              <tr key={s.id}>
                <td className="t">{s.email}</td>
                <td className="m">{date(s.createdAt)}</td>
                <td><span className={`status ${s.confirmed ? 'Published' : 'Draft'}`}>{s.confirmed ? 'Confirmed' : 'Pending'}</span></td>
                <td className="act">
                  <ConfirmButton className="small-ghost" message={`Remove ${s.email} from the list?`} onConfirm={() => router.delete(`/admin/subscribers/${s.id}`, { preserveScroll: true })}>Remove</ConfirmButton>
                </td>
              </tr>
            ))}
            {subscribers.length === 0 && (
              <tr><td colSpan={4} className="m" style={{ padding: 28 }}>{filters.q || filters.status ? 'Nobody matches those filters.' : 'No subscribers yet - signups from the blog show up here.'}</td></tr>
            )}
          </tbody>
        </table>
      </div>

      {pagination.lastPage > 1 && (
        <div className="pager">
          {pagination.prev ? <Link href={pagination.prev} className="a-btn a-outline" preserveScroll>← Newer</Link> : <span />}
          <span className="sample-note">Page {pagination.page} of {pagination.lastPage}</span>
          {pagination.next ? <Link href={pagination.next} className="a-btn a-outline" preserveScroll>Older →</Link> : <span />}
        </div>
      )}
    </>
  );
}
