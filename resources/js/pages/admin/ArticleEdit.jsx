import { useRef, useState } from 'react';
import { Link, router, useForm, usePage } from '@inertiajs/react';
import { Sparkle } from '../../components/Icons.jsx';
import { Check, ConfirmButton, Field, FieldError, PageHead, TagInput, UploadButton, toast } from '../../admin/ui.jsx';
import { blocksToText, textToBlocks } from '../../lib/blocks.js';
import { http } from '../../lib/http.js';
import { slugify } from '../../lib/text.js';

const tones = ['Practical', 'Story-led', 'Technical', 'Beginner-friendly'];
const lengths = ['Medium (~1,200 words)', 'Short (~700 words)', 'Long (~2,000 words)'];
const quickActions = [
  { label: 'Continue writing', action: 'continue' },
  { label: 'Rewrite clearer', action: 'clearer' },
  { label: 'Add an example', action: 'example' },
  { label: 'Shorten', action: 'shorten' },
];

// "2026-10-26T09:00:00+00:00" -> "2026-10-26T10:00" in the browser's time zone (for datetime-local).
const toLocalInput = (iso) => {
  if (!iso) return '';
  const d = new Date(iso);
  return new Date(d.getTime() - d.getTimezoneOffset() * 60000).toISOString().slice(0, 16);
};

/** The paragraph the cursor is in (or the selected text) as [start, end] offsets in the body. */
function selectedRange(el, text) {
  const { selectionStart: s, selectionEnd: e } = el;
  if (e > s) return [s, e];
  const start = text.lastIndexOf('\n\n', Math.max(0, s - 1));
  const end = text.indexOf('\n\n', s);
  return [start === -1 ? 0 : start + 2, end === -1 ? text.length : end];
}

