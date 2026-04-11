# UI Animations — Design

**Date:** 2026-04-12
**Branch:** `001-bassila-network-platform`
**Status:** Design approved — ready for implementation plan

## Context

The SEO optimization work from 2026-04-11 (`docs/superpowers/specs/2026-04-11-seo-optimization-design.md`) delivered a solid technical foundation (sitemap, structured data, Core Web Vitals budget, content pages) but the public pages feel static: no entrance animations, no hover polish beyond basic Tailwind classes, no visual feedback on the stats bar. Add a tight, SEO-safe animation layer that raises the perceived quality of the site without touching the Core Web Vitals budget we just earned.

## Goals

1. Add **entrance animations** (scroll-reveal + stagger) to every section of the home and the two new static content pages.
2. Animate the **stats bar** with a count-up from 0 to the displayed value.
3. Add **hover micro-interactions** (lift, arrow glide) on cards and secondary CTAs.
4. A short **hero entrance** animation for the subtitle and CTA buttons, with the H1 and hero image kept untouched so LCP is unaffected.
5. Honour **`prefers-reduced-motion`** throughout.
6. **Zero regressions** on `PerformanceHintsTest`, the SEO test suite, or manual Core Web Vitals measurements.

Non-goals:
- Parallax effects (deferred — require before/after LCP measurements).
- Inter-page `wire:transition` fades (deferred — separate concern with its own INP risk profile).
- Animations on `/blog`, `/annuaire`, blog show, profile show (out of scope; these are utilitarian pages where animation buys less and costs the same).
- Any third-party library (GSAP, Framer Motion, Lottie) — vanilla CSS + ES modules only.
- Automated Lighthouse/Playwright testing of animations — manual verification only.

## Design principles

