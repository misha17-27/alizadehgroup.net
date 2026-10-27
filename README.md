# alizadehgroup.net

Corporate website of **Alizadeh Group** (Baku) with an admin panel.
Plain PHP 7.4+ (no framework, no Composer), MySQL/MariaDB on cPanel, SQLite for local development.
Languages: **AZ** (default, `/…`) and **EN** (`/en/…`).

## Pages

| AZ | EN |
|---|---|
| `/` — home (slider, request cards, about, why us, services, partners, request form) | `/en/` |
| `/haqqimizda` | `/en/about` |
| `/xidmetler` | `/en/services` |
| `/xidmetler/{slug}` — 4 service pages | `/en/services/{slug}` |
| `/partnyorlar` | `/en/partners` |
| `/elaqe` — contacts, request form, map | `/en/contact` |

Also: `/sitemap.xml`, `/robots.txt`, 404 page. Request form posts to `/api/request` (AJAX, works without JS too).

## Admin panel

`/admin/` — sections: Dashboard, Requests (status, notes, attachments, CSV export), Slider, Request cards,
About us, Why us, Services (texts per language, images, target groups, offers, process, FAQ, extra block),
Partners, Texts (every label on the site), Pages & SEO, Settings (contacts, socials, notification e-mail,
Google Analytics, Cloudflare Turnstile), Users (admin / editor roles), Profile.
Interface language: Azerbaijani or Russian. Content is edited per language with the **AZ / EN** switch in the top bar.

## Project structure

```
.
├── index.php               public front controller (routes, request API, sitemap)
├── install.php             one-time installer (DB tables, initial content, first admin)
├── .htaccess               URL rewriting + blocks app/ and storage/
├── .cpanel.yml             cPanel deployment tasks
├── admin/
│   └── index.php           admin entry point (?section=…)
├── app/                    application code — not web-accessible
│   ├── bootstrap.php       config, constants, session, autoload of the parts below
│   ├── config.sample.php   example of app/config.php (the real one is created by install.php)
│   ├── core/               db.php, helpers.php, uploads.php, icons.php
│   ├── site/               content.php (routes, languages, repositories), request.php (form)
│   ├── admin/              admin.php (auth, roles, render), crud.php, forms.php, sections/, lang/ (az, ru)
│   ├── database/           migrate.php (schema + seeding), seed.json (initial AZ/EN content)
│   ├── views/site/         layout, pages, partials/
│   ├── views/admin/        layout, login, list, form, dashboard, requests…
│   └── dev-router.php      router for `php -S` (local only)
├── assets/                 css/, js/, img/ (site images, logos, partners/)
├── uploads/                images uploaded in the admin (server only, scripts disabled)
└── storage/                logs, sessions, request attachments, SQLite (server only, not web-accessible)
```

## First installation on cPanel

1. **cPanel → MySQL® Databases**: create a database and a user, add the user to the database with *ALL PRIVILEGES*.
2. **cPanel → Git™ Version Control → Create**: Clone URL `https://github.com/misha17-27/alizadehgroup.net.git`,
   repository path `repositories/alizadehgroup.net`.
3. Open the repository → **Pull or Deploy** → **Deploy HEAD Commit**. Files are copied to `public_html/yeni/`
   (see `DEPLOYPATH` in `.cpanel.yml`).
4. Open `https://alizadehgroup.net/yeni/install.php`, enter the database details and the first administrator.
5. Log in at `https://alizadehgroup.net/yeni/admin/` and check **Settings** (notification e-mail etc.).

The installer locks itself once `app/config.php` exists.

## Updating the site

```bash
git add -A && git commit -m "…" && git push
```

Then in cPanel: **Git™ Version Control → Manage → Pull or Deploy → Update from Remote → Deploy HEAD Commit**.
Deploys never overwrite `app/config.php`, `uploads/` or `storage/`.

## Going live on the main domain

Change `DEPLOYPATH` in `.cpanel.yml` to `/home/alizadehgroup/public_html/` after backing up and removing the old
WordPress files, deploy, and either move `app/config.php` from `public_html/yeni/app/` or run `install.php` again.

## Local development

```bash
php -S 127.0.0.1:8000 -t . app/dev-router.php
```

Open `http://127.0.0.1:8000/install.php` and choose **SQLite**.

## Notes

- Request notifications use PHP `mail()`; on cPanel the sender is `noreply@<domain>`. Delivery problems are logged in `storage/logs/mail.log`.
- Attachments from the supply form are stored in `storage/requests/` and are downloadable only from the admin panel.
- Spam protection: CSRF token, honeypot field, minimum fill time, 5 requests / 10 min per IP; optional Cloudflare Turnstile (Settings).
- Admin login: 6 failed attempts per IP → 15-minute block; passwords are hashed with `password_hash()`.
