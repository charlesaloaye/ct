import { useForm, usePage } from '@inertiajs/react';
import { linkProps, useSite } from './Site.jsx';

/** The enquiry form with the contact details beside it (homepage and /contact). */
export default function ContactForm({ copy, headingLevel = 2 }) {
  const Heading = `h${headingLevel}`;
  const { contact } = useSite();
  const { flash } = usePage();
  const needs = copy.needs || [];
  const form = useForm({ name: '', email: '', need: needs[0] || '', message: '', website: '' });
  const set = (k) => (e) => form.setData(k, e.target.value);

  const submit = (e) => {
    e.preventDefault();
    form.post('/contact', { preserveScroll: true, onSuccess: () => form.reset() });
  };

  return (
    <section id="contact" className="container contact-section">
      <div className="contact">
        <div className="contact-copy">
          {copy.heading && <Heading>{copy.heading}</Heading>}
          {copy.lead && <p className="contact-lead">{copy.lead}</p>}
          <div className="contact-rows">
            {contact.email && <a href={`mailto:${contact.email}`} className="contact-row"><span>Email</span><span>{contact.email}</span></a>}
            {contact.whatsapp && <a href={contact.whatsapp} {...linkProps(contact.whatsapp)} className="contact-row"><span>WhatsApp</span><span>Message me</span></a>}
            {contact.location && <div className="contact-row"><span>Based in</span><span>{contact.location}</span></div>}
          </div>
        </div>
        <form className="contact-form" onSubmit={submit} noValidate>
          <div className="field-row">
            <label className="field">Your name
              <input className="input" type="text" autoComplete="name" placeholder="Your full name" value={form.data.name} onChange={set('name')} required aria-invalid={Boolean(form.errors.name)} />
              {form.errors.name && <span className="field-error">{form.errors.name}</span>}
            </label>
            <label className="field">Email
              <input className="input" type="email" autoComplete="email" placeholder="you@company.com" value={form.data.email} onChange={set('email')} required aria-invalid={Boolean(form.errors.email)} />
              {form.errors.email && <span className="field-error">{form.errors.email}</span>}
            </label>
          </div>
          {needs.length > 0 && (
            <label className="field">What do you need?
              <select className="input" value={form.data.need} onChange={set('need')}>
                {needs.map((n) => <option key={n}>{n}</option>)}
              </select>
              {form.errors.need && <span className="field-error">{form.errors.need}</span>}
            </label>
          )}
          {/* Honeypot: hidden from people, bots fill it in and get dropped server-side. */}
          <label className="hp-field" aria-hidden="true">Website
            <input type="text" name="website" tabIndex={-1} autoComplete="off" value={form.data.website} onChange={set('website')} />
          </label>
          <label className="field">Tell me about it
            <textarea className="input" rows={5} placeholder="What you're building, where it's stuck, and when it needs to be live." value={form.data.message} onChange={set('message')} required aria-invalid={Boolean(form.errors.message)} />
            {form.errors.message && <span className="field-error">{form.errors.message}</span>}
          </label>
          {flash?.contact && <div className="form-success" role="status">{flash.contact}</div>}
          <button type="submit" className="btn btn-submit" disabled={form.processing}>{form.processing ? 'Sending...' : (copy.button || 'Send message')}</button>
          {contact.whatsapp && copy.altLink && (
            <span className="form-note">{copy.altPrompt} <a href={contact.whatsapp} {...linkProps(contact.whatsapp)}>{copy.altLink}</a></span>
          )}
        </form>
      </div>
    </section>
  );
}