function Editor({ item, options }) {
  const titleSuffix = usePage().props.site.seo.titleSuffix || '';
  const isNew = !item;
  const categories = options.categories || [];
  const form = useForm({
    title: item?.title || '',
    categoryId: item?.categoryId ? String(item.categoryId) : String(categories[0]?.id || ''),
    excerpt: item?.excerpt || '',
    body: blocksToText(item?.body || []),
    seoTitle: item?.seoTitle || '',
    seoDescription: item?.seoDescription || '',
    publishedAt: toLocalInput(item?.publishedAt),
    notifySubscribers: item ? Boolean(item.notifySubscribers) : true,
    isFeatured: Boolean(item?.isFeatured),
    coverPath: item?.coverPath || null,
    coverUrl: item?.coverUrl || null,
    tags: item?.tags || [],
  });
  const f = form.data;
  const [ai, setAi] = useState({ prompt: item ? `Write about: ${item.title}. ${item.excerpt || ''}`.trim() : '', tone: 'Practical', length: lengths[0] });
  const [busy, setBusy] = useState('');
  const editor = useRef(null);
  const imageInput = useRef(null);
  const savedNote = isNew ? 'Not saved yet' : item.live ? 'Published' : `${item.statusLabel}`;

  const save = (status) => {
    if (!f.title.trim()) { toast('Add a title first'); return; }
    setBusy(status === 'published' ? 'publish' : 'save');
    form.transform((data) => ({
      title: data.title,
      categoryId: data.categoryId ? Number(data.categoryId) : null,
      excerpt: data.excerpt || null,
      body: textToBlocks(data.body, item?.body || []),
      seoTitle: data.seoTitle || null,
      seoDescription: data.seoDescription || null,
      publishedAt: data.publishedAt ? new Date(data.publishedAt).toISOString() : null,
      notifySubscribers: data.notifySubscribers,
      isFeatured: data.isFeatured,
      coverPath: data.coverPath,
      tags: data.tags.length ? data.tags : null,
      // "Save" keeps the current status (a new article starts as a draft); "Publish" goes live or schedules.
      ...(status ? { status } : isNew ? { status: 'draft' } : {}),
    }));
    const opts = { preserveScroll: true, onFinish: () => setBusy(''), onError: () => toast('Check the highlighted fields') };
    if (isNew) form.post('/admin/articles', opts);
    else form.put(`/admin/articles/${item.id}`, opts);
  };

  const unpublish = () => router.put(`/admin/articles/${item.id}`, { status: 'draft' }, { preserveScroll: true });

  // Toolbar: edits the plain text with the same markers the blog renders.
  const applyFormat = (kind) => {
    const el = editor.current;
    const body = f.body;
    const { selectionStart: s, selectionEnd: e } = el;
    const selected = body.slice(s, e);
    let insert;
    let cursor;
    if (kind === 'bold' || kind === 'italic') {
      const mark = kind === 'bold' ? '**' : '_';
      const inner = selected || (kind === 'bold' ? 'bold text' : 'italic text');
      insert = `${mark}${inner}${mark}`;
      cursor = [s + mark.length, s + mark.length + inner.length];
    } else if (kind === 'h2' || kind === 'quote') {
      const prefix = kind === 'h2' ? '## ' : '> ';
      const lineStart = body.lastIndexOf('\n', s - 1) + 1;
      const lineEnd = body.indexOf('\n', s) === -1 ? body.length : body.indexOf('\n', s);
      const line = body.slice(lineStart, lineEnd);
      const next = line.startsWith(prefix) ? line.slice(prefix.length) : `${prefix}${line.replace(/^(## |> )/, '')}`;
      form.setData('body', body.slice(0, lineStart) + next + body.slice(lineEnd));
      requestAnimationFrame(() => { el.focus(); el.setSelectionRange(lineStart + next.length, lineStart + next.length); });
      return;
    } else if (kind === 'code') {
      insert = `\n\n\`\`\`\n${selected || 'code here'}\n\`\`\`\n\n`;
      cursor = [s + 6, s + 6 + (selected || 'code here').length];
    } else if (kind === 'link') {
      const url = window.prompt('Link address (https://...)', 'https://');
      if (!url || url === 'https://') return;
      const text = selected || 'link text';
      insert = `[${text}](${url.trim()})`;
      cursor = [s + 1, s + 1 + text.length];
    }
    form.setData('body', body.slice(0, s) + insert + body.slice(e));
    requestAnimationFrame(() => { el.focus(); el.setSelectionRange(cursor[0], cursor[1]); });
  };

  const insertImage = async (e) => {
    const file = e.target.files?.[0];
    e.target.value = '';
    if (!file) return;
    const at = editor.current.selectionEnd;
    try {
      const media = await http.upload(file, { alt: f.title });
      const snippet = `\n\n![${f.title || 'Image'}](${media.url})\n\n`;
      form.setData('body', f.body.slice(0, at) + snippet + f.body.slice(at));
      toast('Image added');
    } catch (err) {
      toast(err.message);
    }
  };

  const runAi = async (key, call) => {
    setBusy(key);
    try {
      await call();
    } catch (err) {
      toast(err.message);
    } finally {
      setBusy('');
    }
  };

  const draft = () => {
    if (!ai.prompt.trim()) { toast('Say what the article should cover first'); return; }
    runAi('draft', async () => {
      const res = await http.post('/admin/ai/draft', { title: f.title, prompt: ai.prompt, tone: ai.tone, length: ai.length });
      const text = blocksToText(res.data.body);
      form.setData('body', f.body ? `${f.body}\n\n${text}` : text);
      toast('Draft added to the editor - review it, then save');
    });
  };

  const quick = ({ label, action }) => {
    const [start, end] = selectedRange(editor.current, f.body);
    const text = f.body.slice(start, end).trim();
    if (!text) { toast('Click into a paragraph (or select text) first'); return; }
    runAi(action, async () => {
      const res = await http.post('/admin/ai/rewrite', { text, action, context: f.body });
      const body = f.body;
      form.setData('body', action === 'continue'
        ? `${body.slice(0, end)}\n\n${res.data.text}${body.slice(end)}`
        : `${body.slice(0, start)}${res.data.text}${body.slice(end)}`);
      toast(`"${label}" applied`);
    });
  };

  const generateSeo = () => {
    if (!f.title.trim()) { toast('Add a title first'); return; }
    runAi('seo', async () => {
      const res = await http.post('/admin/ai/seo', { title: f.title, excerpt: f.excerpt, body: f.body });
      form.setData({ ...f, seoTitle: res.data.seoTitle, seoDescription: res.data.seoDescription });
      toast('SEO title and description generated');
    });
  };

  const errors = form.errors;

  return (
    <>
      <PageHead back={<Link href="/admin/articles" className="a-back">← Articles</Link>} title={isNew ? 'Write article' : 'Edit article'}>
        <span className="sample-note" style={{ fontSize: 13 }}>{form.isDirty ? 'Unsaved changes' : savedNote}</span>
        {item?.live && <a href={`/blog/${item.slug}`} target="_blank" rel="noopener" className="a-btn a-outline">View on site ↗</a>}
        <button type="button" className="a-btn a-outline" onClick={() => save()} disabled={Boolean(busy) || form.processing}>{busy === 'save' ? 'Saving...' : 'Save'}</button>
        <button type="button" className="a-btn a-dark" onClick={() => save('published')} disabled={Boolean(busy) || form.processing}>{busy === 'publish' ? 'Publishing...' : (item?.live ? 'Update' : 'Publish')}</button>
      </PageHead>

      <div className="a-row">
        <div className="editor-card">
          <div className="editor-cover" style={f.coverUrl ? { backgroundImage: `url(${f.coverUrl})`, backgroundSize: 'cover', backgroundPosition: 'center' } : undefined}>
            <UploadButton label={f.coverUrl ? 'Replace cover image' : '+ Add cover image'} alt={f.title} onUploaded={(m) => { form.setData({ ...f, coverPath: m.path, coverUrl: m.url }); toast('Cover added - save to keep it'); }} />
            {f.coverUrl && <button type="button" className="a-btn a-outline" onClick={() => form.setData({ ...f, coverPath: null, coverUrl: null })}>Remove</button>}
          </div>
          <div className="editor-body">
            <label className="a-field">Title
              <input className="title-input" value={f.title} onChange={(e) => form.setData('title', e.target.value)} placeholder="Article title" aria-invalid={Boolean(errors.title)} />
              <FieldError error={errors.title} />
            </label>
            <div className="toolbar" role="toolbar" aria-label="Formatting">
              <button type="button" aria-label="Bold" style={{ fontWeight: 800 }} onMouseDown={(e) => e.preventDefault()} onClick={() => applyFormat('bold')}>B</button>
              <button type="button" aria-label="Italic" style={{ fontStyle: 'italic' }} onMouseDown={(e) => e.preventDefault()} onClick={() => applyFormat('italic')}>I</button>
              <button type="button" aria-label="Heading" onMouseDown={(e) => e.preventDefault()} onClick={() => applyFormat('h2')}>H2</button>
              <button type="button" aria-label="Quote" onMouseDown={(e) => e.preventDefault()} onClick={() => applyFormat('quote')}>"</button>
              <button type="button" aria-label="Code block" style={{ fontFamily: 'var(--mono)', fontSize: 13 }} onMouseDown={(e) => e.preventDefault()} onClick={() => applyFormat('code')}>&lt;/&gt;</button>
              <button type="button" aria-label="Link" style={{ fontSize: 14, fontWeight: 600 }} onMouseDown={(e) => e.preventDefault()} onClick={() => applyFormat('link')}>Link</button>
              <button type="button" aria-label="Image" style={{ fontSize: 14, fontWeight: 600 }} onMouseDown={(e) => e.preventDefault()} onClick={() => imageInput.current?.click()}>Image</button>
              <input ref={imageInput} type="file" accept="image/png,image/jpeg,image/webp,image/gif" hidden onChange={insertImage} />
            </div>
            <textarea ref={editor} className="editor-area" aria-label="Article body" value={f.body} onChange={(e) => form.setData('body', e.target.value)} placeholder="Start writing, or ask the assistant for a draft... (## for a heading, > for a quote, ``` for code)" />
            <FieldError error={errors.body} />
          </div>
        </div>

        <aside className="side-col" style={{ flex: '1 1 320px' }}>
          <section className="ai-panel" aria-label="AI writing assistant">
            <div className="ai-head">
              <span className="ai-icon"><Sparkle size={18} /></span>
              <span className="txt"><b>AI writing assistant</b><span>Drafts in your voice - you edit and publish</span></span>
            </div>
            <label className="ai-field">What should the article cover?
              <textarea className="ai-input" rows={4} placeholder="The points to cover, examples from your work, and what you recommend." value={ai.prompt} onChange={(e) => setAi({ ...ai, prompt: e.target.value })} />
            </label>
            <div className="ai-field">
              <span>Tone</span>
              <div style={{ display: 'flex', flexWrap: 'wrap', gap: 6 }}>
                {tones.map((t) => (
                  <button key={t} type="button" className={`tone${t === ai.tone ? ' on' : ''}`} aria-pressed={t === ai.tone} onClick={() => setAi({ ...ai, tone: t })}>{t}</button>
                ))}
              </div>
            </div>
            <label className="ai-field">Length
              <select className="ai-input" value={ai.length} onChange={(e) => setAi({ ...ai, length: e.target.value })}>
                {lengths.map((l) => <option key={l}>{l}</option>)}
              </select>
            </label>
            <button type="button" className="a-btn a-green" style={{ padding: 15, fontSize: 15 }} onClick={draft} disabled={Boolean(busy)}>
              {busy === 'draft' ? 'Writing draft...' : 'Generate full draft'}
            </button>
            <div className="ai-actions">
              <span className="ai-field">On selected paragraph</span>
              <div style={{ display: 'flex', flexWrap: 'wrap', gap: 6 }}>
                {quickActions.map((q) => (
                  <button key={q.action} type="button" className="ai-chip" onMouseDown={(e) => e.preventDefault()} onClick={() => quick(q)} disabled={Boolean(busy)}>
                    {busy === q.action ? 'Working...' : q.label}
                  </button>
                ))}
              </div>
            </div>
            <Link href="/admin/integrations" className="ai-settings-link">AI provider &amp; voice settings →</Link>
          </section>

          <section className="panel">
            <h2>Post settings</h2>
            <Field as="select" label="Category" value={f.categoryId} onChange={(e) => form.setData('categoryId', e.target.value)} error={errors.categoryId} hint={<Link href="/admin/homepage#categories">Manage categories</Link>}>
              <option value="">No category</option>
              {categories.map((c) => <option key={c.id} value={c.id}>{c.name}</option>)}
            </Field>
            <Field as="textarea" label="Excerpt" rows={3} value={f.excerpt} onChange={(e) => form.setData('excerpt', e.target.value)} error={errors.excerpt} />
            <div className="a-field">
              <span>Tags</span>
              <TagInput value={f.tags} onChange={(v) => form.setData('tags', v)} label="Add tag" />
            </div>
            <div className="panel-head">
              <span className="a-field">SEO title &amp; description</span>
              <button type="button" className="text-link" style={{ fontSize: 13 }} onClick={generateSeo} disabled={Boolean(busy)}>{busy === 'seo' ? 'Generating...' : 'Generate with AI'}</button>
            </div>
            <input className="a-input" aria-label="SEO title" maxLength={60} placeholder="SEO title (60 characters)" value={f.seoTitle} onChange={(e) => form.setData('seoTitle', e.target.value)} />
            <FieldError error={errors.seoTitle} />
            <textarea className="a-input" aria-label="SEO description" rows={3} maxLength={155} placeholder="Meta description (155 characters)" value={f.seoDescription} onChange={(e) => form.setData('seoDescription', e.target.value)} />
            <FieldError error={errors.seoDescription} />
            <div className="serp serp-inline" aria-label="Search preview">
              <span className="url">{window.location.host} › blog › {item?.slug || slugify(f.title) || 'new-article'}</span>
              <span className="t">{(f.seoTitle || f.title || 'Article title').slice(0, 60)}{(f.seoTitle || f.title).length > 60 ? '…' : ''} {titleSuffix}</span>
              <span className="d">{(f.seoDescription || f.excerpt || 'Add an excerpt or SEO description so Google shows a summary here.').slice(0, 158)}</span>
              <span className="a-hint">SEO title {f.seoTitle.length}/60 · description {f.seoDescription.length}/155. Empty fields fall back to the title and excerpt.</span>
            </div>
            <Field label="Publish date" type="datetime-local" value={f.publishedAt} onChange={(e) => form.setData('publishedAt', e.target.value)} error={errors.publishedAt} hint="A future date schedules the article" />
            <Check label="Email subscribers when published" checked={f.notifySubscribers} onChange={(e) => form.setData('notifySubscribers', e.target.checked)} />
            <Check label="Feature at the top of the blog" checked={f.isFeatured} onChange={(e) => form.setData('isFeatured', e.target.checked)} />
            {!isNew && (
              <div className="a-actions">
                {item.live && <button type="button" className="a-btn a-outline" onClick={unpublish}>Unpublish</button>}
                <ConfirmButton className="a-btn a-danger" message={`Delete "${item.title}"? This cannot be undone.`} onConfirm={() => router.delete(`/admin/articles/${item.id}`)}>Delete article</ConfirmButton>
              </div>
            )}
          </section>
        </aside>
      </div>
    </>
  );
}

export default function ArticleEdit(props) {
  // Remount after each save so the editor shows what the server stored (slug, status, read time).
  return <Editor key={`${props.item?.id ?? 'new'}-${props.item?.updatedAt ?? ''}`} {...props} />;
}