The 6 rules the design obeys throughout (derived from Core Web Vitals and Google's indexing constraints):

1. **Only `transform` and `opacity` are animated** — no layout properties (`width`, `height`, `top`, `left`, `margin`, `padding`). These two are compositor-friendly and don't cause reflow/repaint, so CLS and INP stay flat.
2. **Content must exist in the initial HTML** — animations start from `opacity: 0` / `translateY(20px)` but the DOM is already populated when Google renders the page. Nothing is lazy-injected by JS.
3. **The LCP element is never animated during its first paint** — the hero `<h1>` and hero `<img>` stay completely static.
4. **`prefers-reduced-motion: reduce` disables everything** — mandatory a11y, enforced via a single `@media` block that overrides `animation`, `transition`, `opacity`, and `transform`.
5. **No external animation library** — Tailwind utilities + a custom `animations.css` file + a ~1.2 KB gzip vanilla JS module. Total JS budget: under 2 KB gzip.
6. **Animated elements reserve their space** — cards and sections that translate on reveal already occupy their final layout footprint; the 20 px translate is a paint-only offset. Zero CLS contribution.

## Architecture

```
resources/
├── css/
│   ├── app.css                      (append: @import './animations.css';)
│   └── animations.css               (NEW — all animation styles in one place)
└── js/
    ├── app.js                       (append: import './animations/index.js';)
    └── animations/
        ├── index.js                 (NEW — entry point, auto-init on DOMContentLoaded + livewire:navigated)
        ├── scroll-reveal.js         (NEW — IntersectionObserver logic)
        └── count-up.js              (NEW — stats bar count-up logic)
```

**Principles:**

- **CSS-first**: everything that can be done in pure CSS (`@keyframes`, `transition`, `:hover`) stays in CSS. JS only handles the two things that truly need it: IntersectionObserver-based reveal and count-up numeric tween.
- **No dependency additions**: `package.json` stays unchanged. Vite bundles the new modules with the existing pipeline.
- **Auto-init idempotent**: `animations/index.js` re-scans the DOM on `livewire:navigated`. Already-revealed elements are `unobserve`d after their first intersection so they don't double-register.
- **Budget ceiling**: total added JS < 2 KB gzip (measurable via `vite build`).

## CSS animations (`resources/css/animations.css`)

All utility classes live here. The file is imported from `resources/css/app.css`.

```css
/* ============================================================
   Animations — safe for SEO / Core Web Vitals
   Only transform + opacity are animated (compositor-friendly).
============================================================ */

/* --- Reveal on scroll (JS adds .is-visible when intersecting) --- */
.anim-reveal {
    opacity: 0;
    transform: translateY(20px);
    transition: opacity 500ms ease-out, transform 500ms ease-out;
    will-change: opacity, transform;
}

.anim-reveal.is-visible {
    opacity: 1;
    transform: translateY(0);
}

/* Stagger children — index set via inline style --i */
.anim-reveal-stagger > * {
    opacity: 0;
    transform: translateY(20px);
    transition: opacity 500ms ease-out, transform 500ms ease-out;
    transition-delay: calc(var(--i, 0) * 80ms);
    will-change: opacity, transform;
}

.anim-reveal-stagger.is-visible > * {
    opacity: 1;
    transform: translateY(0);
}

/* --- Hero entrance (pure CSS, plays on page load, no JS) --- */
@keyframes fade-up {
    from { opacity: 0; transform: translateY(16px); }
    to   { opacity: 1; transform: translateY(0); }
}

.anim-hero-subtitle {
    animation: fade-up 700ms ease-out 150ms both;
}
.anim-hero-cta {
    animation: fade-up 700ms ease-out 300ms both;
}
/* .anim-hero-title and the hero <img> are NOT animated — LCP-protected. */

/* --- Hover micro-interactions --- */
.hover-lift {
    transition: transform 200ms ease-out, box-shadow 200ms ease-out, border-color 200ms ease-out;
}
.hover-lift:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px -8px rgba(10, 22, 40, 0.15);
}

.hover-arrow svg,
.hover-arrow .arrow {
    transition: transform 200ms ease-out;
}
.hover-arrow:hover svg,
.hover-arrow:hover .arrow {
    transform: translateX(4px);
}

/* --- Respect prefers-reduced-motion (override everything above) --- */
@media (prefers-reduced-motion: reduce) {
    .anim-reveal,
    .anim-reveal-stagger > *,
    .anim-hero-subtitle,
    .anim-hero-cta,
    .hover-lift,
    .hover-arrow svg,
    .hover-arrow .arrow {
        animation: none !important;
        transition: none !important;
        opacity: 1 !important;
        transform: none !important;
    }
}
```

**Notes:**
- `will-change` is declared on reveal utilities but only takes effect for the short duration of the transition — acceptable memory cost.
- `box-shadow` and `border-color` on `.hover-lift` trigger paint but not reflow — safe.
- The `prefers-reduced-motion` block is intentionally last so it wins specificity ties.

## JavaScript

### `resources/js/animations/scroll-reveal.js`

```js
/**
 * Scroll-triggered reveal animation via IntersectionObserver.
 * Adds .is-visible to elements with .anim-reveal or .anim-reveal-stagger
 * when they scroll into the viewport.
 *
 * One-shot: elements stay visible after first reveal (we unobserve).
 */
export function initScrollReveal(root = document) {
    const elements = root.querySelectorAll('.anim-reveal, .anim-reveal-stagger');
    if (elements.length === 0) return;

    // Fallback for ancient browsers: reveal everything immediately.
    if (typeof IntersectionObserver === 'undefined') {
        elements.forEach(el => el.classList.add('is-visible'));
        return;
    }

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            }
        });
    }, {
        rootMargin: '0px 0px -10% 0px', // trigger slightly before fully in view
        threshold: 0.1,
    });

    elements.forEach(el => observer.observe(el));
}
```

### `resources/js/animations/count-up.js`

```js
/**
 * Animates numeric values from 0 to their target on first intersection.
 * The target is parsed from the element's initial textContent — so the
 * server MUST render the final value in the HTML (SEO-critical).
 *
 * Usage: <span class="anim-count">1 247</span>
 */
export function initCountUp(root = document) {
    const elements = root.querySelectorAll('.anim-count');
    if (elements.length === 0) return;

    const prefersReduced = window.matchMedia?.('(prefers-reduced-motion: reduce)').matches;
    if (prefersReduced || typeof IntersectionObserver === 'undefined') return;

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (!entry.isIntersecting) return;
            animateValue(entry.target);
            observer.unobserve(entry.target);
        });
    }, { threshold: 0.5 });

    elements.forEach(el => observer.observe(el));
}

function animateValue(el) {
    const originalText = el.textContent.trim();
    const target = parseInt(originalText.replace(/\s/g, ''), 10);
    if (Number.isNaN(target) || target === 0) return;

    const duration = 1200;
    const startTime = performance.now();
    const formatter = new Intl.NumberFormat('fr-FR');

    function tick(now) {
        const elapsed = now - startTime;
        const progress = Math.min(elapsed / duration, 1);
        const eased = 1 - Math.pow(1 - progress, 3); // easeOutCubic
        const value = Math.round(target * eased);
        el.textContent = formatter.format(value);
        if (progress < 1) requestAnimationFrame(tick);
    }

    el.textContent = '0';
    requestAnimationFrame(tick);
}
```

### `resources/js/animations/index.js`

```js
import { initScrollReveal } from './scroll-reveal.js';
import { initCountUp } from './count-up.js';

function initAll() {
    initScrollReveal();
    initCountUp();
}

// Initial page load
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAll);
} else {
    initAll();
}

// Re-init after Livewire SPA navigation (wire:navigate)
document.addEventListener('livewire:navigated', initAll);
```

### Wiring into `resources/js/app.js`

Append one line:

```js
import './animations/index.js';
```

**Notes:**
- `unobserve` prevents double-registration after `livewire:navigated` because already-revealed elements no longer carry an observer.
- Count-up format matches the existing `number_format()` server-side output (French locale uses non-breaking spaces as thousands separators).
- The HTML always renders the final value — Google sees `1 247`, the user sees the count-up animation from 0. SEO-safe by construction.

## View integration

### `resources/views/welcome.blade.php`

| Element | Class to add |
|---|---|
| Hero subtitle `<p>` (line ~52) | `anim-hero-subtitle` |
| Hero CTA container (line ~55) | `anim-hero-cta` |
| Stats bar 4 count `<div>`s (line ~80, conditional) | `anim-count` |
| "Mission" `<section>` | `anim-reveal` |
| "Piliers" `<section>` | `anim-reveal` |
| "Piliers" inner 3-column grid | `anim-reveal-stagger` + `style="--i: 0/1/2"` on each child card |
| "Comment ça marche" `<section>` | `anim-reveal` |
| "Comment ça marche" inner 3-step grid | `anim-reveal-stagger` + `--i` per step |
| "Secteurs représentés" `<section>` (conditional) | `anim-reveal` |
| "Témoignages" `<section>` | `anim-reveal` |
| "Témoignages" inner 3-card grid | `anim-reveal-stagger` + `--i` per card |
| "Blog récent" `<section>` | `anim-reveal` |
| "Blog récent" inner card grid | `anim-reveal-stagger` + `--i` per card |
| "Profils vérifiés" `<section>` | `anim-reveal` |
| "Profils vérifiés" inner card grid | `anim-reveal-stagger` + `--i` per card |
| "Newsletter" `<section>` | `anim-reveal` |
| "CTA final" `<section>` | `anim-reveal` |
| Blog card `<article>` | `hover-lift` |
| Profile card `<a>` | `hover-lift` |
| Pillar cards | `hover-lift` |
| "Tous les articles →" link | `hover-arrow` |
| "Voir l'annuaire complet →" link | `hover-arrow` |
| "Rejoindre le réseau →" link (mission section) | `hover-arrow` |

**Explicitly not touched:** hero H1 (`<h1>`), hero `<img>`, nav, footer (other than any hover polish later), page background.

### `resources/views/pages/a-propos-de-bassila.blade.php`

| Element | Class to add |
|---|---|
| `<header>` with H1 + subtitle | `anim-reveal` |
| `<article class="prose">` (the whole body) | `anim-reveal` — one block, everything appears together on scroll |
| "Faites partie du réseau" CTA section | `anim-reveal` |

### `resources/views/pages/qui-sommes-nous.blade.php`

| Element | Class to add |
|---|---|
| `<header>` | `anim-reveal` |
| "Notre mission" `<section>` | `anim-reveal` |
| "Nos valeurs" `<section>` | `anim-reveal` |
| "Nos valeurs" 2×2 grid | `anim-reveal-stagger` + `--i: 0/1/2/3` on the 4 cards |
| "Comment ça marche" `<section>` | `anim-reveal` |
| "Contact" `<section>` | `anim-reveal` |

## Testing strategy

### Extended `tests/Feature/Seo/PerformanceHintsTest.php`

Three new tests guarding the LCP invariants the animations must not break:

```php
it('home still has fetchpriority=high on hero after animation work', function () {
    $html = get('/')->getContent();
    expect($html)->toContain('fetchpriority="high"');
});

it('home hero h1 has no animation class (LCP-protected)', function () {
    $html = get('/')->getContent();
    preg_match('/<h1\b[^>]*>/i', $html, $m);
    $h1Tag = $m[0] ?? '';
    expect($h1Tag)->not->toContain('anim-');
});

it('home hero img has no animation class (LCP-protected)', function () {
    $html = get('/')->getContent();
    // Find the <img> inside the hero section by its fetchpriority="high" marker.
    preg_match('/<img\b[^>]*fetchpriority="high"[^>]*>/i', $html, $m);
    $imgTag = $m[0] ?? '';
    expect($imgTag)->not->toContain('anim-');
});
```

### New `tests/Feature/Seo/AnimationsTest.php`

```php
it('scroll-reveal classes are present on home sections', function () {
    $html = get('/')->getContent();
    // At least 6 sections should carry anim-reveal.
    expect(substr_count($html, 'anim-reveal'))->toBeGreaterThan(5);
});

it('qui-sommes-nous has scroll-reveal classes', function () {
    $html = get('/qui-sommes-nous')->getContent();
    expect($html)->toContain('anim-reveal');
});

it('a-propos-de-bassila has scroll-reveal classes', function () {
    $html = get('/a-propos-de-bassila')->getContent();
    expect($html)->toContain('anim-reveal');
});

it('count-up target values are rendered in initial HTML (SEO-safe)', function () {
    \App\Models\User::factory()->count(5)->create();
    $html = get('/')->getContent();
    // The server must render the final numeric value inside the anim-count element.
    expect($html)->toMatch('/<div[^>]*anim-count[^>]*>\s*\d+\s*<\/div>/');
});

it('all existing SEO tests still pass (regression net)', function () {
    // Ran via --filter=Seo as part of the suite, no additional assertion here.
    expect(true)->toBeTrue();
})->skip('Covered by running php artisan test --filter=Seo');
```

### Not tested automatically (documented manual checks)

- Visual smoothness when scrolling `/`, `/qui-sommes-nous`, `/a-propos-de-bassila` in dev
- DevTools Performance panel: zero "forced reflow" warnings during a scroll-through recording
- DevTools Rendering tab → "Emulate CSS media feature prefers-reduced-motion" → confirm all animations are disabled
- Lighthouse on `/`: CLS ≤ 0.05 (identical to pre-animation baseline), LCP unchanged, INP ≤ 200 ms
- `vite build` output: confirm the new JS modules add ≤ 2 KB gzip to the main chunk

## Open questions

None. All branching decisions are resolved:

- Scope: home + `/qui-sommes-nous` + `/a-propos-de-bassila`. No blog, no directory, no profile.
- Approach: #2 Standard (scroll-reveal + stagger + hero entrance + count-up + hover micro-interactions).
- Stack: vanilla CSS + ES modules, zero new dependencies.
- Parallax, `wire:transition`, line-draw SVGs, accordéons: explicitly deferred.
- Testing: HTML-assertion tests only (presence of classes, LCP protection, server-rendered count-up targets). No visual regression tooling.

## Implementation order (rough)

1. `resources/css/animations.css` with all utility classes + `prefers-reduced-motion` block.
2. Import `animations.css` from `app.css`.
3. `resources/js/animations/scroll-reveal.js` (unit of logic).
4. `resources/js/animations/count-up.js` (unit of logic).
5. `resources/js/animations/index.js` (entry point + init hooks).
6. Import `animations/index.js` from `app.js`.
7. Wire classes into `welcome.blade.php`.
8. Wire classes into `pages/qui-sommes-nous.blade.php` and `pages/a-propos-de-bassila.blade.php`.
9. Extend `PerformanceHintsTest` with LCP-protection assertions.
10. Add `AnimationsTest`.
11. Run `php artisan test --filter=Seo` — must be green.
12. Run `vite build` — confirm JS budget.
13. Manual smoke-test in a browser with DevTools Performance + reduced-motion emulation.
