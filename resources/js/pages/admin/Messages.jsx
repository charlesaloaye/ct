import { useEffect, useRef, useState } from 'react';
import { Link, router, useForm } from '@inertiajs/react';
import { ConfirmButton, FieldError, PageHead } from '../../admin/ui.jsx';

const when = (iso) => new Date(iso).toLocaleString('en-GB', { dateStyle: 'medium', timeStyle: 'short' });

export default function Messages({ messages, openId, filters }) {
  const open = messages.find((m) => m.id === openId);
  const [query, setQuery] = useState(filters.q);
  const first = useRef(true);
  const reply = useForm({ reply: '' });
  const base = filters.archived ? { archived: 1 } : {};

  useEffect(() => {
    if (first.current) { first.current = false; return undefined; }
    const t = setTimeout(() => router.get('/admin/messages', { ...base, ...(query.trim() ? { q: query.trim() } : {}) }, { preserveState: true, replace: true }), 300);
    return () => clearTimeout(t);
  }, [query]); // eslint-disable-line react-hooks/exhaustive-deps

  const select = (id) => router.get('/admin/messages', { ...base, ...(filters.q ? { q: filters.q } : {}), open: id }, { preserveState: true, preserveScroll: true, replace: true });

  const send = (e) => {
    e.preventDefault();
    reply.post(`/admin/messages/${open.id}/reply`, { preserveScroll: true, onSuccess: () => reply.reset() });
  };

  return (
    <>
      <PageHead kicker="Inbox" title={filters.archived ? 'Archived messages' : 'Messages'}>
        <Link href={filters.archived ? '/admin/messages' : '/admin/messages?archived=1'} className="a-btn a-outline">{filters.archived ? '← Back to inbox' : 'View archive'}</Link>
      </PageHead>

      <div className="filters">
        <label className="a-field" style={{ flex: '1 1 280px' }}>Search
          <input type="search" className="a-input white" placeholder="Search by name, email or message" value={query} onChange={(e) => setQuery(e.target.value)} />
        </label>
      </div>

      {messages.length === 0 ? (
        <div className="panel"><p style={{ margin: 0 }}>{filters.q ? 'No messages match that search.' : filters.archived ? 'Nothing archived.' : 'Inbox zero. New enquiries from the contact form will show up here.'}</p></div>
      ) : open && (
        <div className="a-row">
          <div className="msg-list">
            {messages.map((m) => (
              <button key={m.id} type="button" className={`msg-item${m.id === open.id ? ' on' : ''}`} onClick={() => select(m.id)}>
                <span className="top"><b>{m.read ? m.name : `● ${m.name}`}</b><span className="type">{m.repliedAt ? 'Replied' : m.type}</span></span>
                <span className="pv">{m.preview}</span>
              </button>
            ))}
          </div>
          <article className="panel lg msg-detail">
            <div className="who">
              <div style={{ display: 'flex', flexDirection: 'column', gap: 4 }}>
                <h2>{open.name}</h2>
                <a className="email" href={`mailto:${open.email}`}>{open.email}</a>
              </div>
              <span className="green-pill">{open.type}</span>
            </div>
            <p style={{ whiteSpace: 'pre-line' }}>{open.body}</p>
            {open.repliedAt && (
              <div className="reply-sent">
                <span className="sample-note">You replied {when(open.repliedAt)}</span>
                <p style={{ whiteSpace: 'pre-line', margin: 0 }}>{open.replyBody}</p>
              </div>
            )}
            <form onSubmit={send} style={{ display: 'flex', flexDirection: 'column', gap: 12 }}>
              <label className="a-field">{open.repliedAt ? 'Send another reply' : 'Reply'}
                <textarea className="a-input" rows={5} placeholder={`Write to ${open.name.split(' ')[0]}... It is emailed from the site, and their answer comes to your contact email.`} value={reply.data.reply} onChange={(e) => reply.setData('reply', e.target.value)} />
                <FieldError error={reply.errors.reply} />
              </label>
              <div className="a-actions">
                <button type="submit" className="a-btn a-dark" disabled={reply.processing}>{reply.processing ? 'Sending...' : 'Send reply'}</button>
                {open.archived
                  ? <button type="button" className="a-btn a-ghost" onClick={() => router.patch(`/admin/messages/${open.id}/restore`)}>Move to inbox</button>
                  : <button type="button" className="a-btn a-ghost" onClick={() => router.patch(`/admin/messages/${open.id}/archive`)}>Archive</button>}
                <ConfirmButton message={`Delete the message from ${open.name}? This cannot be undone.`} onConfirm={() => router.delete(`/admin/messages/${open.id}`)}>Delete</ConfirmButton>
              </div>
            </form>
            <span className="sample-note">Received {when(open.createdAt)}{open.need ? ` · ${open.need}` : ''}</span>
          </article>
        </div>
      )}
    </>
  );
}
