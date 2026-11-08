import { Link, router, useForm } from '@inertiajs/react';
import { collections } from '../../admin/config.js';
import { Check, ConfirmButton, Field, FieldError, PageHead, TagInput, UploadButton, toast } from '../../admin/ui.jsx';
import { domainOf, slugify } from '../../lib/text.js';

// Editor fields for the simpler types. `half` fields share a two-column grid.
const fieldSets = {
  ventures: {
    titleKey: 'name',
    fields: [
      { key: 'name', label: 'Company name' },
      { key: 'role', label: 'My role', half: true, hint: 'e.g. Founder & CTO' },
      { key: 'url', label: 'Website', type: 'url', half: true },
      { key: 'years', label: 'Years', half: true, hint: 'Optional, e.g. 2023 - Now' },
      { key: 'isFounder', label: 'I founded this company', type: 'check', half: true },
      { key: 'description', label: 'What the company does', type: 'textarea', rows: 3 },
    ],
  },
  writings: {
    titleKey: 'title',
    fields: [
      { key: 'title', label: 'Article title' },
      { key: 'publication', label: 'Published on', half: true, hint: 'e.g. Medium, DEV, Hashnode' },
      { key: 'url', label: 'Link to the article', type: 'url', half: true },
      { key: 'publishedOn', label: 'Date', type: 'date', half: true },
      { key: 'summary', label: 'One-line summary (optional)', type: 'textarea', rows: 2 },
    ],
  },
  appearances: {
    titleKey: 'title',
    fields: [
      { key: 'title', label: 'Headline or episode title' },
      { key: 'outlet', label: 'Publication, podcast or event', half: true },
      { key: 'kind', label: 'Type', type: 'kind', half: true },
      { key: 'url', label: 'Link to the coverage', type: 'url', half: true, hint: 'Required, so readers can verify it' },
      { key: 'publishedOn', label: 'Date', type: 'date', half: true },
      { key: 'summary', label: 'One-line summary', type: 'textarea', rows: 2 },
    ],
  },
  products: {
    titleKey: 'name',
    fields: [
      { key: 'name', label: 'Name' },
      { key: 'ventureId', label: 'Company (shown as "By ...")', type: 'venture', half: true },
      { key: 'url', label: 'Live URL', type: 'url', half: true },
      { key: 'category', label: 'Category', half: true, hint: 'e.g. Commerce · SaaS' },
      { key: 'role', label: 'My role', half: true },
      { key: 'mono', label: 'Monogram (2 letters)', half: true, maxLength: 4 },
      { key: 'proof', label: 'Proof line', hint: 'One short fact, e.g. "Launched on Product Hunt"' },
      { key: 'description', label: 'Card summary', type: 'textarea', rows: 3 },
    ],
  },
  services: {
    titleKey: 'title',
    fields: [
      { key: 'title', label: 'Title' },
      { key: 'number', label: 'Number', half: true, hint: 'Shown above the title, e.g. 01' },
      { key: 'description', label: 'Description', type: 'textarea', rows: 3 },
    ],
  },
  experiences: {
    titleKey: 'org',
    fields: [
      { key: 'org', label: 'Organisation' },
      { key: 'role', label: 'My role', half: true },
      { key: 'years', label: 'Years', half: true, hint: 'e.g. 2022 - Now' },
      { key: 'place', label: 'Place', half: true },
      { key: 'description', label: 'What I did', type: 'textarea', rows: 3 },
    ],
  },
  testimonials: {
    titleKey: 'clientName',
    fields: [
      { key: 'clientName', label: 'Client name' },
      { key: 'clientRole', label: 'Role', half: true },
      { key: 'company', label: 'Company', half: true },
      { key: 'projectId', label: 'Project (shows on its case study)', type: 'project', half: true },
      { key: 'quote', label: 'Quote', type: 'textarea', rows: 4 },
    ],
  },
};

const projectFields = ['title', 'slug', 'url', 'tag', 'role', 'timeline', 'since', 'description', 'showOnMobile', 'hasCaseStudy',
  'csTitle', 'csCategory', 'csSummary', 'client', 'challenge', 'built', 'outcome', 'features', 'results', 'techStack', 'screenshotPath', 'gallery'];

