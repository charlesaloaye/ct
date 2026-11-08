// JSON requests that are not page visits (uploads, the AI assistant). Inertia
// handles everything else. Laravel's XSRF-TOKEN cookie is sent back as a header.

export class HttpError extends Error {
  constructor(status, message, errors = {}) {
    super(message);
    this.status = status;
    this.errors = errors;
  }
}

function xsrfToken() {
  const match = document.cookie.split('; ').find((c) => c.startsWith('XSRF-TOKEN='));
  return match ? decodeURIComponent(match.slice('XSRF-TOKEN='.length)) : '';
}

async function request(method, url, body) {
  const isForm = typeof FormData !== 'undefined' && body instanceof FormData;
  const headers = { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-XSRF-TOKEN': xsrfToken() };
  if (body !== undefined && !isForm) headers['Content-Type'] = 'application/json';

  let res;
  try {
    res = await fetch(url, {
      method,
      headers,
      credentials: 'same-origin',
      body: body === undefined ? undefined : isForm ? body : JSON.stringify(body),
    });
  } catch {
    throw new HttpError(0, 'Could not reach the server. Check your connection and try again.');
  }

  if (res.status === 204) return null;
  const data = await res.json().catch(() => null);

  if (!res.ok) {
    if (res.status === 401) window.location.assign('/admin/login');
    const firstError = data?.errors && Object.values(data.errors)[0]?.[0];
    const message = res.status === 429
      ? 'Too many requests. Please wait a little and try again.'
      : res.status === 419
        ? 'Your session expired. Reload the page and try again.'
        : firstError || data?.message || `Request failed (${res.status})`;
    throw new HttpError(res.status, message, data?.errors || {});
  }
  return data;
}

export const http = {
  post: (url, body = {}) => request('POST', url, body),
  /** Upload one file; returns the stored media ({ id, url, path }). */
  upload: async (file, { url = '/admin/media', alt } = {}) => {
    const form = new FormData();
    form.append('file', file);
    if (alt) form.append('alt', alt);
    return (await request('POST', url, form)).data;
  },
};
