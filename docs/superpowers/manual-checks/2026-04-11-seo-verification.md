# SEO Verification Checklist — Post-Deploy

Run this after the first production deploy of the SEO work (spec: `docs/superpowers/specs/2026-04-11-seo-optimization-design.md`).

## 1. `robots.txt` production URL

- [ ] Edit `public/robots.txt`, replace `REPLACE-WITH-PROD-URL` with the real production hostname (e.g. `https://bassila-emergence.org`).
- [ ] Redeploy.
- [ ] Visit `{prod}/robots.txt` and confirm the `Sitemap:` line is correct.

## 2. Google Search Console verification

- [ ] Go to https://search.google.com/search-console and add the property with URL-prefix = production URL.
- [ ] Choose the **"HTML tag"** verification method. Google gives a token value.
- [ ] Set `GOOGLE_SITE_VERIFICATION=<token>` in the production `.env` (Dokku: `dokku config:set bassila-emergence GOOGLE_SITE_VERIFICATION=<token>`).
- [ ] Redeploy. The meta tag appears in `<head>` via `<x-seo.meta-tags>`.
- [ ] Back in Search Console, click **"Verify"**. Expect "Property verified".

## 3. Sitemap submission

- [ ] In Search Console → **Sitemaps** → add `sitemap.xml` → submit.
- [ ] Within 24 h, check the "Discovered URLs" count matches the number of live URLs.

## 4. Rich Results validation

- [ ] Open https://search.google.com/test/rich-results
- [ ] Test `{prod}/` → expect **Organization** and **WebSite** detected.
- [ ] Test `{prod}/blog/<any published slug>` → expect **Article** detected.
- [ ] Test `{prod}/profils/<any verified id>` → expect **Person** detected.
- [ ] Test `{prod}/qui-sommes-nous` → expect **BreadcrumbList** detected.
- [ ] Test `{prod}/a-propos-de-bassila` → expect **BreadcrumbList** detected.

## 5. Social share previews

- [ ] Open https://cards-dev.twitter.com/validator (or X's card validator) and test the home URL. Expect a `summary_large_image` card with the default OG image.
- [ ] Open https://developers.facebook.com/tools/debug/ and test the home URL. Expect title, description, og:image.
- [ ] Repeat for a blog post URL (should use the post's featured image).

## 6. Core Web Vitals baseline

- [ ] Open https://pagespeed.web.dev and test `{prod}/` on mobile.
- [ ] Record LCP, CLS, INP, total weight. **Target: LCP < 2.5s, CLS < 0.05, INP < 200ms.**
- [ ] If any metric is red, profile and iterate on image/font tuning. Common culprits:
    - LCP: hero image not served with WebP or not preloaded
    - CLS: a fonts-swap jitter or a missing `width`/`height` somewhere
    - INP: Livewire JS payload too heavy — consider deferring

## 7. Search Console first-week check

- [ ] One week after verification, open **Search Console → Performance**. Confirm impressions are growing.
- [ ] Open **Search Console → Core Web Vitals**. Confirm no red URLs.
- [ ] Open **Search Console → Coverage**. Confirm no private URLs (`/admin`, `/profil/creer`, etc.) appear as "excluded by robots.txt" in a problematic way — that exclusion is *expected*, but flag anything unexpected.

## 8. Post-launch optional improvements (not blockers)

- [ ] If `cwebp` / ImageMagick become available on the dev machine, convert `public/images/home/*.jpg` and `public/images/og-default.png` to WebP and ship a `<picture>` source.
- [ ] Replace `public/images/og-default.png` (currently a PHP-GD-generated brand placeholder) with a designer-made 1200×630 image.
- [ ] Replace the two Unsplash placeholders in `public/images/home/` with real Bassila community photographs.
- [ ] Fill in the `{{-- TODO éditorial --}}` markers in `resources/views/pages/a-propos-de-bassila.blade.php` with community-sourced content (superficie, population, histoire, langues, festivals).
- [ ] Consider adding a few social profile URLs to `config/seo.php` under `socials` so the Organization `sameAs` array becomes populated.
- [ ] If you want behavioural analytics later, install a self-hosted Umami or Plausible on the same Dokku server and add the script tag to `layouts/app.blade.php` — no plan changes needed.
