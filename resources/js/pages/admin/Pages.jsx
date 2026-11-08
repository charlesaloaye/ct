import { useForm } from '@inertiajs/react';
import { Field, PageHead, TagInput, UploadButton, toast } from '../../admin/ui.jsx';

// Every block of copy on the public pages. [field, label, { textarea, hint }]
const sections = [
  { key: 'clients', title: 'Homepage · Clients strip', fields: [['label', 'Label before the names']] },
  { key: 'work', title: 'Homepage · Selected work', fields: [['eyebrow', 'Eyebrow'], ['heading', 'Heading', { textarea: true }], ['linkLabel', 'Link to all projects', { hint: '{count} becomes the number of projects' }]] },
  { key: 'products', title: 'Homepage · Products', fields: [['eyebrow', 'Eyebrow'], ['heading', 'Heading', { textarea: true }], ['note', 'Side note', { textarea: true }]] },
  { key: 'rescue', title: 'Homepage · Codebase rescue', fields: [['eyebrow', 'Eyebrow'], ['heading', 'Heading', { textarea: true }], ['body', 'Text', { textarea: true }], ['cta', 'Button (scrolls to the contact form)']] },
  { key: 'services', title: 'Homepage · Services', fields: [['eyebrow', 'Eyebrow'], ['heading', 'Heading'], ['lead', 'Lead', { textarea: true }]] },
  { key: 'process', title: 'Homepage · Process', fields: [['eyebrow', 'Eyebrow'], ['heading', 'Heading']] },
  { key: 'toolkit', title: 'Homepage · Toolkit', fields: [['eyebrow', 'Eyebrow'], ['heading', 'Heading']] },
  { key: 'experience', title: 'Homepage · Experience', fields: [['eyebrow', 'Eyebrow'], ['heading', 'Heading']] },
  { key: 'contact', title: 'Homepage · Contact form', fields: [['heading', 'Heading'], ['lead', 'Lead', { textarea: true }], ['needs', '"What do you need?" options', { tags: true, hint: 'Leave empty to hide the question' }], ['button', 'Send button'], ['success', 'Thank-you message', { textarea: true }], ['altPrompt', 'WhatsApp prompt'], ['altLink', 'WhatsApp link text']] },
  { key: 'workPage', title: 'Work page (/work)', fields: [['eyebrow', 'Eyebrow'], ['title', 'Title'], ['lead', 'Lead', { textarea: true }]] },
  { key: 'caseStudy', title: 'Case study pages', fields: [['ctaTitle', 'Closing call to action'], ['ctaButton', 'Button text', { hint: 'Links to the main button link from Site settings' }]] },
  { key: 'blog', title: 'Blog page (/blog)', fields: [['eyebrow', 'Eyebrow'], ['title', 'Title'], ['lead', 'Lead', { textarea: true }], ['searchHint', 'Search box hint'], ['newsletterTitle', 'Newsletter heading'], ['newsletterText', 'Newsletter text']] },
  { key: 'aboutPage', title: 'About page (/about)', fields: [['eyebrow', 'Eyebrow'], ['title', 'Title'], ['lead', 'Lead', { textarea: true }]] },
  { key: 'experiencePage', title: 'Experience page (/experience)', fields: [['eyebrow', 'Eyebrow'], ['title', 'Title'], ['lead', 'Lead', { textarea: true }]] },
  { key: 'venturesPage', title: 'Ventures page (/ventures)', fields: [['eyebrow', 'Eyebrow'], ['title', 'Title'], ['lead', 'Lead', { textarea: true }]] },
  { key: 'productsPage', title: 'Products page (/products)', fields: [['eyebrow', 'Eyebrow'], ['title', 'Title'], ['lead', 'Lead', { textarea: true }]] },
  { key: 'expertisePage', title: 'Expertise page (/expertise)', fields: [['eyebrow', 'Eyebrow'], ['title', 'Title'], ['lead', 'Lead', { textarea: true }]] },
  { key: 'mediaPage', title: 'Media & press page (/media)', fields: [['eyebrow', 'Eyebrow'], ['title', 'Title'], ['lead', 'Lead', { textarea: true }]] },
  { key: 'contactPage', title: 'Contact page (/contact)', fields: [['eyebrow', 'Eyebrow'], ['title', 'Title'], ['lead', 'Lead', { textarea: true }]] },
  { key: 'article', title: 'Article pages', fields: [['ctaTitle', 'Call-to-action heading'], ['ctaText', 'Call-to-action text', { textarea: true }], ['ctaButton', 'Button text']] },
];

