# Charles Aloaye: personal brand and Knowledge Panel readiness

*Prepared November 2026 for Charles Aloaye Sedenu (CharlesTechy). Covers the website at charlestechy.com and the work around it.*

## The honest starting point

No website, schema markup or SEO work can guarantee a Google Knowledge Panel. Google decides on its own whether a person is a distinct, notable entity. It does that by cross-checking many independent sources, and nobody can apply for, buy or force a panel.

This plan does what is in your control:

- make your identity unambiguous and consistent everywhere;
- make your website the clearest, most authoritative source about you;
- earn genuine independent coverage, which is the part Google weighs most.

Expect this to take months to years, not weeks.

### Five different processes

These are often confused, but they are separate:

| Process | What it means | What you control |
|---|---|---|
| **1. Indexing** | Google has crawled your pages and can show them in results. | High. Submit the sitemap in Search Console; keep pages crawlable. |
| **2. Ranking** | Where your pages appear for a search such as "Charles Aloaye". | Medium. Good content, consistent identity and links from relevant sites. |
| **3. Entity recognition** | Google's Knowledge Graph understands "Charles Aloaye" as one specific person, linked to his companies and profiles. | Indirect. Structured data and consistency help, but independent sources decide it. |
| **4. Panel generation** | Google chooses to show a Knowledge Panel for that entity. | None directly. It follows from strong entity recognition and public interest. |
| **5. Panel verification** | Once a panel exists, you claim it and suggest edits. | Full, but only after step 4. |

## 1. Brand positioning and naming

**Primary public name:** Charles Aloaye. Use this everywhere first.

**Full name:** Charles Aloaye Sedenu. Use it in formal contexts (press kit, legal, About page).

**Handle:** CharlesTechy. Make it the username wherever it's available (it is already on DEV and Instagram).

**One-line positioning:** Software engineer, technology entrepreneur and TRCN-certified educator in Lagos; founder and CTO of Built Different LTD.

**Naming rule:** write "Charles Aloaye" in headlines and bios. Add "(CharlesTechy)" the first time in longer copy. Don't vary it ("Charles A.", "Charles Sedenu", "C. Aloaye"). Every variation weakens the signal that it's one person.

**Roles (in this order):**

- Software Engineer
- Technology Entrepreneur
- SaaS Builder
- TRCN-certified Educator

**Confirmed relationships, and only these:**

- Built Different LTD: Founder & CTO. Parent company of Frontstore, DelsuTools, UniAdvisor and JAMB Advisor. Its products also include StudyAbroad.ng and ScholarshipHQ.ng, whose own sites credit Built Different.
- Hamplus Technologies: CTO.
- Gen6ixx LTD: CTO.

AfricanStores.co.uk was in the original brief but isn't on the site, because the relationship hasn't been confirmed and no public source links it to you. Add it in the admin (Products) once confirmed.

## 2. Website architecture (built)

| Page | Purpose | Structured data |
|---|---|---|
| `/` Home | Overview, featured work, products, partners, contact | WebSite, Person |
| `/about` | The authoritative profile: biography, roles, names, profiles | **ProfilePage** with Person, Organizations, Breadcrumbs |
| `/experience` | Leadership roles and full work history | Breadcrumbs |
| `/ventures` | Built Different, Hamplus, Gen6ixx and their products | Organization ×3, Breadcrumbs |
| `/products` | Every product with its company and real logo | Breadcrumbs |
| `/work` and `/work/{project}` | Client projects and case studies | CreativeWork, Breadcrumbs |
| `/expertise` | Technical stack and services | Breadcrumbs |
| `/blog` and `/blog/{article}` | Articles and insights | BlogPosting (author = your Person), Breadcrumbs |
| `/media` | Real coverage (only once it exists) and a press kit | Breadcrumbs |
| `/contact` | Enquiry form | Breadcrumbs |

**Internal linking:** every page footer links to every main page. About links to Ventures and Experience; Ventures links to Products; products link back to their company.

**Everything is editable** in the admin:

