import { Link, useForm } from '@inertiajs/react';
import AuthShell from '../../../admin/AuthShell.jsx';
import { FieldError } from '../../../admin/ui.jsx';

export default function ResetPassword({ token, email }) {
  const form = useForm({ token, email, password: '', password_confirmation: '' });
  const set = (k) => (e) => form.setData(k, e.target.value);

  const submit = (e) => {
    e.preventDefault();
    form.post('/admin/reset-password', { onFinish: () => form.reset('password', 'password_confirmation') });
  };

  return (
    <AuthShell title="Choose a new password">
      <form className="login-form" onSubmit={submit}>
        <div style={{ display: 'flex', flexDirection: 'column', gap: 8, marginBottom: 8 }}>
          <h2>Choose a new password</h2>
          <p style={{ margin: 0, color: '#5C5F5A', fontSize: 16 }}>At least 12 characters, with letters and numbers.</p>
        </div>
        <label className="field" style={{ fontWeight: 600 }}>Email
          <input type="email" autoComplete="username" value={form.data.email} onChange={set('email')} required />
          <FieldError error={form.errors.email} />
        </label>
        <label className="field" style={{ fontWeight: 600 }}>New password
          <input type="password" autoComplete="new-password" minLength={12} value={form.data.password} onChange={set('password')} required />
          <FieldError error={form.errors.password} />
        </label>
        <label className="field" style={{ fontWeight: 600 }}>Confirm new password
          <input type="password" autoComplete="new-password" minLength={12} value={form.data.password_confirmation} onChange={set('password_confirmation')} required />
        </label>
        <button type="submit" className="btn btn-submit" style={{ padding: 17, fontWeight: 700 }} disabled={form.processing}>
          {form.processing ? 'Saving...' : 'Save new password'}
        </button>
        <Link href="/admin/login" style={{ textAlign: 'center' }}>← Back to sign in</Link>
      </form>
    </AuthShell>
  );
}
