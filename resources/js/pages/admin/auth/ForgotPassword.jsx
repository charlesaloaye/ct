import { Link, useForm, usePage } from '@inertiajs/react';
import AuthShell from '../../../admin/AuthShell.jsx';
import { FieldError } from '../../../admin/ui.jsx';

export default function ForgotPassword() {
  const { flash } = usePage();
  const form = useForm({ email: '' });

  const submit = (e) => {
    e.preventDefault();
    form.post('/admin/forgot-password');
  };

  return (
    <AuthShell title="Reset your password">
      <form className="login-form" onSubmit={submit}>
        <div style={{ display: 'flex', flexDirection: 'column', gap: 8, marginBottom: 8 }}>
          <h2>Reset your password</h2>
          <p style={{ margin: 0, color: '#5C5F5A', fontSize: 16 }}>Enter your admin email and we'll send you a link to choose a new password.</p>
        </div>
        <label className="field" style={{ fontWeight: 600 }}>Email
          <input type="email" autoComplete="username" placeholder="you@example.com" value={form.data.email} onChange={(e) => form.setData('email', e.target.value)} required autoFocus />
          <FieldError error={form.errors.email} />
        </label>
        {flash?.status && <div className="form-success" role="status">{flash.status}</div>}
        <button type="submit" className="btn btn-submit" style={{ padding: 17, fontWeight: 700 }} disabled={form.processing}>
          {form.processing ? 'Sending...' : 'Send reset link'}
        </button>
        <Link href="/admin/login" style={{ textAlign: 'center' }}>← Back to sign in</Link>
      </form>
    </AuthShell>
  );
}
