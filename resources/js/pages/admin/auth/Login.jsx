import { Link, useForm } from '@inertiajs/react';
import AuthShell from '../../../admin/AuthShell.jsx';
import { FieldError } from '../../../admin/ui.jsx';

export default function Login() {
  const form = useForm({ email: '', password: '', remember: false });

  const submit = (e) => {
    e.preventDefault();
    form.post('/admin/login', { onFinish: () => form.reset('password') });
  };

  return (
    <AuthShell title="Sign in">
      <form className="login-form" onSubmit={submit}>
        <div style={{ display: 'flex', flexDirection: 'column', gap: 8, marginBottom: 8 }}>
          <h2>Sign in</h2>
          <p style={{ margin: 0, color: '#5C5F5A', fontSize: 16 }}>Welcome back.</p>
        </div>
        <label className="field" style={{ fontWeight: 600 }}>Email
          <input type="email" autoComplete="username" placeholder="you@example.com" value={form.data.email} onChange={(e) => form.setData('email', e.target.value)} required autoFocus />
          <FieldError error={form.errors.email} />
        </label>
        <label className="field" style={{ fontWeight: 600 }}>Password
          <input type="password" autoComplete="current-password" value={form.data.password} onChange={(e) => form.setData('password', e.target.value)} required />
          <FieldError error={form.errors.password} />
        </label>
        <div className="row">
          <label className="a-check"><input type="checkbox" checked={form.data.remember} onChange={(e) => form.setData('remember', e.target.checked)} />Keep me signed in</label>
          <Link href="/admin/forgot-password">Forgot password?</Link>
        </div>
        <button type="submit" className="btn btn-submit" style={{ padding: 17, fontWeight: 700 }} disabled={form.processing}>{form.processing ? 'Signing in...' : 'Sign in'}</button>
        <a href="/" style={{ textAlign: 'center' }}>← Back to site</a>
      </form>
    </AuthShell>
  );
}
