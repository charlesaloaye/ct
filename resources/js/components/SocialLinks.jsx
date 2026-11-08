import {
  siBehance, siBluesky, siDevdotto, siDiscord, siDribbble, siFacebook, siGithub, siGitlab, siHashnode, siInstagram,
  siMedium, siMessenger, siPinterest, siProducthunt, siRss, siSnapchat, siStackoverflow, siTelegram, siThreads,
  siTiktok, siWhatsapp, siX, siYoutube,
} from 'simple-icons';
import { linkProps } from './Site.jsx';

// [icon, words that may appear in the label, hosts its links live on]
const brands = [
  [siGithub, ['github'], ['github.com']],
  [siGitlab, ['gitlab'], ['gitlab.com']],
  [siDevdotto, ['dev', 'dev.to'], ['dev.to']],
  [siX, ['x', 'twitter'], ['x.com', 'twitter.com']],
  [siInstagram, ['instagram'], ['instagram.com']],
  [siFacebook, ['facebook'], ['facebook.com', 'fb.com']],
  [siMessenger, ['messenger'], ['m.me', 'messenger.com']],
  [siWhatsapp, ['whatsapp'], ['wa.me', 'whatsapp.com']],
  [siYoutube, ['youtube'], ['youtube.com', 'youtu.be']],
  [siTiktok, ['tiktok'], ['tiktok.com']],
  [siThreads, ['threads'], ['threads.net', 'threads.com']],
  [siBluesky, ['bluesky'], ['bsky.app']],
  [siTelegram, ['telegram'], ['t.me', 'telegram.org']],
  [siDiscord, ['discord'], ['discord.com', 'discord.gg']],
  [siSnapchat, ['snapchat'], ['snapchat.com']],
  [siPinterest, ['pinterest'], ['pinterest.com']],
  [siMedium, ['medium'], ['medium.com']],
  [siHashnode, ['hashnode'], ['hashnode.com', 'hashnode.dev']],
  [siDribbble, ['dribbble'], ['dribbble.com']],
  [siBehance, ['behance'], ['behance.net']],
  [siStackoverflow, ['stack overflow', 'stackoverflow'], ['stackoverflow.com']],
  [siProducthunt, ['product hunt', 'producthunt'], ['producthunt.com']],
];

const hostOf = (href) => {
  try {
    return new URL(href).hostname.replace(/^www\./, '');
  } catch {
    return '';
  }
};

/** The brand icon for a link: by its label first (it says what you meant), then by its address. */
function iconFor({ label, href }) {
  const name = (label || '').trim().toLowerCase();
  const host = hostOf(href);
  return (brands.find(([, names]) => names.includes(name))
    || brands.find(([, , hosts]) => hosts.some((h) => host === h || host.endsWith(`.${h}`))))?.[0];
}

export const BrandIcon = ({ icon, size = 20 }) => (
  <svg width={size} height={size} viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d={icon.path} /></svg>
);

/** Social links as icons. A link with no known brand icon (e.g. LinkedIn) keeps its text label. */
export default function SocialLinks({ socials, rss = false }) {
  return (
    <>
      {socials.map((s) => {
        const icon = iconFor(s);
        return (
          <a key={`${s.label}-${s.href}`} href={s.href} {...linkProps(s.href)} className={icon ? 'social-icon' : 'social-text'} aria-label={s.label} title={s.label}>
            {icon ? <BrandIcon icon={icon} /> : s.label}
          </a>
        );
      })}
      {rss && <a href="/feed.xml" className="social-icon" aria-label="RSS feed" title="RSS feed"><BrandIcon icon={siRss} /></a>}
    </>
  );
}
