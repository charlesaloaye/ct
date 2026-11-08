import { router, useForm, usePage } from '@inertiajs/react';
import { Field, FieldError, PageHead } from '../../admin/ui.jsx';

function Profile({ profile }) {
  const form = useForm(profile);
  const save = (e) => {
    e.preventDefault();
    form.put('/admin/account/profile', { preserveScroll: true });
  };
  return (
    <form onSubmit={save} className="panel lg">
      <h2>Profile</h2>
      <div className="a-grid w240">
        <Field label="Name" autoComplete="name" value={form.data.name} onChange={(e) => form.setData('name', e.target.value)} error={form.errors.name} />
        <Field label="Sign-in email" type="email" autoComplete="email" value={form.data.email} onChange={(e) => form.setData('email', e.target.value)} error={form.errors.email} hint="Also where password reset links go" />
      </div>
      <div className="a-actions"><button type="submit" className="a-btn a-dark" disabled={form.processing}>Save profile</button></div>
    </form>
  );
}

function Password() {
  const form = useForm({ current_password: '', password: '', password_confirmation: '' });
  const save = (e) => {
    e.preventDefault();
    form.put('/admin/account/password', { preserveScroll: true, onFinish: () => form.reset() });
  };
  return (
    <form onSubmit={save} className="panel lg">
      <h2>Password</h2>
      <div className="a-grid w240">
        <Field label="Current password" type="password" autoComplete="current-password" value={form.data.current_password} onChange={(e) => form.setData('current_password', e.target.value)} error={form.errors.current_password} />
        <Field label="New password" type="password" autoComplete="new-password" value={form.data.password} onChange={(e) => form.setData('password', e.target.value)} error={form.errors.password} hint="12+ characters, letters and numbers" />
        <Field label="Confirm new password" type="password" autoComplete="new-password" value={form.data.password_confirmation} onChange={(e) => form.setData('password_confirmation', e.target.value)} />
      </div>
      <div className="a-actions"><button type="submit" className="a-btn a-dark" disabled={form.processing}>Change password</button></div>
      <span className="sample-note">Changing it signs out every other browser.</span>
    </form>
  );
}

function TwoFactor({ tf }) {
  const { flash } = usePage();
  const codes = flash?.recoveryCodes;
  const confirm = useForm({ code: '' });
  const off = useForm({ password: '' });
  const regen = useForm({ password: '' });

  return (
    <section className="panel lg">
      <h2>Two-factor sign-in</h2>

      {codes && (
        <>
          <p style={{ margin: 0 }}>Save these recovery codes somewhere safe. Each one signs you in once if you lose your phone. <b>They will not be shown again.</b></p>
          <pre className="codes">{codes.join('\n')}</pre>
          <div className="a-actions">
            <button type="button" className="a-btn a-outline" onClick={() => navigator.clipboard?.writeText(codes.join('\n')).then(() => window.dispatchEvent(new CustomEvent('admin:toast', { detail: 'Recovery codes copied' })))}>Copy codes</button>
          </div>
        </>
      )}

      {tf.enabled && (
        <>
          <p style={{ margin: 0 }}>Two-factor sign-in is <b>on</b>. Signing in needs your password and a code from your authenticator app. {tf.recoveryCodesLeft} recovery code{tf.recoveryCodesLeft === 1 ? '' : 's'} left.</p>
          <form className="a-grid w240" onSubmit={(e) => { e.preventDefault(); regen.post('/admin/account/two-factor/recovery-codes', { preserveScroll: true, onFinish: () => regen.reset() }); }}>
            <Field label="Password (for new recovery codes)" type="password" autoComplete="current-password" value={regen.data.password} onChange={(e) => regen.setData('password', e.target.value)} error={regen.errors.password} />
            <div className="a-actions" style={{ alignSelf: 'end' }}><button type="submit" className="a-btn a-outline" disabled={regen.processing}>New recovery codes</button></div>
          </form>
          <form className="a-grid w240" onSubmit={(e) => { e.preventDefault(); if (window.confirm('Turn off two-factor sign-in?')) off.delete('/admin/account/two-factor', { preserveScroll: true, onFinish: () => off.reset() }); }}>
            <Field label="Password (to turn it off)" type="password" autoComplete="current-password" value={off.data.password} onChange={(e) => off.setData('password', e.target.value)} error={off.errors.password} />
            <div className="a-actions" style={{ alignSelf: 'end' }}><button type="submit" className="a-btn a-danger" disabled={off.processing}>Turn off two-factor</button></div>
          </form>
        </>
      )}

      {tf.pending && (
        <>
          <p style={{ margin: 0 }}>Scan this code with your authenticator app (Google Authenticator, 1Password, Authy...), or type the setup key. Then enter the 6-digit code it shows.</p>
          <div className="qr-row">
            <img src={tf.qrCode} alt="QR code for your authenticator app" width={192} height={192} className="qr" />
            <div className="cv-box" style={{ flex: 1 }}>
              <span className="txt"><b className="setup-key">{tf.secret.match(/.{1,4}/g).join(' ')}</b><span>Setup key (time-based)</span></span>
              <a className="a-btn a-outline" href={tf.otpauthUrl}>Open in app</a>
            </div>
          </div>
          <form className="a-grid w240" onSubmit={(e) => { e.preventDefault(); confirm.post('/admin/account/two-factor/confirm', { preserveScroll: true, onFinish: () => confirm.reset() }); }}>
            <label className="a-field">Code from the app
              <input className="a-input" inputMode="numeric" autoComplete="one-time-code" placeholder="123456" value={confirm.data.code} onChange={(e) => confirm.setData('code', e.target.value)} />
              <FieldError error={confirm.errors.code} />
            </label>
            <div className="a-actions" style={{ alignSelf: 'end' }}><button type="submit" className="a-btn a-dark" disabled={confirm.processing}>Confirm and turn on</button></div>
          </form>
        </>
      )}

      {!tf.enabled && !tf.pending && (
        <>
          <p style={{ margin: 0 }}>Two-factor sign-in is <b>off</b>. Turn it on so a stolen password alone can't open your admin.</p>
          <div className="a-actions">
            <button type="button" className="a-btn a-dark" onClick={() => router.post('/admin/account/two-factor', {}, { preserveScroll: true })}>Set up two-factor</button>
          </div>
        </>
      )}
    </section>
  );
}

export default function Account({ profile, twoFactor }) {
  return (
    <div className="settings-page">
      <PageHead kicker="You" title="Account & security" />
      <Profile key={JSON.stringify(profile)} profile={profile} />
      <Password />
      <TwoFactor tf={twoFactor} />
    </div>
  );
}
