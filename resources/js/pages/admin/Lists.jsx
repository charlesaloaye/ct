import { useForm } from '@inertiajs/react';
import { FieldError, PageHead, TagInput, UploadButton, toast } from '../../admin/ui.jsx';

const lists = [
  { key: 'stats', title: 'Hero stats', note: 'The figures under the hero. Only real numbers.', columns: [['value', 'Figure', 0.5], ['label', 'Label', 2]], blank: { value: '', label: '', shortLabel: '' } },
  { key: 'clients', title: 'Clients strip', note: 'Shown after "Trusted by teams at". Upload a logo, or leave it empty to show the name.', columns: [['logoPath', 'Logo', 0, 'logo'], ['name', 'Name', 1]], blank: { name: '', logoPath: null, logoUrl: null } },
  { key: 'rescue', title: 'Codebase rescue steps', columns: [['number', 'No.', 0.3], ['title', 'Step', 1], ['description', 'Detail', 2]], blank: (n) => ({ number: String(n + 1).padStart(2, '0'), title: '', description: '' }) },
  { key: 'process', title: '"How we\'ll work" steps', columns: [['number', 'No.', 0.3], ['title', 'Step', 1], ['description', 'Detail', 2]], blank: (n) => ({ number: String(n + 1).padStart(2, '0'), title: '', description: '' }) },
  { key: 'stack', title: 'Toolkit', note: 'Groups of tools. Press Enter after each one.', columns: [['label', 'Group', 0.6], ['items', 'Tools', 2, 'tags']], blank: { label: '', items: [] } },
  { key: 'categories', title: 'Blog categories', note: 'Removing a category keeps its articles; they just lose the category.', columns: [['name', 'Name', 1], ['tint', 'Card colour', 0.4, 'color']], blank: { name: '', tint: '#EEECE6' } },
];

function ListEditor({ list, items, usage }) {
  const form = useForm({ items });
  const rows = form.data.items;
  const set = (i, key, v) => form.setData('items', rows.map((r, j) => (j === i ? { ...r, [key]: v } : r)));
  const move = (i, delta) => {
    const next = [...rows];
    [next[i], next[i + delta]] = [next[i + delta], next[i]];
    form.setData('items', next);
  };
  const add = () => form.setData('items', [...rows, typeof list.blank === 'function' ? list.blank(rows.length) : { ...list.blank }]);
  const save = () => form.put(`/admin/homepage/${list.key}`, { preserveScroll: true, onSuccess: () => form.setDefaults(), onError: () => toast('Check the highlighted rows') });

  return (
    <section className="panel lg" id={list.key}>
      <div className="panel-head">
        <h2>{list.title}</h2>
        <button type="button" className="a-btn a-dark" onClick={save} disabled={form.processing}>{form.processing ? 'Saving...' : form.isDirty ? 'Save changes' : 'Save'}</button>
      </div>
      {list.note && <span className="sample-note">{list.note}</span>}
      {rows.map((r, i) => (
        <div key={r.id ?? `new-${i}`} className="row-editor">
          {list.columns.map(([key, label, flex, kind]) => {
            if (kind === 'logo') {
              return (
                <span key={key} className="logo-cell">
                  {r.logoUrl && <img src={r.logoUrl} alt="" className="cv-thumb logo-thumb" />}
                  <UploadButton label={r.logoUrl ? 'Replace logo' : 'Upload logo'} alt={r.name} onUploaded={(m) => form.setData('items', rows.map((x, j) => (j === i ? { ...x, logoPath: m.path, logoUrl: m.url } : x)))} />
                  {r.logoUrl && <button type="button" className="small-ghost" onClick={() => form.setData('items', rows.map((x, j) => (j === i ? { ...x, logoPath: null, logoUrl: null } : x)))}>Clear</button>}
                </span>
              );
            }
            if (kind === 'tags') return <div key={key} style={{ flex }}><TagInput value={r[key] || []} onChange={(v) => set(i, key, v)} label={`${label} for row ${i + 1}`} /></div>;
            if (kind === 'color') return <input key={key} type="color" className="a-color" aria-label={`${label} ${i + 1}`} value={r[key] || '#EEECE6'} onChange={(e) => set(i, key, e.target.value)} />;
            return <input key={key} className="a-input" style={{ flex }} placeholder={label} aria-label={`${label} ${i + 1}`} value={r[key] || ''} onChange={(e) => set(i, key, e.target.value)} />;
          })}
          {list.key === 'categories' && r.id && <span className="sample-note">{usage?.[r.id] ?? 0} articles</span>}
          <button type="button" className="small-ghost" aria-label="Move up" onClick={() => move(i, -1)} disabled={i === 0}>↑</button>
          <button type="button" className="small-ghost" aria-label="Move down" onClick={() => move(i, 1)} disabled={i === rows.length - 1}>↓</button>
          <button type="button" className="small-ghost" onClick={() => form.setData('items', rows.filter((_, j) => j !== i))}>Remove</button>
          {list.columns.map(([key]) => <FieldError key={key} error={form.errors[`items.${i}.${key}`] || form.errors[`items.${i}.${key}.0`]} />)}
        </div>
      ))}
      {rows.length === 0 && <span className="sample-note">Empty - this block is hidden on the site.</span>}
      <button type="button" className="a-btn a-outline" style={{ alignSelf: 'flex-start' }} onClick={add}>+ Add</button>
    </section>
  );
}

export default function Lists({ lists: data }) {
  return (
    <div className="settings-page">
      <PageHead kicker="Site" title="Homepage lists" />
      {lists.map((l) => (
        // Remount after a save so new rows pick up their ids.
        <ListEditor key={`${l.key}-${JSON.stringify(data[l.key].map((r) => r.id))}`} list={l} items={data[l.key]} usage={data.categoryUsage} />
      ))}
    </div>
  );
}
