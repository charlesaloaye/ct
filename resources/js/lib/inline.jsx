// Inline formatting inside article blocks: **bold**, _italic_, `code`,
// [links](https://...) and a paragraph that is just ![alt](url) becomes an image.
// Builds React elements (never raw HTML), and only allows safe link targets.

const SAFE_URL = /^(https?:\/\/|mailto:|\/(?!\/))/i;
const TOKEN = /(\*\*[^*]+\*\*|_[^_\s][^_]*_|`[^`]+`|\[[^\]]+\]\([^)\s]+\))/g;

export function imageOnly(text) {
  const m = text.trim().match(/^!\[([^\]]*)\]\(([^)\s]+)\)$/);
  return m && SAFE_URL.test(m[2]) ? { alt: m[1], src: m[2] } : null;
}

export function renderInline(text) {
  return text.split(TOKEN).map((part, i) => {
    if (!part) return null;
    if (part.startsWith('**') && part.endsWith('**') && part.length > 4) return <strong key={i}>{part.slice(2, -2)}</strong>;
    if (part.startsWith('`') && part.endsWith('`') && part.length > 2) return <code key={i}>{part.slice(1, -1)}</code>;
    if (part.startsWith('_') && part.endsWith('_') && part.length > 2) return <em key={i}>{part.slice(1, -1)}</em>;
    const link = part.match(/^\[([^\]]+)\]\(([^)\s]+)\)$/);
    if (link) {
      if (!SAFE_URL.test(link[2])) return link[1];
      const external = /^https?:\/\//i.test(link[2]);
      return <a key={i} href={link[2]} {...(external ? { target: '_blank', rel: 'noreferrer' } : {})}>{link[1]}</a>;
    }
    return part;
  });
}
