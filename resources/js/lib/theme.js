import { useEffect, useState } from 'react';

// Light by default. A visitor's choice (header toggle) is saved in this browser
// and wins over the site default set in Admin → Site settings.
const KEY = 'ct-theme';
const EVENT = 'ct-theme-change';

export function storedTheme() {
  try {
    const v = localStorage.getItem(KEY);
    return v === 'dark' || v === 'light' ? v : null;
  } catch {
    return null;
  }
}

/** The site default from settings: 'light' | 'dark' | 'system' (system follows the device). */
export function resolveTheme(siteDefault = 'light') {
  const stored = storedTheme();
  if (stored) return stored;
  if (siteDefault === 'dark') return 'dark';
  if (siteDefault === 'system' && window.matchMedia?.('(prefers-color-scheme: dark)').matches) return 'dark';
  return 'light';
}

export function setTheme(theme) {
  try {
    localStorage.setItem(KEY, theme);
  } catch {
    // Private mode: the toggle still works for this page view.
  }
  window.dispatchEvent(new CustomEvent(EVENT, { detail: theme }));
}

/** Current effective theme; re-renders when the toggle is used anywhere on the page. */
export default function useTheme(siteDefault = 'light') {
  const [theme, setState] = useState(() => resolveTheme(siteDefault));
  useEffect(() => setState(resolveTheme(siteDefault)), [siteDefault]);
  useEffect(() => {
    const onChange = (e) => setState(e.detail);
    window.addEventListener(EVENT, onChange);
    return () => window.removeEventListener(EVENT, onChange);
  }, []);
  return theme;
}