const withDefaults = (type, item, nextSortOrder) => {
  const base = { statusLabel: 'Draft', sortOrder: nextSortOrder, ...item };
  // Dropdowns must start with the value they show, or the first save sends nothing.
  if (type === 'appearances') return { kind: 'interview', ...base };
  if (type === 'writings') return { publication: 'Medium', ...base, statusLabel: item?.statusLabel || 'Published' };
  if (type === 'ventures') return { isFounder: false, ...base };
  if (type !== 'projects') return base;
  return {
    showOnMobile: true, hasCaseStudy: false, ...base,
    features: item?.features || [], results: item?.results || [], techStack: item?.techStack || [],
    gallery: item?.gallery || [], galleryUrls: item?.galleryUrls || [],
  };
};

// Form state -> request body (camelCase keys the server validates).
function toPayload(type, f) {
  const nul = (v) => (v === '' || v === undefined ? null : v);
  const body = { status: (f.statusLabel || 'Draft').toLowerCase(), sortOrder: Number(f.sortOrder) || 0 };
  if (type === 'projects') {
    projectFields.forEach((k) => { body[k] = nul(f[k]); });
    body.slug = f.slug ? slugify(f.slug) : null;
    body.showOnMobile = f.showOnMobile !== false;
    body.hasCaseStudy = Boolean(f.hasCaseStudy);
    body.features = (f.features || []).filter((x) => x.t?.trim());
    body.results = (f.results || []).filter((x) => x.v?.trim() && x.l?.trim());
    return body;
  }
  fieldSets[type].fields.forEach(({ key }) => { body[key] = nul(f[key]); });
  if (type === 'products') {
    body.domain = domainOf(f.url) || null;
    body.ventureId = f.ventureId ? Number(f.ventureId) : null;
  }
  if (type === 'products' || type === 'ventures') body.logoPath = f.logoPath || null;
  if (type === 'ventures') body.isFounder = Boolean(f.isFounder);
  if (type === 'testimonials') {
    body.projectId = f.projectId ? Number(f.projectId) : null;
    body.avatarPath = f.avatarPath || null;
  }
  return body;
}

/** Where the item shows on the public site, if it is visible there. */
function liveUrl(type, item) {
  if (!item || !['Published', 'Featured'].includes(item.statusLabel)) return null;
  if (type === 'projects') return item.hasCaseStudy ? `/work/${item.slug}` : '/work';
  if (type === 'ventures') return `/ventures#${item.slug}`;
  return { products: '/products', services: '/expertise', experiences: '/experience', testimonials: '/', appearances: '/media', writings: '/blog#elsewhere' }[type];
}

function SimpleField({ field, form, projects, ventures = [] }) {
  const value = form.data[field.key] ?? '';
  const onChange = (e) => form.setData(field.key, e.target.value);
  const error = form.errors[field.key];
  if (field.type === 'textarea') return <Field as="textarea" label={field.label} rows={field.rows || 3} value={value} onChange={onChange} error={error} hint={field.hint} />;
  if (field.type === 'check') return <Check label={field.label} checked={Boolean(form.data[field.key])} onChange={(e) => form.setData(field.key, e.target.checked)} />;
  if (field.type === 'date') return <Field label={field.label} type="date" value={(value || '').slice(0, 10)} onChange={onChange} error={error} hint={field.hint} />;
  if (field.type === 'kind') {
    return (
      <Field as="select" label={field.label} value={value || 'interview'} onChange={onChange} error={error}>
        {['interview', 'podcast', 'talk', 'article', 'feature'].map((k) => <option key={k} value={k}>{k[0].toUpperCase() + k.slice(1)}</option>)}
      </Field>
    );
  }
  if (field.type === 'venture') {
    return (
      <Field as="select" label={field.label} value={value} onChange={onChange} error={error}>
        <option value="">None</option>
        {ventures.map((v) => <option key={v.id} value={v.id}>{v.name}</option>)}
      </Field>
    );
  }
  if (field.type === 'project') {
    return (
      <Field as="select" label={field.label} value={value} onChange={onChange} error={error}>
        <option value="">None</option>
        {projects.map((p) => <option key={p.id} value={p.id}>{p.title}</option>)}
      </Field>
    );
  }
  return <Field label={field.label} type={field.type || 'text'} value={value} onChange={onChange} error={error} hint={field.hint} maxLength={field.maxLength} placeholder={field.type === 'url' ? 'https://' : undefined} />;
}

