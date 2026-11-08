import { useRef, useState } from 'react';
import { http } from '../lib/http.js';

/** Show a toast from anywhere in the admin (the layout listens). */
export const toast = (message) => window.dispatchEvent(new CustomEvent('admin:toast', { detail: message }));

export function PageHead({ kicker, title, back, children }) {
  return (
    <div className="page-head">
      <div className="page-head-copy">
        {back}
        {kicker && <span className="page-kicker">{kicker}</span>}
        <h1 className="page-title">{title}</h1>
      </div>
      {children && <div className="a-actions">{children}</div>}
    </div>
  );
}

export function FieldError({ error }) {
  return error ? <span className="field-error" role="alert">{error}</span> : null;
}

/** Label + input (or textarea/select via `as`) + validation error. */
export function Field({ label, error, as = 'input', hint, className = '', children, ...props }) {
  const Tag = as;
  return (
    <label className={`a-field ${className}`}>
      {label}
      {as === 'select'
        ? <select className="a-input" aria-invalid={Boolean(error)} {...props}>{children}</select>
        : <Tag className="a-input" aria-invalid={Boolean(error)} {...props} />}
      {hint && <span className="a-hint">{hint}</span>}
      <FieldError error={error} />
    </label>
  );
}

export function Check({ label, ...props }) {
  return <label className="a-check"><input type="checkbox" {...props} />{label}</label>;
}

/** A file button that uploads straight away and hands back { path, url }. */
export function UploadButton({ label, accept = 'image/png,image/jpeg,image/webp,image/gif', url, alt, onUploaded, className = 'a-btn a-outline' }) {
  const input = useRef(null);
  const [busy, setBusy] = useState(false);

  const change = async (e) => {
    const file = e.target.files?.[0];
    e.target.value = '';
    if (!file) return;
    setBusy(true);
    try {
      onUploaded(await http.upload(file, { url, alt }));
    } catch (err) {
      toast(err.message);
    } finally {
      setBusy(false);
    }
  };

  return (
    <>
      <button type="button" className={className} onClick={() => input.current?.click()} disabled={busy}>{busy ? 'Uploading...' : label}</button>
      <input ref={input} type="file" accept={accept} hidden onChange={change} />
    </>
  );
}

/** A destructive button that asks first. */
export function ConfirmButton({ message, onConfirm, children, className = 'a-btn a-ghost', ...props }) {
  return <button type="button" className={className} onClick={() => window.confirm(message) && onConfirm()} {...props}>{children}</button>;
}

/** Editable list of short strings (tech stack, contact options, toolkit items). Enter adds. */
export function TagInput({ value, onChange, placeholder = 'Type and press Enter...', label }) {
  const [draft, setDraft] = useState('');
  const add = () => {
    const v = draft.trim();
    if (v && !value.includes(v)) onChange([...value, v]);
    setDraft('');
  };
  return (
    <div className="tag-box">
      {value.map((t) => (
        <span key={t}>{t}<button type="button" aria-label={`Remove ${t}`} onClick={() => onChange(value.filter((x) => x !== t))}>×</button></span>
      ))}
      <input
        value={draft}
        onChange={(e) => setDraft(e.target.value)}
        onKeyDown={(e) => { if (e.key === 'Enter' || e.key === ',') { e.preventDefault(); add(); } }}
        onBlur={add}
        placeholder={placeholder}
        aria-label={label || placeholder}
      />
    </div>
  );
}
