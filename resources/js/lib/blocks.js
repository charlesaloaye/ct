import { slugify } from './text.js';
// Converts between the editor's plain text and the article body blocks
// ({type: p|h2|code|quote, text, id?, toc?}) the blog renders.
//   ## Heading      -> h2
//   > Quote         -> quote
//   ``` ... ```     -> code
//   blank-line separated text -> p



export function blocksToText(blocks = []) {
  return (blocks || []).map((b) => {
    if (b.type === 'h2') return `## ${b.text}`;
    if (b.type === 'quote') return `> ${b.text}`;
    if (b.type === 'code') return `\`\`\`\n${b.text}\n\`\`\``;
    return b.text;
  }).join('\n\n');
}

/** previous: the blocks last loaded, so headings keep their id and short TOC label. */
export function textToBlocks(text, previous = []) {
  const known = new Map((previous || []).filter((b) => b.type === 'h2').map((b) => [b.text, b]));
  const ids = new Set();
  const blocks = [];
  const parts = text.replace(/\r\n/g, '\n').split(/^```[^\n]*\n([\s\S]*?)^```[ \t]*$/m);

  parts.forEach((part, i) => {
    if (i % 2 === 1) {
      blocks.push({ type: 'code', text: part.replace(/\n$/, '') });
      return;
    }
    part.replace(/^(#{1,6}[ \t]+[^\n]+)\n(?!\n)/gm, '$1\n\n').split(/\n\s*\n/).forEach((raw) => {
      const chunk = raw.trim();
      if (!chunk) return;
      const heading = chunk.match(/^#{1,6}[ \t]+([^\n]+)$/);
      if (heading) {
        const textValue = heading[1].trim();
        const prev = known.get(textValue);
        let id = prev?.id || slugify(textValue) || 'section';
        const base = id;
        for (let n = 2; ids.has(id); n += 1) id = `${base}-${n}`;
        ids.add(id);
        blocks.push({ type: 'h2', text: textValue, id, ...(prev?.toc ? { toc: prev.toc } : {}) });
      } else if (chunk.startsWith('>')) {
        blocks.push({ type: 'quote', text: chunk.replace(/^>\s?/gm, '').trim() });
      } else {
        blocks.push({ type: 'p', text: chunk.replace(/\s*\n\s*/g, ' ') });
      }
    });
  });
  return blocks;
}