/** Repeating rows (case study features and results). */
function Rows({ label, rows, onChange, columns, add, errors, prefix }) {
  const update = (i, key, v) => onChange(rows.map((r, j) => (j === i ? { ...r, [key]: v } : r)));
  return (
    <div className="a-field">
      <span>{label}</span>
      {rows.map((r, i) => (
        <div key={i} className="row-editor">
          {columns.map((c) => (
            c.type === 'textarea'
              ? <textarea key={c.key} className="a-input" rows={2} placeholder={c.label} aria-label={`${c.label} ${i + 1}`} value={r[c.key] || ''} onChange={(e) => update(i, c.key, e.target.value)} style={{ flex: c.flex || 1 }} />
              : <input key={c.key} className="a-input" placeholder={c.label} aria-label={`${c.label} ${i + 1}`} value={r[c.key] || ''} onChange={(e) => update(i, c.key, e.target.value)} style={{ flex: c.flex || 1 }} />
          ))}
          <button type="button" className="small-ghost" aria-label={`Remove ${label} ${i + 1}`} onClick={() => onChange(rows.filter((_, j) => j !== i))}>Remove</button>
          {columns.map((c) => <FieldError key={c.key} error={errors[`${prefix}.${i}.${c.key}`]} />)}
        </div>
      ))}
      <button type="button" className="a-btn a-outline" style={{ alignSelf: 'flex-start' }} onClick={() => onChange([...rows, add(rows.length)])}>+ Add</button>
    </div>
  );
}