const pages = [['home', 'Homepage'], ['about', 'About'], ['experience', 'Experience'], ['ventures', 'Ventures'], ['products', 'Products'], ['work', 'Work'], ['expertise', 'Expertise'], ['blog', 'Blog'], ['media', 'Media & press'], ['contact', 'Contact']];

export default function Pages({ about, sections: initialSections, seo, shareImageUrl }) {
  const form = useForm({ about, sections: initialSections, seo, shareImageUrl });
  const d = form.data;
  const e = form.errors;

  const setSection = (section, field, value) => form.setData('sections', { ...d.sections, [section]: { ...d.sections[section], [field]: value } });
  const setAbout = (field, value) => form.setData('about', { ...d.about, [field]: value });
  const setSeo = (page, field, value) => form.setData('seo', page ? { ...d.seo, [page]: { ...d.seo[page], [field]: value } } : { ...d.seo, [field]: value });

  const save = (ev) => {
    ev?.preventDefault();
    form.transform(({ shareImageUrl: _ignored, ...data }) => data);
    form.put('/admin/pages', { preserveScroll: true, onError: () => toast('Check the highlighted fields') });
  };

  return (
    <form onSubmit={save} className="settings-page">
      <PageHead kicker="Site" title="Pages & SEO">
        <button type="submit" className="a-btn a-dark" disabled={form.processing}>{form.processing ? 'Saving...' : 'Save changes'}</button>
      </PageHead>

      <section className="panel lg">
        <h2>About &amp; standard bios</h2>
        <span className="sample-note">Facts only. Use the same short and medium bio on LinkedIn, GitHub, X, YouTube and in press, so search engines see one consistent identity.</span>
        <Field as="textarea" label="About page biography" rows={12} value={d.about.body || ''} onChange={(ev) => setAbout('body', ev.target.value)} error={e['about.body']} hint="Paragraphs separated by a blank line. The first paragraph is shown larger." />
        <Field as="textarea" label="Short bio" rows={3} maxLength={300} value={d.about.bioShort || ''} onChange={(ev) => setAbout('bioShort', ev.target.value)} error={e['about.bioShort']} hint={`${(d.about.bioShort || '').length}/300 - for profile bios; also the description in search-engine data`} />
        <Field as="textarea" label="Medium bio" rows={6} maxLength={1200} value={d.about.bioMedium || ''} onChange={(ev) => setAbout('bioMedium', ev.target.value)} error={e['about.bioMedium']} hint="For event programmes, podcast intros and press" />
        <Field label="How to write your name" value={d.about.nameNote || ''} onChange={(ev) => setAbout('nameNote', ev.target.value)} error={e['about.nameNote']} hint="Shown in the press kit" />
      </section>

      <section className="panel lg">
        <h2>Search engines &amp; analytics</h2>
        <span className="sample-note">Paste only the code, not the whole tag. Nothing is added to the site until a value is set.</span>
        <div className="a-grid w240">
          <Field label="Google Search Console code" placeholder="from content=&quot;...&quot;" value={d.seo.googleVerification || ''} onChange={(ev) => setSeo(null, 'googleVerification', ev.target.value.trim())} error={e['seo.googleVerification']} hint="Search Console → Add property → HTML tag" />
          <Field label="Bing Webmaster code" value={d.seo.bingVerification || ''} onChange={(ev) => setSeo(null, 'bingVerification', ev.target.value.trim())} error={e['seo.bingVerification']} />
          <Field label="Google Analytics ID" placeholder="G-XXXXXXXXXX" value={d.seo.gaMeasurementId || ''} onChange={(ev) => setSeo(null, 'gaMeasurementId', ev.target.value.trim())} error={e['seo.gaMeasurementId']} hint="Loads on public pages only" />
        </div>
      </section>

      <section className="panel lg">
        <h2>Search &amp; sharing</h2>
        <span className="sample-note">How each page appears in Google and when shared. Article and case study titles come from their own editors.</span>
        {pages.map(([page, label]) => (
          <div key={page} className="seo-row">
            <b>{label}</b>
            <Field label="Title" maxLength={70} value={d.seo[page].title || ''} onChange={(ev) => setSeo(page, 'title', ev.target.value)} error={e[`seo.${page}.title`]} hint={`${(d.seo[page].title || '').length}/70`} />
            <Field as="textarea" label="Description" rows={2} maxLength={200} value={d.seo[page].description || ''} onChange={(ev) => setSeo(page, 'description', ev.target.value)} error={e[`seo.${page}.description`]} hint={`${(d.seo[page].description || '').length}/200`} />
          </div>
        ))}
        <Field label="Added after inner page titles" value={d.seo.titleSuffix || ''} onChange={(ev) => setSeo(null, 'titleSuffix', ev.target.value)} error={e['seo.titleSuffix']} hint='e.g. "- Charles Aloaye" gives "Blog - Charles Aloaye"' />
        <div className="cv-box">
          {d.shareImageUrl && <img src={d.shareImageUrl} alt="" className="cv-thumb" />}
          <span className="txt"><b>{d.seo.shareImagePath ? d.seo.shareImagePath.split('/').pop() : 'Using your portrait'}</b><span>Share image for links on WhatsApp, X, LinkedIn (1200×630 works best)</span></span>
          <UploadButton label={d.seo.shareImagePath ? 'Replace image' : 'Upload image'} onUploaded={(m) => form.setData({ ...d, seo: { ...d.seo, shareImagePath: m.path }, shareImageUrl: m.url })} />
          {d.seo.shareImagePath && <button type="button" className="a-btn a-ghost" onClick={() => form.setData({ ...d, seo: { ...d.seo, shareImagePath: null }, shareImageUrl: null })}>Remove</button>}
        </div>
      </section>

      {sections.map((s) => (
        <section key={s.key} className="panel lg">
          <h2>{s.title}</h2>
          {s.fields.map(([field, label, opts = {}]) => {
            const value = d.sections[s.key][field];
            const error = e[`sections.${s.key}.${field}`] || (opts.tags && Object.keys(e).find((k) => k.startsWith(`sections.${s.key}.${field}.`)) && 'Each option needs 1-60 characters');
            if (opts.tags) {
              return (
                <div key={field} className="a-field">
                  <span>{label}</span>
                  <TagInput value={value || []} onChange={(v) => setSection(s.key, field, v)} label={label} />
                  {opts.hint && <span className="a-hint">{opts.hint}</span>}
                  {error && <span className="field-error">{error}</span>}
                </div>
              );
            }
            return (
              <Field key={field} as={opts.textarea ? 'textarea' : 'input'} rows={opts.textarea ? 2 : undefined} label={label} value={value || ''} onChange={(ev) => setSection(s.key, field, ev.target.value)} error={error} hint={opts.hint} />
            );
          })}
        </section>
      ))}

      <div className="a-actions" style={{ justifyContent: 'flex-end' }}>
        <button type="submit" className="a-btn a-dark" disabled={form.processing}>{form.processing ? 'Saving...' : 'Save changes'}</button>
      </div>
    </form>
  );
}
