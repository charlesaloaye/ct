import { Link, useForm } from '@inertiajs/react';
import AuthShell from '../../../admin/AuthShell.jsx';
import { FieldError } from '../../../admin/ui.jsx';

export default function TwoFactorChallenge() {
  const form = useForm({ code: '' });

  const submit = (e) => {
    e.preventDefault();
    form.post('/admin/two-factor-challenge', { onError: () => form.reset('code') });
  };

  return (
    <AuthShell title="Two-factor sign-in">
      <form className="login-form" onSubmit={submit}>
        <div style={{ display: 'flex', flexDirection: 'column', gap: 8, marginBottom: 8 }}>
          <h2>Enter your code</h2>
          <p style={{ margin: 0, color: '#5C5F5A', fontSize: 16 }}>Open your authenticator app and enter the 6-digit code. Lost your phone? Use one of your recovery codes.</p>
        </div>
        <label className="field" style={{ fontWeight: 600 }}>Authentication code
          <input inputMode="numeric" autoComplete="one-time-code" placeholder="123 456 or a recovery code" value={form.data.code} onChange={(e) => form.setData('code', e.target.value)} required autoFocus />
          <FieldError error={form.errors.code} />
        </label>
        <button type="submit" className="btn btn-submit" style={{ padding: 17, fontWeight: 700 }} disabled={form.processing}>{form.processing ? 'Checking...' : 'Sign in'}</button>
        <Link href="/admin/login" style={{ textAlign: 'center' }}>← Use a different account</Link>
      </form>
    </AuthShell>
  );
}
