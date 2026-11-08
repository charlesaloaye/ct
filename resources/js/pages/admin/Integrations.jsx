import { useState } from 'react';
import { router, useForm } from '@inertiajs/react';
import { Field, PageHead, toast } from '../../admin/ui.jsx';

const providers = [
  { value: 'openai', label: 'OpenAI', keyField: 'openaiKey', modelField: 'openaiModel', keyHint: 'platform.openai.com → API keys', modelHint: 'The model id from your OpenAI account, e.g. a GPT model you have access to' },
  { value: 'anthropic', label: 'Anthropic (Claude)', keyField: 'anthropicKey', modelField: 'anthropicModel', keyHint: 'console.anthropic.com → API keys', modelHint: 'e.g. claude-opus-5-5' },
];

function SecretField({ label, field, data, setData, clear, setClear, available, hint }) {
  const isSet = data[`${field}Set`];
  const cleared = clear.includes(field);
  const status = cleared ? 'Will be removed when you save' : isSet ? `Saved (${data[`${field}Hint`]}). Type a new one to replace it.` : available ? 'Using the key from the server .env file' : 'Not set';
  return (
    <div className="a-field">
      <Field label={label} type="password" autoComplete="new-password" placeholder={isSet ? '••••••••••••' : 'Paste the key'} value={data[field] || ''} onChange={(e) => setData(field, e.target.value)} hint={hint} />
      <span className="a-hint">{status}{isSet && !cleared && <> · <button type="button" className="link-button underline" onClick={() => setClear([...clear, field])}>Remove</button></>}</span>
    </div>
  );
}

function AiPanel({ ai }) {
  const form = useForm({
    provider: ai.provider || 'openai',
    openaiModel: ai.openaiModel || '',
    anthropicModel: ai.anthropicModel || '',
    maxTokens: ai.maxTokens || 16000,
    voice: ai.voice || '',
    openaiKey: '',
    anthropicKey: '',
    clear: [],
  });
  const [testing, setTesting] = useState(false);
  const d = form.data;
  const p = providers.find((x) => x.value === d.provider) || providers[0];

  const save = (e) => {
    e.preventDefault();
    form.put('/admin/integrations/ai', { preserveScroll: true, onSuccess: () => form.reset('openaiKey', 'anthropicKey', 'clear'), onError: () => toast('Check the highlighted fields') });
  };
  const test = () => {
    if (form.isDirty) { toast('Save your changes first, then test them.'); return; }
    setTesting(true);
    router.post('/admin/integrations/ai/test', {}, { preserveScroll: true, onFinish: () => setTesting(false) });
  };

  return (
    <form onSubmit={save} className="panel lg">
      <div className="panel-head">
        <h2>AI writing assistant</h2>
        <span className="sample-note">Up to {ai.requestsPerHour} requests an hour. Keys stay on the server.</span>
      </div>
      <div className="a-grid w240">
        <Field as="select" label="Provider" value={d.provider} onChange={(e) => form.setData('provider', e.target.value)} error={form.errors.provider}>
          {providers.map((x) => <option key={x.value} value={x.value}>{x.label}</option>)}
        </Field>
        <Field label="Model" value={d[p.modelField]} onChange={(e) => form.setData(p.modelField, e.target.value)} error={form.errors[p.modelField]} hint={p.modelHint} />
        <Field label="Max output tokens" type="number" min={1000} max={128000} step={1000} value={d.maxTokens} onChange={(e) => form.setData('maxTokens', e.target.value)} error={form.errors.maxTokens} hint="A 2,000-word draft needs about 4,000; reasoning models need more" />
      </div>
      <SecretField label={`${p.label} API key`} field={p.keyField} data={{ ...ai, [p.keyField]: d[p.keyField] }} setData={form.setData} clear={d.clear} setClear={(v) => form.setData('clear', v)} available={ai[`${p.keyField}Available`]} hint={p.keyHint} />
      {form.errors[p.keyField] && <span className="field-error">{form.errors[p.keyField]}</span>}
      <Field as="textarea" label="Writing voice" rows={8} placeholder={ai.defaultVoice} value={d.voice} onChange={(e) => form.setData('voice', e.target.value)} error={form.errors.voice} hint="How drafts should sound. Leave empty to use the built-in voice shown above." />
      <div className="a-actions">
        <button type="submit" className="a-btn a-dark" disabled={form.processing}>{form.processing ? 'Saving...' : 'Save AI settings'}</button>
        <button type="button" className="a-btn a-outline" onClick={test} disabled={testing}>{testing ? 'Testing...' : 'Test connection'}</button>
      </div>
    </form>
  );
}