- Ventures, Products and Media & press: their own sections.
- Biography, bios and page copy: Pages & SEO.
- Names and job titles: Site settings.
- Toolkit: Homepage lists.

## 3. Standard biographies

Use these word for word on every profile, event programme and press mention. They live in **Admin → Pages & SEO → About & standard bios**, and are also on the public press kit at `/media`.

**Short (profiles, about 50 words):**

> Charles Aloaye (CharlesTechy) is a Lagos-based software engineer, technology entrepreneur and TRCN-certified educator. He is the founder and CTO of Built Different LTD, the company behind Frontstore, DelsuTools, UniAdvisor and JAMB Advisor.

**Medium (events, podcasts, press):**

> Charles Aloaye (CharlesTechy) is a software engineer, technology entrepreneur and TRCN-certified educator based in Lagos, Nigeria. He is the founder and CTO of Built Different LTD, which builds custom software for businesses and runs its own products, including Frontstore, DelsuTools, UniAdvisor and JAMB Advisor, and he is CTO of Hamplus Technologies and Gen6ixx LTD.
>
> Over more than ten years he has built web applications, SaaS platforms and mobile products for organisations in Nigeria and the United Kingdom, often rescuing stalled codebases and getting them to launch. He works with PHP and Laravel, Python and FastAPI, and JavaScript and TypeScript with React and Next.js, and he teaches and mentors developers.

**Profile headline (LinkedIn, X):**

> Software Engineer · Founder & CTO, Built Different · TRCN-certified Educator

## 4. Structured data (built)

