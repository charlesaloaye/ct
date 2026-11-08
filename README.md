# charlestechy.com

The portfolio, blog and admin for charlestechy.com: one Laravel 11 application with a React front end rendered through Inertia.js. There is no separate API or front-end server. Everything visitors see can be edited in the admin, and nothing on the site is placeholder content.

| Layer | What it uses |
| --- | --- |
| Server | PHP 8.2+, Laravel 11, MySQL 8 / MariaDB |
| Pages | React 19 + Inertia.js v3, built with Vite |
| Auth | Laravel session auth, two-factor sign-in (TOTP), password reset by email |
| Background work | Database queue (emails, newsletters) and the scheduler (scheduled posts, link checks) |
| AI | OpenAI (Responses API) or Anthropic (Claude), chosen in the admin |
| Files | The `public` disk (`storage/app/public`). Switch to S3 or Spaces with `MEDIA_DISK=s3` |

> **Laravel 11 security notes.** Laravel 11 is past its security-fix window. `composer audit` lists advisories that are only fixed in 12.x. This app is not exposed to them:
> - **CRLF injection in the `email` rule:** every email field uses `App\Rules\SafeEmail`, which rejects control characters. Tests cover it.
> - **TokenGuard auth bypass:** the token guard is not used. Admin auth is the session guard.
> - **Temporary signed URL path confusion:** no signed URLs are used. Subscription and reset links use random tokens.
> - **XSS on the debug page:** the debug page exists only with `APP_DEBUG=true`, so keep it `false` in production.
>
> Moving to Laravel 12 later only means changing the version in `composer.json`.

---