function MailPanel({ mail, adminEmail }) {
  const form = useForm({
    mailer: mail.mailer || '',
    host: mail.host || '',
    port: mail.port || '',
    username: mail.username || '',
    password: '',
    encryption: mail.encryption || 'tls',
    fromAddress: mail.fromAddress || '',
    fromName: mail.fromName || '',
    contactRecipient: mail.contactRecipient || '',
    clear: [],
  });
  const [testing, setTesting] = useState(false);
  const d = form.data;
  const smtp = d.mailer === 'smtp';

  const save = (e) => {
    e.preventDefault();
    form.transform((data) => ({ ...data, mailer: data.mailer || null, port: data.port ? Number(data.port) : null }));
    form.put('/admin/integrations/mail', { preserveScroll: true, onSuccess: () => form.reset('password', 'clear'), onError: () => toast('Check the highlighted fields') });
  };
  const test = () => {
    if (form.isDirty) { toast('Save your changes first, then test them.'); return; }
    setTesting(true);
    router.post('/admin/integrations/mail/test', {}, { preserveScroll: true, onFinish: () => setTesting(false) });
  };

  return (
    <form onSubmit={save} className="panel lg">
      <div className="panel-head">
        <h2>Email</h2>
        <span className={`status ${['log', 'array'].includes(mail.effectiveMailer) ? 'Draft' : 'Published'}`}>{['log', 'array'].includes(mail.effectiveMailer) ? 'Not sending (log only)' : `Sending via ${mail.effectiveMailer}`}</span>
      </div>
      <span className="sample-note">Used for contact form alerts, your replies, newsletter emails and password resets. Any SMTP provider works: Zoho, Brevo, Google Workspace, Postmark, Amazon SES...</span>
      <div className="a-grid w240">
        <Field as="select" label="Sending method" value={d.mailer} onChange={(e) => form.setData('mailer', e.target.value)} error={form.errors.mailer}>
          <option value="">Use the server .env ({mail.effectiveMailer})</option>
          <option value="smtp">SMTP</option>
          <option value="log">Log only (testing)</option>
        </Field>
        {smtp && (
          <>
            <Field label="SMTP host" placeholder="smtp.zoho.com" value={d.host} onChange={(e) => form.setData('host', e.target.value)} error={form.errors.host} />
            <Field label="Port" type="number" placeholder="587" value={d.port} onChange={(e) => form.setData('port', e.target.value)} error={form.errors.port} hint="587 with TLS, or 465 with SSL" />
            <Field as="select" label="Encryption" value={d.encryption} onChange={(e) => form.setData('encryption', e.target.value)} error={form.errors.encryption}>
              <option value="tls">TLS (STARTTLS, port 587)</option>
              <option value="ssl">SSL (port 465)</option>
              <option value="none">None</option>
            </Field>
            <Field label="Username" autoComplete="off" value={d.username} onChange={(e) => form.setData('username', e.target.value)} error={form.errors.username} />
          </>
        )}
      </div>
      {smtp && <SecretField label="Password" field="password" data={{ ...mail, password: d.password }} setData={form.setData} clear={d.clear} setClear={(v) => form.setData('clear', v)} hint="For Gmail / Google Workspace, use an app password" />}
      <div className="a-grid w240">
        <Field label="From address" type="email" placeholder={mail.effectiveFrom} value={d.fromAddress} onChange={(e) => form.setData('fromAddress', e.target.value)} error={form.errors.fromAddress} hint="Must be allowed by your provider" />
        <Field label="From name" value={d.fromName} onChange={(e) => form.setData('fromName', e.target.value)} error={form.errors.fromName} />
        <Field label="Send enquiries to" type="email" placeholder={mail.effectiveRecipient} value={d.contactRecipient} onChange={(e) => form.setData('contactRecipient', e.target.value)} error={form.errors.contactRecipient} hint="Empty = your contact email" />
      </div>
      <div className="a-actions">
        <button type="submit" className="a-btn a-dark" disabled={form.processing}>{form.processing ? 'Saving...' : 'Save email settings'}</button>
        <button type="button" className="a-btn a-outline" onClick={test} disabled={testing}>{testing ? 'Sending...' : `Send a test to ${adminEmail}`}</button>
      </div>
    </form>
  );
}

export default function Integrations({ ai, mail, adminEmail }) {
  return (
    <div className="settings-page">
      <PageHead kicker="Site" title="Integrations" />
      {/* Remount after a save so the "Saved (•••• 1234)" hints update. */}
      <AiPanel key={JSON.stringify([ai.provider, ai.openaiKeyHint, ai.anthropicKeyHint, ai.openaiModel, ai.anthropicModel, ai.maxTokens, ai.voice])} ai={ai} />
      <MailPanel key={JSON.stringify([mail.mailer, mail.host, mail.port, mail.username, mail.passwordHint, mail.fromAddress, mail.fromName, mail.contactRecipient, mail.encryption])} mail={mail} adminEmail={adminEmail} />
    </div>
  );
}
