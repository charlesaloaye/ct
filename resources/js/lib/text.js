/** "I ship ... *couldn't finish.*" -> [before, highlighted, after]. Without asterisks, the last two words are highlighted. */
export function splitHeadline(headline = '') {
  const marked = (headline || '').match(/^(.*)\*(.+)\*(.*)$/);
  if (marked) return [marked[1], marked[2], marked[3]];
  const words = (headline || '').trim().split(/\s+/);
  if (words.length < 3) return ['', headline || '', ''];
  return [`${words.slice(0, -2).join(' ')} `, words.slice(-2).join(' '), ''];
}

/** "All {count} projects" -> "All 5 projects". */
export const fill = (template, values) => (template || '').replace(/\{(\w+)\}/g, (_, k) => (values[k] ?? ''));

export const isExternal = (href) => /^https?:\/\//i.test(href || '');

export const slugify = (s) => (s || '').toLowerCase().replace(/&/g, 'and').replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');

export const domainOf = (url) => (url || '').replace(/^https?:\/\/(www\.)?/, '').replace(/\/$/, '');