Every page carries schema.org JSON-LD, rendered on the server so crawlers see it immediately. It follows [Google's ProfilePage guidelines](https://developers.google.com/search/docs/appearance/structured-data/profile-page).

**Person, defined once and referenced everywhere:**

- **Stable `@id`:** `https://charlestechy.com/#person`. The homepage, the About page and every article's author point to the same id, so all of them describe one entity.
- **`name`:** "Charles Aloaye". **`alternateName`:** "CharlesTechy" and "Charles Aloaye Sedenu".
- **`jobTitle`:** your four roles.
- **`hasCredential`:** a teaching registration recognised by the Teachers Registration Council of Nigeria (trcn.gov.ng). No registration number is shown; add one only if you want it public.
- **`worksFor`:** links to your three company records.
- **`sameAs`:** your official profiles from Site settings (GitHub, DEV, X, Instagram, Facebook). A Messenger link is converted to the Facebook profile it opens. Add LinkedIn and YouTube in Site settings → Social links once you send them, and they flow into `sameAs` automatically.
- **`image`:** square, 4:3 and 16:9 crops, generated automatically from the portrait you upload. Google asks for all three.
- **`knowsAbout`:** your toolkit. **`address`:** Lagos, NG.

**ProfilePage (on `/about` only, as Google specifies):** `mainEntity` is the Person, with `dateCreated` and `dateModified` taken from when the profile settings were last edited.

**Organization (one per venture):** Built Different lists you as **founder**; Hamplus and Gen6ixx list you as **employee** (CTO). Built Different's products appear as its brands.

**Not included, on purpose:** no review stars, follower counts, awards or "notable" claims. Nothing in the markup states anything the page doesn't show.

**Validate after launch:**

- [Rich Results Test](https://search.google.com/test/rich-results) on `/about`, `/ventures` and an article.
- [Schema Markup Validator](https://validator.schema.org) for the full graph.

## 5. Technical SEO (built)

- Server-rendered title, description, canonical, Open Graph and X card tags on every page. Article pages add published, modified, section and tag meta.
- Titles kept within Google's roughly 65-character display (the suffix is dropped when it would overflow).
- `sitemap.xml` with every page, case study and live article. `robots.txt` points to it and excludes `/admin`. The admin also sends `noindex`.
- RSS feed for the blog.
- Search Console, Bing and Google Analytics: paste the codes into Admin → Pages & SEO. Nothing loads until a code is set, and analytics only runs on public pages.
- Images lazy-load. Screenshots are compressed JPEG. Profile crops are generated once and cached.
- Mobile layout tested from 320px to 1920px, light and dark. Semantic headings: one `h1` per page.
- Automated tests check all of this on every change.

**Before switching on Google Analytics:** it sets cookies. UK visitors (GDPR) and Nigerian visitors (NDPA) may need a consent banner first. Ask before enabling it, or rely on the built-in cookie-free page-view counter in the admin overview.

## 6. Google Search Console setup

1. Go to [search.google.com/search-console](https://search.google.com/search-console) and sign in with the Google account you'll keep long term.
2. **Add property → Domain → `charlestechy.com`.** Verify with the DNS TXT record Google gives you, added at your domain registrar. This covers every subdomain and both http and https. (Alternative: a URL-prefix property with the HTML tag method, where you paste only the code into Admin → Pages & SEO → Google Search Console code.)
3. **Sitemaps → submit `https://charlestechy.com/sitemap.xml`.**
4. **URL Inspection → `https://charlestechy.com/about` → Request indexing.** Repeat for `/` and `/ventures`.
5. After a few days, check **Pages** (indexing errors) and **Enhancements** (structured-data errors). Fix anything reported.
6. Keep this account: Search Console is one of the four ways Google lets you verify a Knowledge Panel later.
7. Optional: add the site to [Bing Webmaster Tools](https://www.bing.com/webmasters) the same way (it can import from Search Console).

## 7. Digital identity consistency checklist

Use the same name, photo, headline and short bio on every profile, and link each one to `https://charlestechy.com/about`.

| Platform | Action |
|---|---|
| **LinkedIn** ([/in/charles-sedenu](https://www.linkedin.com/in/charles-sedenu/), now on the site) | Name "Charles Aloaye", headline as in section 3, experience entries that match the site exactly (Built Different LTD: Founder & CTO; Hamplus Technologies: CTO; Gen6ixx LTD: CTO), website charlestechy.com/about. **Close or merge the two other accounts**: `sedenu-charles-7a840b140` (linked from about.me) and `charles-techy-2b643b198` (linked from Hashnode). Then update those two pages to the one you keep. |
| **GitHub** (charlesaloaye) | The name is currently just "Sedenu": change it to "Charles Aloaye". Bio = short bio, website = charlestechy.com (currently empty). Pin real public repositories. The second account `charlestechy` holds only a profile readme: use it or delete it. |
| **YouTube** ([CharlesTechy channel](https://www.youtube.com/channel/UCz5nyN0Og65kX8dAYg7dI0A), now on the site) | Claim a handle such as @charlestechy. About section: short bio and a link to charlestechy.com. YouTube is a Knowledge Panel verification route. |
| **X** (sedenucharles) | Display name "Charles Aloaye", bio = headline, website = charlestechy.com. Consider the handle @charlestechy if available, and keep the old one as a redirect or account. X is a Knowledge Panel verification route. |
| **Facebook** | Professional profile or page named "Charles Aloaye", linking to the site. |
| **Instagram, DEV, Medium, Hashnode, Threads, Apify** (all now on the site) | One display name everywhere: today they read "CharlesTechy" (DEV), "Charles Techy" (Hashnode), "Charles Sedenu" (Apify) and "Charles Aloaye Sedenu" (Instagram). Use **Charles Aloaye**. Fix DEV's website link: it points to `charlessedenu.cv`, which doesn't load. Medium `@aloayecharles` (linked from about.me) has no posts: remove it or point about.me to `@charlestechy`. |
| **builtdifferent.ng** | Add a team or about entry: "Founded by Charles Aloaye, Founder & CTO", linking to charlestechy.com/about. **Fix the logo on this site**: its logo file returns "403 Forbidden", so visitors see a broken image. |
| **Product sites** (Frontstore, DelsuTools, UniAdvisor, JAMB Advisor, StudyAbroad.ng, ScholarshipHQ.ng) | A footer line "A Built Different product", linking to builtdifferent.ng. Only DelsuTools, StudyAbroad.ng and ScholarshipHQ.ng credit Built Different today. |
| **hamplustech.com** | A leadership entry for "Charles Aloaye, CTO", linking to the site. |
| **Gen6ixx** | If it has a website, a leadership entry linking to your site, and send me the URL for the Ventures page. |
| **Developer communities** | Same name and bio on Stack Overflow, Hashnode or Medium if you use them. Only create profiles you will keep active. |

**Don't:**

- buy links or guest posts;
- mass-submit to directories;
- create profiles you won't maintain;
- use different names or photos on different sites;
- list companies or roles on any profile that the site doesn't confirm.

Unexplained inconsistency is the biggest obstacle to entity recognition.

## 8. Independent authority and media strategy

**Already working for you:** two of your articles were published by Apify on its DEV organisation (September 2026). That's content about your work, published by another company. All 17 of your external articles (Medium, DEV, Hashnode) are listed on the blog under "Writing elsewhere", managed in Admin → Writing elsewhere.

Google trusts what **other people** publish about you far more than what you publish yourself. Prioritise editorial coverage you didn't pay for.

1. **Founder stories in tech publications.** Pitch the Built Different story (one studio running several education and commerce products for Nigerian students and small businesses) to outlets such as TechCabal, Techpoint Africa, Condia (formerly Benjamindada.com), Disrupt Africa and BusinessDay Tech. Lead with a newsworthy angle: product usage, a launch, or something you learned that others can use.
2. **Podcasts.** African tech, founder and developer podcasts. Your codebase-rescue experience and your educator background give hosts a clear angle.
3. **Speaking.** Laravel Nigeria, PHP and Python meetups, DevFest (GDG Lagos and others), Lagos tech community events, and university tech weeks (your educator credential helps here).
4. **Teaching and mentoring in public.** Workshops, bootcamp sessions, or a free course on YouTube. This builds genuine recognition and supports YouTube verification.
5. **Technical writing.** Publish real articles on the site's blog, cross-posted to DEV with a canonical link back to the original. Topics you have first-hand experience in: Paystack and Flutterwave integration, rescuing Laravel codebases, building for Nigerian students.
6. **Open source.** Release genuinely useful packages (for example, Nigerian payment or education utilities) under your GitHub account.
7. **Product launches.** Product Hunt or BetaList launches for Built Different products. Frontstore already launched on Product Hunt.
8. **Credible listings, not directories.**
   - Companies House (UK) or CAC (Nigeria): your company registration is a public record, with no action needed.
   - Crunchbase: profiles for Built Different and for you, with facts that match the site.

Every real piece of coverage goes into **Admin → Media & press**, linked to the source. The site refuses coverage without a link, so it stays verifiable.

**Listed so far:** your talk at DELSU Tech Invasion 1.0 (4 February 2023, Abraka), linked to Hamplus Hub's event post. No public page names you as a speaker yet. Hamplus pages only list CharlesTechy as a *partner* of DTI 2.0 and 3.0. If you have a photo, recording or programme that names you, post it publicly (LinkedIn or YouTube) and switch the entry's link to it. Hamplus is also a company where you are CTO, so search engines won't count this as independent coverage.

**Later, not now:**

- **Wikidata:** an item about you is only appropriate once independent, reliable coverage exists. Self-created items without sources are routinely deleted.
- **Wikipedia:** never write your own article (conflict of interest). If coverage grows, an independent editor may.

## 9. Knowledge Panel readiness checklist

**Identity and website (mostly done):**

- [x] One primary name used consistently on the site (Charles Aloaye), with alternate names declared.
- [x] An authoritative About page with ProfilePage markup, one stable Person `@id`, and photos in three ratios.
- [x] Companies modelled as Organizations with the correct relationship to you.
- [x] Sitemap, robots, canonicals, crawlable server-rendered content.
- [ ] Site live on charlestechy.com with HTTPS.
- [ ] Search Console verified, sitemap submitted, About page indexed (URL Inspection shows "URL is on Google").
- [ ] Rich Results Test passes on `/about`, `/ventures` and articles.

**Consistency (your actions):**

- [x] LinkedIn, YouTube, Medium, Hashnode, Threads and Apify added to Site settings → Social links (and to `sameAs`).
- [ ] Two extra LinkedIn accounts closed or merged; one display name on every profile.
- [ ] Every profile in section 7 updated to the same name, photo, headline, short bio and link.
- [ ] Company and product sites link back to you.
- [ ] builtdifferent.ng logo fixed.

**Independent sources (where the time goes):**

- [ ] At least 3–5 independent, editorial mentions of "Charles Aloaye" with your role (articles, interviews, podcast pages, event speaker pages).
- [ ] Each one added to Media & press.

**Recognition signs to watch for:**

- Searching "Charles Aloaye" shows your site and profiles together on page one.
- Google suggests "Charles Aloaye" with a description in autocomplete.
- A panel appears for searches like "Charles Aloaye Built Different".

**If a panel appears:**

1. Search for yourself, click **"Claim this knowledge panel"** at the bottom of the panel (on mobile, it's under the menu). Not every panel is claimable yet, so check periodically.
2. Sign in with one of the official accounts Google lists: **Search Console, YouTube, X or Facebook**. That's why those accounts should be yours, verified and linked to the site. If none can be matched, Google asks for more identity information.
3. Once verified, suggest corrections through the panel's feedback options. Google keeps final say over what appears.

Source: [Get verified on Google](https://support.google.com/knowledgepanel/answer/7534902).

**About "Google Search profile":** I couldn't find this feature in Google's current official documentation, so this plan doesn't rely on it. If Google offers it in your region, it would appear when you search your own name while signed in, and the same consistent profile information applies.

## 10. 90-day roadmap

**Days 1–14: launch and foundations**

- Deploy the site to charlestechy.com (README, section 5). Turn on HTTPS.
- Add your OpenAI key and SMTP email in Admin → Integrations. Turn on two-factor sign-in.
- Search Console: verify, submit the sitemap, request indexing for `/`, `/about` and `/ventures`. Run the Rich Results Test.
- Close or merge the two extra LinkedIn accounts. Set the display name to "Charles Aloaye" on GitHub, DEV, Hashnode, Apify and Instagram.
- Fix the Built Different logo on builtdifferent.ng.

**Days 15–30: consistency sweep**

- Update every profile per section 7: same name, photo, headline, short bio, link to /about.
- Add "Founded by Charles Aloaye" on builtdifferent.ng, and "A Built Different product" on each product site.
- Create a Crunchbase entry for Built Different and for yourself, with facts that match the site.
- Publish one strong, first-hand article on the blog; cross-post to DEV with a canonical link.

**Days 31–60: first independent signals**

- Pitch 5–8 targeted publications and podcasts with one clear story each.
- Apply to speak at 2–3 meetups or community events (Laravel, Python, DevFest, university tech weeks).
- Publish two more articles. Release one small open-source package.
- Add every real mention to Media & press as it lands.

**Days 61–90: compound and measure**

- Follow up on pitches. Accept interviews and podcasts.
- Teach or mentor publicly: run a workshop, or record a short course on YouTube.
- In Search Console, check queries for "Charles Aloaye" and "CharlesTechy": impressions, position, and which pages rank.
- Review this checklist. Keep the cadence going: one article and one outreach push every month.

**Measuring progress:**

- Search Console: impressions for your name, whether `/about` is indexed, enhancement reports clean.
- Media & press entries: count of independent mentions.
- Branded search results: how much of page one for "Charles Aloaye" is about you.

## Still needed from you

- **Built Different logo:** upload it in Admin → Ventures, and fix it on builtdifferent.ng.
- **Gen6ixx:** website, logo and a one-line description, if public.
- **Years for each venture role**, if you want them shown. They're left blank rather than guessed.
- **AfricanStores.co.uk:** confirm your relationship if it should be listed.
- **Real coverage:** links to any existing articles, interviews or talks about you.