## 1. Run it locally

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
# Create the database, then set DB_*, ADMIN_EMAIL and ADMIN_PASSWORD in .env
php artisan migrate --seed
php artisan storage:link
composer dev        # server + queue worker + logs + Vite, together
```

Open http://127.0.0.1:8001 for the site and http://127.0.0.1:8001/admin to sign in.

The seeders load the site's real content: the copy, 8 roles, 6 services, 3 products, 5 published projects plus 2 drafts, 4 case studies, the portrait, the project screenshots and the CV (copied from `database/seeders/data/media`). One article is published. The other 17 articles are planned topics, kept as **drafts** until you write them, so the blog never shows an empty article.

To change the admin password later, use **Admin → Account & security**. Re-running `php artisan db:seed --class=AdminUserSeeder` also resets it from `.env`.

### Tests

```bash
php artisan test        # 172 Pest tests (SQLite in memory)
./vendor/bin/pint       # PSR-12
```

---

## 2. What the admin controls

Everything on the public site comes from the database. Nothing is hard-coded in the pages.

| Admin screen | What it changes |
| --- | --- |
| **Overview** | Counts, latest enquiries, CV downloads and page views (30 days), and the availability switch. Its **Finish setting up** list shows anything still missing: email service, AI key, CV, portrait, two-factor sign-in, and dead links found by the weekly check. Each item links to where it is fixed. |
| **Messages** | Contact form enquiries. Opening one marks it read. **Send reply** emails the sender from the site; the reply-to is your contact email and the reply is saved on the message. Archive, restore and delete are also here. |
| **Subscribers** | The newsletter list, with search, a confirmed/pending filter, CSV export and remove. |
| **Projects, Products, Articles, Services, Experience, Testimonials** | Create, edit, delete, reorder (↑↓), hide/show (an item returns to its exact previous status) and search. Projects include the full case study: summary, challenge, build, features, outcome, results, tech stack, cover and gallery. |
| **Article editor** | Markdown-style toolbar, inline images, cover, tags, category, SEO fields, scheduling, featured flag, "email subscribers on publish", Unpublish, and the AI assistant. |
| **Site settings** | Name, logo mark, wordmark, role, hero headline and intro, button labels and links, portrait, "last shipped" card, availability text, contact details, WhatsApp, CV upload and download file name, social links (ordered), default theme and accent colour. |
| **Pages & SEO** | Every heading, lead and button on the homepage, Work, Blog, article and case study pages; the contact form options and thank-you text; search titles and descriptions for each page; the share image. |
| **Homepage lists** | Hero stats, client names, rescue steps, process steps, toolkit groups and blog categories. |
| **Integrations** | AI provider (OpenAI or Anthropic), model, API key, token limit and writing voice, plus email (SMTP host, port, encryption, username, password, sender, enquiry address). Each has a **Test** button. Keys and passwords are stored encrypted and never sent back to the browser. |
| **Account & security** | Name, sign-in email, password change (signs out other browsers), and two-factor sign-in with QR code and recovery codes. |

Empty sections disappear from the site rather than showing filler. With no testimonials, for example, the quote block is not rendered.

---

## 3. Environment variables

`.env.example` is the local template. `.env.production.example` has every production value for charlestechy.com filled in except the secrets. Mail and AI values in `.env` are **defaults**: anything saved in Admin → Integrations overrides them without a redeploy.

| Variable | Purpose |
| --- | --- |
| `APP_NAME`, `APP_ENV`, `APP_KEY`, `APP_DEBUG` | Standard Laravel. `APP_DEBUG=false` in production. |
| `APP_URL` | The site's address. Email links, the sitemap, RSS and share images are built from it. |
| `DB_*` | MySQL / MariaDB connection. |
| `SESSION_DRIVER`, `SESSION_LIFETIME`, `SESSION_ENCRYPT`, `SESSION_DOMAIN`, `SESSION_SECURE_COOKIE` | Admin sessions. Production: `SESSION_DOMAIN=charlestechy.com`, `SESSION_SECURE_COOKIE=true`, `SESSION_ENCRYPT=true`. |
| `TRUSTED_PROXIES` | Proxies whose `X-Forwarded-*` headers are trusted, so rate limits see the real visitor IP. |
| `QUEUE_CONNECTION` | `database`. Emails and newsletters are queued. |
| `CACHE_STORE` | `database`. Public pages are cached for 5 minutes, and the cache is cleared on every admin save. |
| `MEDIA_DISK` | `public` (default), or `s3` with the `AWS_*` values for S3 / DigitalOcean Spaces. |
| `MAIL_*` | Default mailer. `log` writes emails to `storage/logs/laravel.log`. |
| `ADMIN_NAME`, `ADMIN_EMAIL`, `ADMIN_PASSWORD` | The owner account created by `db:seed`. Never hard-coded. |
| `CONTACT_RECIPIENT` | Where enquiries are emailed. Defaults to the contact email in Site settings. |
| `CONTACT_RATE_PER_HOUR`, `SUBSCRIBE_RATE_PER_HOUR`, `EVENTS_RATE_PER_MINUTE` | Per-IP limits on the public forms and analytics. |
| `AI_PROVIDER` | `openai` (default) or `anthropic`. |
| `AI_MAX_TOKENS`, `AI_REQUESTS_PER_HOUR` | Output budget per request (default 16000) and the hourly cap per admin (default 20). |
| `OPENAI_API_KEY`, `OPENAI_MODEL`, `OPENAI_REASONING_EFFORT`, `OPENAI_ORGANIZATION`, `OPENAI_TIMEOUT` | OpenAI. The model has no default on purpose. |
| `ANTHROPIC_API_KEY`, `ANTHROPIC_MODEL`, `ANTHROPIC_EFFORT`, `ANTHROPIC_FALLBACKS`, `ANTHROPIC_TIMEOUT` | Anthropic. |
| `INERTIA_SSR_ENABLED` | `false`. Titles, descriptions and share tags are rendered on the server in `resources/views/app.blade.php`, so crawlers see them without a Node SSR process. |

---

## 4. How it fits together

```
app/Http/Controllers/Site     Public pages (Inertia), contact, subscribe, events, CV, sitemap/RSS/robots
app/Http/Controllers/Admin    Admin screens, plus JSON endpoints for uploads and the AI assistant
app/Content                   One class per content type: fields, validation, table row, search
app/Support/SiteSettings.php  Every admin-editable setting: defaults, validation rules, encrypted secrets
app/Support/RuntimeConfig.php Applies the admin's mail and AI settings over .env (per request and per queued job)
app/Services/Ai               AiWriter interface, OpenAiWriter (Responses API), AnthropicWriter (official SDK), prompts
resources/js/pages            Inertia pages: Home, Work, CaseStudy, Blog, BlogPost, Error, admin/*
resources/views/app.blade.php The single HTML shell, with server-rendered <title>, description and Open Graph tags
database/seeders              The real site content and media
```

- **Caching.** Public payloads are cached for 5 minutes. Any model or settings save bumps a version number, so the change shows immediately.
- **Publishing.** `articles:publish-scheduled` runs every minute. The newsletter goes out once per article, in chunks of 200, with one-click unsubscribe. Read time is calculated at 200 words a minute. Slugs are fixed once an article or project leaves draft.
- **Link checking.** `site:check-links` runs every Monday at 06:00. It checks every outbound link the site shows and lists dead ones on the admin Overview. Run it any time with `php artisan site:check-links`.
- **Analytics.** Page views, outbound clicks and CV downloads store only the path, plus the target URL for clicks. No cookies, IPs or user agents are stored.
- **Security.** Login is limited to 5 attempts a minute, two-factor codes to 5 a minute, and AI to 20 requests an hour. The contact and subscribe forms have a honeypot. SVG uploads are refused, images are capped at 5 MB, and the CV must be a PDF. Pages send `nosniff`, `SAMEORIGIN` and referrer-policy headers, and the admin sends `noindex`.

---

## 5. Production: Ubuntu + Nginx + PHP-FPM (single domain)

### 5.1 Packages

```bash
sudo apt update
sudo apt install -y nginx mysql-server supervisor unzip git \
  php8.3-fpm php8.3-cli php8.3-mysql php8.3-mbstring php8.3-xml php8.3-curl php8.3-zip php8.3-bcmath php8.3-intl php8.3-gd
curl -sS https://getcomposer.org/installer | sudo php -- --install-dir=/usr/local/bin --filename=composer
curl -fsSL https://deb.nodesource.com/setup_22.x | sudo bash - && sudo apt install -y nodejs
```

### 5.2 Database

```sql
CREATE DATABASE charlestechy CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'charlestechy'@'localhost' IDENTIFIED BY 'a-long-random-password';
GRANT ALL PRIVILEGES ON charlestechy.* TO 'charlestechy'@'localhost';
```

### 5.3 Install

```bash
sudo mkdir -p /var/www/charlestechy && sudo chown $USER:www-data /var/www/charlestechy
git clone <your-repo> /var/www/charlestechy && cd /var/www/charlestechy

cp .env.production.example .env && nano .env   # fill the REQUIRED values
composer install --no-dev --optimize-autoloader
php artisan key:generate
npm ci && npm run build
php artisan migrate --seed --force
php artisan storage:link
php artisan config:cache && php artisan route:cache && php artisan view:cache

sudo chown -R $USER:www-data storage bootstrap/cache
sudo chmod -R ug+rwx storage bootstrap/cache
```

After signing in, open **Admin → Integrations** to connect email and AI, then **Account & security** to turn on two-factor sign-in.

### 5.4 Nginx

```nginx
server {
    listen 80;
    server_name charlestechy.com www.charlestechy.com;
    root /var/www/charlestechy/public;
    index index.php;
    client_max_body_size 12M;   # uploads: 5 MB images, 10 MB CV

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
    }

    location ~* \.(?:css|js|jpg|jpeg|png|webp|gif|svg|woff2?)$ {
        try_files $uri /index.php?$query_string;
        expires 30d;
        access_log off;
    }

    location ~ /\.(?!well-known) { deny all; }
}
```

```bash
sudo ln -s /etc/nginx/sites-available/charlestechy /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx
sudo apt install -y certbot python3-certbot-nginx && sudo certbot --nginx -d charlestechy.com -d www.charlestechy.com
```

### 5.5 Queue worker (Supervisor)

`/etc/supervisor/conf.d/charlestechy-worker.conf`:

```ini
[program:charlestechy-worker]
command=php /var/www/charlestechy/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
user=www-data
numprocs=1
autostart=true
autorestart=true
stopwaitsecs=3600
redirect_stderr=true
stdout_logfile=/var/www/charlestechy/storage/logs/worker.log
```

```bash
sudo supervisorctl reread && sudo supervisorctl update && sudo supervisorctl start charlestechy-worker
```

The worker reapplies the admin's mail settings before every job, so changes in Integrations apply without a restart.

### 5.6 Scheduler (cron)

```bash
sudo crontab -u www-data -e
# add:
* * * * * cd /var/www/charlestechy && php artisan schedule:run >> /dev/null 2>&1
```

### 5.7 Deploying an update

```bash
cd /var/www/charlestechy
php artisan down
git pull
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force
php artisan config:cache && php artisan route:cache && php artisan view:cache
php artisan queue:restart
php artisan up
```

### 5.8 Backups

Back up the database and `storage/app/public` (uploads and the CV). For example, nightly:

```bash
mysqldump charlestechy | gzip > /backups/charlestechy-$(date +%F).sql.gz
tar czf /backups/uploads-$(date +%F).tgz -C /var/www/charlestechy/storage/app public
```

`APP_KEY` encrypts the saved AI keys and SMTP password. Keep a copy of `.env`. If `APP_KEY` changes, those secrets can no longer be read and must be entered again in Integrations.
