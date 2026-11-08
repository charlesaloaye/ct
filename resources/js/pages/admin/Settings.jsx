import { router, useForm } from '@inertiajs/react';
import { Check, Field, FieldError, PageHead, TagInput, UploadButton, toast } from '../../admin/ui.jsx';

const accents = [
  { label: 'Green', value: '#3DDC97' },
  { label: 'Amber', value: '#F2B544' },
  { label: 'Blue', value: '#8AB4FF' },
  { label: 'Coral', value: '#FF8A5B' },
];
const themes = [{ label: 'Light', value: 'light' }, { label: 'Dark', value: 'dark' }, { label: 'Follow device', value: 'system' }];

export default function Settings({ settings, portraitUrl, logoUrl, cv }) {
  const form = useForm({ ...settings, portraitUrl, logoUrl });
  const d = form.data;
  const e = form.errors;
  const set = (group, key) => (ev) => form.setData(group, { ...d[group], [key]: ev.target.type === 'checkbox' ? ev.target.checked : ev.target.value });
  const err = (group, key) => e[`${group}.${key}`];
  const setSocial = (i, key, v) => form.setData('socials', d.socials.map((s, j) => (j === i ? { ...s, [key]: v } : s)));
  const moveSocial = (i, delta) => {
    const next = [...d.socials];
    [next[i], next[i + delta]] = [next[i + delta], next[i]];
    form.setData('socials', next);
  };

  const save = (ev) => {
    ev?.preventDefault();
    form.transform(({ portraitUrl: _p, logoUrl: _l, ...data }) => data);
    form.put('/admin/settings', { preserveScroll: true, onError: () => toast('Check the highlighted fields') });
  };

  const accentChoices = accents.some((a) => a.value.toLowerCase() === (d.appearance.accent || '').toLowerCase())
    ? accents : [...accents, { label: 'Custom', value: d.appearance.accent }];

  return (
    <form onSubmit={save} className="settings-page">
      <PageHead kicker="Site" title="Site settings">
        <a href="/" target="_blank" rel="noopener" className="a-btn a-outline">View site ↗</a>
        <button type="submit" className="a-btn a-dark" disabled={form.processing}>{form.processing ? 'Saving...' : 'Save changes'}</button>
      </PageHead>

      <section className="panel lg">
        <h2>Identity</h2>
        <div className="a-grid w240">
          <Field label="Public name" value={d.identity.publicName || ''} onChange={set('identity', 'publicName')} error={err('identity', 'publicName')} hint="The name search engines should know you by" />
          <Field label="Full name" value={d.identity.name || ''} onChange={set('identity', 'name')} error={err('identity', 'name')} hint="Footer, article author, emails" />
          <Field label="Name in the header" value={d.identity.shortName || ''} onChange={set('identity', 'shortName')} error={err('identity', 'shortName')} />
          <Field label="Role under the name" value={d.identity.role || ''} onChange={set('identity', 'role')} error={err('identity', 'role')} />
          <Field label="Author title on articles" value={d.identity.authorTitle || ''} onChange={set('identity', 'authorTitle')} error={err('identity', 'authorTitle')} />
          <Field label="Logo mark (2-4 letters)" maxLength={4} value={d.identity.mark || ''} onChange={set('identity', 'mark')} error={err('identity', 'mark')} />
          <Field label="Initials" maxLength={4} value={d.identity.initials || ''} onChange={set('identity', 'initials')} error={err('identity', 'initials')} />
          <Field label="Footer wordmark" value={d.identity.wordmark || ''} onChange={set('identity', 'wordmark')} error={err('identity', 'wordmark')} />
          <Field label="Footer location" value={d.identity.location || ''} onChange={set('identity', 'location')} error={err('identity', 'location')} />
        </div>
        <div className="a-field">
          <span>Also known as</span>
          <TagInput value={d.identity.alternateNames || []} onChange={(v) => form.setData('identity', { ...d.identity, alternateNames: v })} label="Add a name" />
          <span className="a-hint">Handles and other names people search for, e.g. CharlesTechy. Used in search-engine data.</span>
        </div>
        <div className="a-field">
          <span>Job titles</span>
          <TagInput value={d.identity.jobTitles || []} onChange={(v) => form.setData('identity', { ...d.identity, jobTitles: v })} label="Add a title" />
          <span className="a-hint">Most important first. Shown on the About page and in search-engine data.</span>
        </div>
              <div className="cv-box">
          {d.logoUrl ? <img src={d.logoUrl} alt="" className="cv-thumb" /> : <span className="cv-thumb logo-letters">{d.identity.mark}</span>}
          <span className="txt"><b>{d.identity.logoPath ? 'Logo image' : 'No logo image'}</b><span>Shown in the header, phone menu, admin and browser tab. Without one, the letters above are used. A square image works best.</span></span>
          <UploadButton label={d.identity.logoPath ? 'Replace logo' : 'Upload logo'} alt={d.identity.name} onUploaded={(m) => { form.setData({ ...d, identity: { ...d.identity, logoPath: m.path }, logoUrl: m.url }); toast('Logo uploaded - save to publish it'); }} />
          {d.identity.logoPath && <button type="button" className="a-btn a-ghost" onClick={() => form.setData({ ...d, identity: { ...d.identity, logoPath: null }, logoUrl: null })}>Remove</button>}
        </div>
      </section>

      <section className="panel lg">
        <h2>Hero</h2>
        <Field label="Headline" value={d.hero.headline || ''} onChange={set('hero', 'headline')} error={err('hero', 'headline')} hint="Wrap words in *asterisks* to highlight them; otherwise the last two words are highlighted." />
        <Field as="textarea" label="Intro" rows={3} value={d.hero.intro || ''} onChange={set('hero', 'intro')} error={err('hero', 'intro')} />
        <div className="a-grid w240">
          <Field label="Main button label" value={d.hero.primaryLabel || ''} onChange={set('hero', 'primaryLabel')} error={err('hero', 'primaryLabel')} />
          <Field label="Main button link" placeholder="Empty = your WhatsApp link" value={d.hero.primaryUrl || ''} onChange={set('hero', 'primaryUrl')} error={err('hero', 'primaryUrl')} hint="https://, mailto: or a page like /#contact" />
          <Field label="CV button label" value={d.hero.cvLabel || ''} onChange={set('hero', 'cvLabel')} error={err('hero', 'cvLabel')} />
        </div>
        <div className="a-grid w240">
          <Field label='"Last shipped" card' value={d.hero.shippedTitle || ''} onChange={set('hero', 'shippedTitle')} error={err('hero', 'shippedTitle')} hint="Leave empty to hide the card" />
          <Field label="Card note" value={d.hero.shippedNote || ''} onChange={set('hero', 'shippedNote')} error={err('hero', 'shippedNote')} />
          <Field label="Card link" type="url" placeholder="https://" value={d.hero.shippedUrl || ''} onChange={set('hero', 'shippedUrl')} error={err('hero', 'shippedUrl')} />
        </div>
        <div className="cv-box">
          {d.portraitUrl && <img src={d.portraitUrl} alt="" className="cv-thumb" />}
          <span className="txt"><b>{d.hero.portraitPath ? d.hero.portraitPath.split('/').pop() : 'No portrait yet'}</b><span>Portrait beside the hero headline (also the default share image)</span></span>
          <UploadButton label={d.hero.portraitPath ? 'Replace photo' : 'Upload photo'} alt={d.identity.name} onUploaded={(m) => { form.setData({ ...d, hero: { ...d.hero, portraitPath: m.path }, portraitUrl: m.url }); toast('Portrait uploaded - save to publish it'); }} />
        </div>
        <Field label="Portrait description (for screen readers)" value={d.hero.portraitAlt || ''} onChange={set('hero', 'portraitAlt')} error={err('hero', 'portraitAlt')} />
      </section>

      <section className="panel lg">
        <div className="panel-head">
          <h2>Availability</h2>
          <Check label="Show the badge" checked={Boolean(d.availability.enabled)} onChange={set('availability', 'enabled')} />
        </div>
        <Field label="Badge on the hero" value={d.availability.text || ''} onChange={set('availability', 'text')} error={err('availability', 'text')} />
        <Field label="Short version in the menu" value={d.availability.shortText || ''} onChange={set('availability', 'shortText')} error={err('availability', 'shortText')} />
      </section>

      <section className="panel lg">
        <h2>Contact &amp; CV</h2>
        <div className="a-grid w240">
          <Field label="Email" type="email" value={d.contact.email || ''} onChange={set('contact', 'email')} error={err('contact', 'email')} />
          <Field label="WhatsApp number" value={d.contact.phone || ''} onChange={set('contact', 'phone')} error={err('contact', 'phone')} hint="Private: never shown on the site. Builds the WhatsApp link." />
          <Field label="WhatsApp link" type="url" placeholder="Empty = built from the number" value={d.contact.whatsappUrl || ''} onChange={set('contact', 'whatsappUrl')} error={err('contact', 'whatsappUrl')} />
          <Field label="Based in" value={d.contact.location || ''} onChange={set('contact', 'location')} error={err('contact', 'location')} />
        </div>
        <div className="cv-box">
          <span className="txt"><b>{cv.name || 'No CV uploaded'}</b><span>{cv.url ? <a href={cv.url} target="_blank" rel="noopener">Open the current CV ↗</a> : 'The "Download CV" button appears once a PDF is uploaded'}</span></span>
          <UploadButton label={cv.name ? 'Replace PDF' : 'Upload PDF'} url="/admin/cv" accept="application/pdf" onUploaded={() => { router.reload({ only: ['cv'] }); toast('CV replaced - it is live now'); }} />
        </div>
        <Field label="Download file name" placeholder="your-name-cv.pdf" value={d.contact.cvDownloadName || ''} onChange={set('contact', 'cvDownloadName')} error={err('contact', 'cvDownloadName')} />
      </section>

      <section className="panel lg">
        <h2>Social links</h2>
        <span className="sample-note">Shown as brand icons in the footer and the mobile menu, in this order. The icon comes from the label or address; a network without one shows its label.</span>
        {d.socials.map((s, i) => (
          <div key={i} className="row-editor">
            <input className="a-input" style={{ flex: 0.6 }} aria-label={`Link ${i + 1} label`} placeholder="Label (e.g. GitHub)" value={s.label} onChange={(ev) => setSocial(i, 'label', ev.target.value)} />
            <input className="a-input" style={{ flex: 2 }} aria-label={`Link ${i + 1} address`} placeholder="https://" value={s.href} onChange={(ev) => setSocial(i, 'href', ev.target.value)} />
            <button type="button" className="small-ghost" aria-label="Move up" onClick={() => moveSocial(i, -1)} disabled={i === 0}>↑</button>
            <button type="button" className="small-ghost" aria-label="Move down" onClick={() => moveSocial(i, 1)} disabled={i === d.socials.length - 1}>↓</button>
            <button type="button" className="small-ghost" onClick={() => form.setData('socials', d.socials.filter((_, j) => j !== i))}>Remove</button>
            <FieldError error={e[`socials.${i}.label`] || e[`socials.${i}.href`]} />
          </div>
        ))}
        <button type="button" className="a-btn a-outline" style={{ alignSelf: 'flex-start' }} onClick={() => form.setData('socials', [...d.socials, { label: '', href: '' }])}>+ Add link</button>
      </section>

      <section className="panel lg">
        <h2>Appearance</h2>
        <div className="a-grid w240">
          <Field as="select" label="Default theme (visitors can switch)" value={d.appearance.theme} onChange={set('appearance', 'theme')} error={err('appearance', 'theme')}>
            {themes.map((t) => <option key={t.value} value={t.value}>{t.label}</option>)}
          </Field>
          <Field as="select" label="Accent colour" value={d.appearance.accent} onChange={set('appearance', 'accent')} error={err('appearance', 'accent')}>
            {accentChoices.map((a) => <option key={a.value} value={a.value}>{a.label} {a.value}</option>)}
          </Field>
        </div>
      </section>

      <div className="a-actions" style={{ justifyContent: 'flex-end' }}>
        <button type="submit" className="a-btn a-dark" disabled={form.processing}>{form.processing ? 'Saving...' : 'Save changes'}</button>
      </div>
    </form>
  );
}