function Editor({ type, item, statuses, nextSortOrder, options }) {
  const col = collections[type];
  const isNew = !item;
  const isProject = type === 'projects';
  const config = fieldSets[type];
  const form = useForm(withDefaults(type, item, nextSortOrder));
  const f = form.data;
  const set = (k) => (e) => form.setData(k, e.target.type === 'checkbox' ? e.target.checked : e.target.value);
  const name = isProject ? f.title : f[config.titleKey];
  const single = col.single;
  const live = liveUrl(type, item);

  const save = (e) => {
    e?.preventDefault();
    form.transform((data) => toPayload(type, data));
    const opts = { preserveScroll: true, onError: () => toast('Check the highlighted fields') };
    if (isNew) form.post(`/admin/${type}`, opts);
    else form.put(`/admin/${type}/${item.id}`, opts);
  };

  const remove = () => router.delete(`/admin/${type}/${item.id}`);

  const thumb = (url) => (url ? { backgroundImage: `url(${url})`, backgroundSize: 'cover', backgroundPosition: 'center' } : undefined);
  const half = config ? config.fields.filter((x) => x.half) : [];
  const projectTags = options.tags || [];

  return (
    <form onSubmit={save}>
      <PageHead back={<Link href={`/admin/${type}`} className="a-back">← {col.title}</Link>} title={isNew ? `New ${single}` : `Edit ${single}`}>
        {live && <a href={live} target="_blank" rel="noopener" className="a-btn a-outline">View on site ↗</a>}
        <button type="submit" className="a-btn a-dark" disabled={form.processing}>{form.processing ? 'Saving...' : `Save ${single}`}</button>
      </PageHead>

      <div className="a-row">
        <div className="main-col">
          <section className="panel lg">
            <h2>Details</h2>
            {isProject ? (
              <>
                <Field label="Name" value={f.title || ''} onChange={set('title')} error={form.errors.title} />
                <div className="a-grid">
                  <Field label="Live URL" type="url" placeholder="https://" value={f.url || ''} onChange={set('url')} error={form.errors.url} />
                  <Field label="Category" list="project-tags" value={f.tag || ''} onChange={set('tag')} error={form.errors.tag} hint="Pick one or type a new one" />
                  <datalist id="project-tags">{projectTags.map((t) => <option key={t} value={t} />)}</datalist>
                  <Field label="My role" value={f.role || ''} onChange={set('role')} error={form.errors.role} />
                  <Field label="Since (featured card)" value={f.since || ''} onChange={set('since')} error={form.errors.since} hint="e.g. 2022, ongoing" />
                </div>
                <Field as="textarea" label="Card summary" rows={2} value={f.description || ''} onChange={set('description')} error={form.errors.description} />
                <div className="a-field">
                  <span>Tech stack</span>
                  <TagInput value={f.techStack} onChange={(v) => form.setData('techStack', v)} label="Add technology" />
                </div>
              </>
            ) : (
              <>
                {config.fields.filter((x) => !x.half && x.type !== 'textarea').map((x) => <SimpleField key={x.key} field={x} form={form} projects={options.projects || []} ventures={options.ventures || []} />)}
                {half.length > 0 && <div className="a-grid">{half.map((x) => <SimpleField key={x.key} field={x} form={form} projects={options.projects || []} ventures={options.ventures || []} />)}</div>}
                {config.fields.filter((x) => x.type === 'textarea').map((x) => <SimpleField key={x.key} field={x} form={form} projects={[]} />)}
              </>
            )}
          </section>

          {isProject && (
            <section className="panel lg">
              <div className="panel-head">
                <h2>Case study</h2>
                <Check label="Publish a case study page" checked={Boolean(f.hasCaseStudy)} onChange={set('hasCaseStudy')} />
              </div>
              {f.hasCaseStudy ? (
                <>
                  <Field label="Case study title" value={f.csTitle || ''} onChange={set('csTitle')} error={form.errors.csTitle} hint="Leave empty to use the project name" />
                  <div className="a-grid">
                    <Field label="Category line" value={f.csCategory || ''} onChange={set('csCategory')} error={form.errors.csCategory} hint="e.g. Finance · Public sector" />
                    <Field label="Client" value={f.client || ''} onChange={set('client')} error={form.errors.client} />
                    <Field label="Timeline" value={f.timeline || ''} onChange={set('timeline')} error={form.errors.timeline} hint="e.g. 2022 - present" />
                  </div>
                  <Field as="textarea" label="Summary" rows={2} value={f.csSummary || ''} onChange={set('csSummary')} error={form.errors.csSummary} />
                  <Field as="textarea" label="The challenge" rows={4} value={f.challenge || ''} onChange={set('challenge')} error={form.errors.challenge} />
                  <Field as="textarea" label="What I built" rows={4} value={f.built || ''} onChange={set('built')} error={form.errors.built} />
                  <Rows label="Features" rows={f.features} onChange={(v) => form.setData('features', v)} errors={form.errors} prefix="features"
                    columns={[{ key: 'n', label: 'No.', flex: 0.3 }, { key: 't', label: 'Feature' }, { key: 'd', label: 'Detail', type: 'textarea', flex: 2 }]}
                    add={(n) => ({ n: String(n + 1).padStart(2, '0'), t: '', d: '' })} />
                  <Field as="textarea" label="The outcome" rows={3} value={f.outcome || ''} onChange={set('outcome')} error={form.errors.outcome} />
                  <Rows label="Results (real figures only)" rows={f.results} onChange={(v) => form.setData('results', v)} errors={form.errors} prefix="results"
                    columns={[{ key: 'v', label: 'Figure', flex: 0.5 }, { key: 'l', label: 'What it measures', flex: 2 }]}
                    add={() => ({ v: '', l: '' })} />
                </>
              ) : (
                <span className="sample-note">Turn this on to give the project its own page at /work/{f.slug || slugify(f.title || '') || 'project-name'}.</span>
              )}
            </section>
          )}

          {(type === 'products' || type === 'ventures') && (
            <section className="panel lg">
              <h2>Logo</h2>
              <div className="cv-box">
                {f.logoUrl ? <img src={f.logoUrl} alt="" className="cv-thumb logo-thumb" /> : <span className="cv-thumb logo-letters">{(name || '?').slice(0, 2)}</span>}
                <span className="txt"><b>{f.logoPath ? 'Logo uploaded' : 'No logo yet'}</b><span>A square logo on a white or transparent background works best. JPG, PNG, WebP or GIF, up to 5 MB.</span></span>
                <UploadButton label={f.logoPath ? 'Replace logo' : 'Upload logo'} alt={name || ''} onUploaded={(m) => { form.setData({ ...f, logoPath: m.path, logoUrl: m.url }); toast('Logo added - save to keep it'); }} />
                {f.logoPath && <button type="button" className="a-btn a-ghost" onClick={() => form.setData({ ...f, logoPath: null, logoUrl: null })}>Remove</button>}
              </div>
            </section>
          )}

          {(isProject || type === 'testimonials') && (
            <section className="panel lg">
              <h2>Images</h2>
              <div className="thumbs">
                {isProject ? (
                  <>
                    <div style={thumb(f.screenshotUrl)}>
                      <span className="cover-tag">Cover</span>
                      {f.screenshotUrl && <button type="button" className="small-ghost" style={{ marginLeft: 'auto', alignSelf: 'flex-start' }} onClick={() => form.setData({ ...f, screenshotPath: null, screenshotUrl: null })}>Remove</button>}
                    </div>
                    {f.galleryUrls.map((url, i) => (
                      <div key={url} style={thumb(url)}>
                        <button type="button" className="small-ghost" style={{ marginLeft: 'auto', alignSelf: 'flex-start' }} onClick={() => form.setData({ ...f, gallery: f.gallery.filter((_, j) => j !== i), galleryUrls: f.galleryUrls.filter((_, j) => j !== i) })}>Remove</button>
                      </div>
                    ))}
                  </>
                ) : (
                  <div style={thumb(f.avatarUrl)}>
                    <span className="cover-tag">Photo</span>
                    {f.avatarUrl && <button type="button" className="small-ghost" style={{ marginLeft: 'auto', alignSelf: 'flex-start' }} onClick={() => form.setData({ ...f, avatarPath: null, avatarUrl: null })}>Remove</button>}
                  </div>
                )}
                <div className="upload">
                  <UploadButton
                    label={isProject ? (f.screenshotUrl ? '+ Add to gallery' : '+ Upload cover') : (f.avatarUrl ? 'Replace photo' : '+ Upload photo')}
                    className="upload-btn"
                    alt={name || ''}
                    onUploaded={(media) => {
                      if (!isProject) form.setData({ ...f, avatarPath: media.path, avatarUrl: media.url });
                      else if (!f.screenshotUrl) form.setData({ ...f, screenshotPath: media.path, screenshotUrl: media.url });
                      else form.setData({ ...f, gallery: [...f.gallery, media.path], galleryUrls: [...f.galleryUrls, media.url] });
                      toast('Image added - save to keep it');
                    }}
                  />
                </div>
              </div>
              <span className="sample-note">JPG, PNG, WebP or GIF up to 5 MB.{isProject ? ' The first image is the cover; the rest form the case study gallery.' : ''}</span>
            </section>
          )}
        </div>

        <aside className="side-col">
          <section className="panel">
            <h2>Publishing</h2>
            <Field as="select" label="Status" value={f.statusLabel} onChange={set('statusLabel')} error={form.errors.status}>
              {statuses.map((s) => <option key={s}>{s}</option>)}
            </Field>
            <Field label="Order on the site" type="number" min={0} value={f.sortOrder ?? 1} onChange={set('sortOrder')} error={form.errors.sortOrder} />
            {isProject && (
              <>
                <Field label="Web address" value={f.slug || ''} onChange={set('slug')} error={form.errors.slug} placeholder={slugify(f.title || '')} hint={item && item.statusLabel !== 'Draft' ? 'Fixed once published, so links keep working' : 'Leave empty to use the name'} disabled={Boolean(item && item.statusLabel !== 'Draft')} />
                <Check label="Show on the mobile homepage" checked={f.showOnMobile !== false} onChange={set('showOnMobile')} />
              </>
            )}
            {!isNew && (
              <ConfirmButton className="a-btn a-danger" message={`Delete "${name || single}"? This cannot be undone.`} onConfirm={remove}>Delete {single}</ConfirmButton>
            )}
          </section>
          {isProject && (
            <section className="panel serp">
              <h2 style={{ marginBottom: 4 }}>Search preview</h2>
              <span className="url">{window.location.host} › work › {f.slug || slugify(name || '') || 'new'}</span>
              <span className="t">{f.csTitle || name || `Untitled ${single}`}</span>
              <span className="d">{f.csSummary || f.description || domainOf(f.url)}</span>
            </section>
          )}
        </aside>
      </div>
    </form>
  );
}

export default function ContentEdit(props) {
  // Remount after each save so the form shows exactly what the server stored.
  return <Editor key={`${props.item?.id ?? 'new'}-${props.item?.updatedAt ?? ''}`} {...props} />;
}
