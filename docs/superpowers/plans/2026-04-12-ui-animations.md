# UI Animations Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add an SEO-safe animation layer (scroll-reveal, stagger, count-up, hero entrance, hover micro-interactions) to the home and two static content pages without impacting Core Web Vitals.

**Architecture:** Pure CSS animations in `resources/css/animations.css` (imported from `app.css`), plus three vanilla ES modules under `resources/js/animations/` for IntersectionObserver-based reveal and a count-up tween. Zero external dependencies. The LCP hero element (H1 + `<img>`) stays untouched. A `prefers-reduced-motion` media query disables everything for users who opt out.

**Tech Stack:** CSS3 (`@keyframes`, `transition`, `@media (prefers-reduced-motion)`), ES modules bundled via Vite (already configured), Tailwind v4 (existing), Livewire 4 (for `livewire:navigated` event hook).

**Spec:** `docs/superpowers/specs/2026-04-12-ui-animations-design.md`

---

## File Map

**New files:**
- `resources/css/animations.css` — all animation utilities + `prefers-reduced-motion` override
- `resources/js/animations/scroll-reveal.js` — `initScrollReveal(root)` exported
- `resources/js/animations/count-up.js` — `initCountUp(root)` exported
- `resources/js/animations/index.js` — entry point, re-inits on `livewire:navigated`
- `tests/Feature/Seo/AnimationsTest.php` — presence + SEO-safety assertions

**Modified files:**
- `resources/css/app.css` — add one `@import './animations.css';` line
- `resources/js/app.js` — add one `import './animations/index.js';` line
- `resources/views/welcome.blade.php` — apply classes to ~15 sections/elements
- `resources/views/pages/a-propos-de-bassila.blade.php` — apply classes to 3 blocks
- `resources/views/pages/qui-sommes-nous.blade.php` — apply classes to 6 blocks
- `tests/Feature/Seo/PerformanceHintsTest.php` — add 3 LCP-protection tests

---

## Task 1: Create `animations.css`

**Files:**
- Create: `resources/css/animations.css`

- [ ] **Step 1: Write the file**

Create `resources/css/animations.css` with the following full contents:

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

/* --- Respect prefers-reduced-motion (overrides everything above) --- */
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

- [ ] **Step 2: Import from `app.css`**

Add the import at the top of `resources/css/app.css`, right after the existing `@import 'tailwindcss';` line:

```css
@import 'tailwindcss';
@import './animations.css';
```

- [ ] **Step 3: Commit**

```bash
git add resources/css/animations.css resources/css/app.css
git commit -m "feat(animations): add animations.css utilities and reduced-motion override"
```

---

## Task 2: Create `scroll-reveal.js` module

**Files:**
- Create: `resources/js/animations/scroll-reveal.js`

- [ ] **Step 1: Write the module**

Create `resources/js/animations/scroll-reveal.js` with:

```js
/**
 * Scroll-triggered reveal animation via IntersectionObserver.
 * Adds .is-visible to elements with .anim-reveal or .anim-reveal-stagger
 * when they scroll into the viewport.
 *
 * One-shot: elements stay visible after first reveal (unobserved).
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
        rootMargin: '0px 0px -10% 0px',
        threshold: 0.1,
    });

    elements.forEach(el => observer.observe(el));
}
```

- [ ] **Step 2: Commit**

```bash
git add resources/js/animations/scroll-reveal.js
git commit -m "feat(animations): add scroll-reveal IntersectionObserver module"
```

---

## Task 3: Create `count-up.js` module

**Files:**
- Create: `resources/js/animations/count-up.js`

- [ ] **Step 1: Write the module**

Create `resources/js/animations/count-up.js` with:

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
    // Strip whitespace (fr-FR thousands separators use non-breaking spaces).
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

- [ ] **Step 2: Commit**

```bash
git add resources/js/animations/count-up.js
git commit -m "feat(animations): add count-up tween module"
```

---

## Task 4: Create `animations/index.js` entry point and wire into `app.js`

**Files:**
- Create: `resources/js/animations/index.js`
- Modify: `resources/js/app.js`

- [ ] **Step 1: Write the entry point**

Create `resources/js/animations/index.js` with:

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

- [ ] **Step 2: Import from `app.js`**

The current `resources/js/app.js` is a single line: `import './bootstrap';`. Append the animations import:

```js
import './bootstrap';
import './animations/index.js';
```

- [ ] **Step 3: Commit**

```bash
git add resources/js/animations/index.js resources/js/app.js
git commit -m "feat(animations): wire animations init into app.js with livewire:navigated hook"
```

---

## Task 5: Wire `welcome.blade.php` — hero

**Files:**
- Modify: `resources/views/welcome.blade.php`

- [ ] **Step 1: Add `anim-hero-subtitle` to hero subtitle**

Find the hero subtitle paragraph (around line 51) and add the class:

```blade
<p class="text-white/75 text-lg leading-relaxed mb-10 max-w-xl anim-hero-subtitle">
    Retrouvez d'anciens camarades, développez votre réseau professionnel et contribuez à l'histoire de votre communauté d'origine.
</p>
```

- [ ] **Step 2: Add `anim-hero-cta` to hero CTA container**

Find the hero CTA wrapper (around line 54) and add the class:

```blade
<div class="flex flex-wrap gap-4 anim-hero-cta">
```

- [ ] **Step 3: Verify — hero H1 and hero `<img>` must NOT have animation classes**

Search the hero section (lines 11-111) manually: the `<h1>` on line 47 and the `<img>` on line 28 must have NO `anim-*` classes. They are the LCP elements.

- [ ] **Step 4: Commit**

```bash
git add resources/views/welcome.blade.php
git commit -m "feat(animations): wire hero subtitle and CTA entrance on welcome"
```

---

## Task 6: Wire `welcome.blade.php` — stats bar count-up

**Files:**
- Modify: `resources/views/welcome.blade.php`

- [ ] **Step 1: Add `anim-count` class to the 4 stats numbers**

Inside the `@if(collect($stats)->sum() > 0)` block (around lines 78-104), each of the 4 stats `<div>` elements renders a numeric value. Add `anim-count` to each:

```blade
<div class="text-white font-bold text-3xl anim-count">
    {{ number_format($stats['members']) }}
</div>
```

Repeat for the `profiles`, `countries`, and `posts` divs (4 total).

- [ ] **Step 2: Commit**

```bash
git add resources/views/welcome.blade.php
git commit -m "feat(animations): add count-up class to home stats bar"
```

---

## Task 7: Wire `welcome.blade.php` — scroll-reveal on sections + stagger on grids

**Files:**
- Modify: `resources/views/welcome.blade.php`

- [ ] **Step 1: Add `anim-reveal` to each content section's opening `<section>` tag**

Walk through the sections in order and add `anim-reveal` to the `class` attribute:

- Mission section: `<section class="bg-white py-20 anim-reveal">`
- 3 Pillars section: `<section class="bg-gray-50 py-20 anim-reveal">`
- "Comment ça marche" section: `<section class="bg-white py-20 anim-reveal">`
- "Secteurs représentés" section (inside `@if($sectors->isNotEmpty())`): `<section class="bg-gray-50 py-16 anim-reveal">`
- "Témoignages" section: `<section class="bg-white py-20 anim-reveal">`
- "Blog récent" section: `<section class="bg-white py-20 anim-reveal">`
- "Profils vérifiés" section: `<section class="bg-gray-50 py-20 anim-reveal">`
- "Newsletter" section: `<section class="bg-[#0A1628] py-16 anim-reveal">`
- Final CTA section: `<section class="bg-[#0066CC] py-20 anim-reveal">`

- [ ] **Step 2: Add `anim-reveal-stagger` to grids with multiple cards**

Three grids should animate their children in a staggered sequence. Add `anim-reveal-stagger` to the grid container AND `style="--i: N"` to each child where N is the 0-based index.

**3 Pillars grid** — the wrapper is currently `<div class="grid md:grid-cols-3 gap-6">`, change to `<div class="grid md:grid-cols-3 gap-6 anim-reveal-stagger">`. Then on each of the 3 inner `<div class="bg-white border border-gray-200 p-8">` (pillars 1, 2, 3), add `style="--i: 0;"`, `style="--i: 1;"`, `style="--i: 2;"` respectively. Pillar 2 already has `bg-[#0066CC]` styling — preserve it.

**"Comment ça marche" 3 steps grid** — same treatment: add `anim-reveal-stagger` to the `<div class="grid md:grid-cols-3 gap-10">` and `style="--i: 0/1/2;"` on each of the 3 `<div class="text-center">` step cards.

**Témoignages 3 cards grid** — same treatment: add `anim-reveal-stagger` to the `<div class="grid md:grid-cols-3 gap-6">` and `style="--i: 0/1/2;"` on each `<div class="border border-gray-200 p-8">` inside the `@foreach` loop. Since it's a loop, use `style="--i: {{ $loop->index }};"`.

**Blog récent cards grid** — `<div class="grid md:grid-cols-3 gap-6">` becomes `<div class="grid md:grid-cols-3 gap-6 anim-reveal-stagger">` and inside the `@foreach($recentPosts as $post)`, each `<article class="border border-gray-200 group">` becomes `<article class="border border-gray-200 group hover-lift" style="--i: {{ $loop->index }};">` (note: `hover-lift` is also added here, see step 4).

**Profils vérifiés cards grid** — `<div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">` becomes `<div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4 anim-reveal-stagger">` and each `<a class="bg-white border border-gray-200 p-5 flex items-start gap-4 hover:border-[#0066CC] transition group">` gets `hover-lift` and `style="--i: {{ $loop->index }};"`.

- [ ] **Step 3: Add `hover-lift` to pillar cards**

The 3 pillar cards (inside the 3-pillars grid) each carry `border border-gray-200` (or `bg-[#0066CC]` for the middle one). Add `hover-lift` to each of their class attributes.

- [ ] **Step 4: Add `hover-arrow` to "see more" links**

The welcome page has several "→" text links. Add `hover-arrow` to these:

- Mission section "Rejoindre le réseau →" link (`<a class="inline-flex items-center gap-2 text-[#0066CC] font-semibold text-sm hover:underline">`) → add `hover-arrow`.
- "Tous les articles →" link (`<a class="text-[#0066CC] text-sm font-semibold hover:underline shrink-0">`) → add `hover-arrow`.
- "Voir l'annuaire complet →" link → add `hover-arrow`.
- Pillar cards "Explorer l'annuaire →", "Lire les articles →", "Rejoindre →" links → add `hover-arrow` to each.

Note: `hover-arrow` animates any `svg` or `.arrow` element inside the link. Several of these links use an SVG arrow (`<svg class="w-4 h-4" ...>`); the class will apply automatically via the CSS descendant selector. For text-only arrows (the `&rarr;` HTML entity), the `hover-arrow` class has no effect — that's fine, it's a no-op.

- [ ] **Step 5: Test**

```bash
php artisan test --filter=HomePageTest
```

Expected: 18 tests pass. The existing home tests do not assert on animation classes, so they should still pass.

- [ ] **Step 6: Commit**

```bash
git add resources/views/welcome.blade.php
git commit -m "feat(animations): wire scroll-reveal, stagger and hover-lift across welcome sections"
```

---

## Task 8: Wire `pages/a-propos-de-bassila.blade.php`

**Files:**
- Modify: `resources/views/pages/a-propos-de-bassila.blade.php`

- [ ] **Step 1: Add `anim-reveal` to the three main blocks**

Find each of these and add `anim-reveal` to the class attribute:

1. The `<header class="mb-12">` block (contains H1) → `<header class="mb-12 anim-reveal">`
2. The `<article class="prose prose-lg max-w-none">` block → `<article class="prose prose-lg max-w-none anim-reveal">`
3. The final CTA `<section class="mt-16 bg-[#0066CC] text-white p-10">` → `<section class="mt-16 bg-[#0066CC] text-white p-10 anim-reveal">`

- [ ] **Step 2: Add `hover-arrow` to the "Explorer les secteurs" link**

Find the link with `text-[#0066CC] font-semibold hover:underline` in the Économie section and add `hover-arrow` to it.

- [ ] **Step 3: Test**

```bash
php artisan test --filter=StaticPagesTest
```

Expected: 4 tests pass (all existing static-page assertions still hold).

- [ ] **Step 4: Commit**

```bash
git add resources/views/pages/a-propos-de-bassila.blade.php
git commit -m "feat(animations): wire scroll-reveal on a-propos-de-bassila"
```

---

## Task 9: Wire `pages/qui-sommes-nous.blade.php`

**Files:**
- Modify: `resources/views/pages/qui-sommes-nous.blade.php`

- [ ] **Step 1: Add `anim-reveal` to each section**

Add `anim-reveal` to the following top-level blocks inside the content div:

- `<header class="mb-12">` → `<header class="mb-12 anim-reveal">`
- `<section class="prose prose-lg max-w-none mb-12">` (Notre mission) → add `anim-reveal`
- `<section class="mb-12">` (Nos valeurs) — there are multiple sections with `class="mb-12"`; this is the one containing the H2 "Nos valeurs" → add `anim-reveal`
- `<section class="mb-12">` (Comment ça marche) → add `anim-reveal`
- `<section class="mb-12">` (Contact) → add `anim-reveal`

- [ ] **Step 2: Add `anim-reveal-stagger` to the values grid**

Inside the "Nos valeurs" section, the `<div class="grid md:grid-cols-2 gap-6">` contains 4 `<div class="border border-gray-200 p-6">` cards. Change the grid to:

```blade
<div class="grid md:grid-cols-2 gap-6 anim-reveal-stagger">
```

And add `style="--i: 0;"`, `style="--i: 1;"`, `style="--i: 2;"`, `style="--i: 3;"` to the 4 inner cards in order.

- [ ] **Step 3: Add `hover-lift` to the 4 values cards**

Same 4 cards from step 2: append `hover-lift` to each one's class attribute.

- [ ] **Step 4: Test**

```bash
php artisan test --filter=StaticPagesTest
```

Expected: 4 tests pass.

- [ ] **Step 5: Commit**

```bash
git add resources/views/pages/qui-sommes-nous.blade.php
git commit -m "feat(animations): wire scroll-reveal and stagger on qui-sommes-nous"
```

---

## Task 10: Add `AnimationsTest` + extend `PerformanceHintsTest`

**Files:**
- Create: `tests/Feature/Seo/AnimationsTest.php`
- Modify: `tests/Feature/Seo/PerformanceHintsTest.php`

- [ ] **Step 1: Create `AnimationsTest.php`**

Create `tests/Feature/Seo/AnimationsTest.php` with:

```php
<?php

use App\Models\User;
use function Pest\Laravel\get;

it('home has multiple scroll-reveal sections', function () {
    $html = get('/')->getContent();
    expect(substr_count($html, 'anim-reveal'))->toBeGreaterThan(5);
});

it('home hero has entrance animation classes', function () {
    $html = get('/')->getContent();
    expect($html)->toContain('anim-hero-subtitle')
        ->and($html)->toContain('anim-hero-cta');
});

it('home stats bar has count-up class when platform has data', function () {
    User::factory()->count(5)->create();
    $html = get('/')->getContent();
    expect($html)->toContain('anim-count');
});

it('count-up elements render final numeric value in HTML (SEO-safe)', function () {
    User::factory()->count(5)->create();
    $html = get('/')->getContent();
    // The <div class="... anim-count">N</div> must contain a digit, not "0".
    expect($html)->toMatch('/<div[^>]*anim-count[^>]*>\s*\d+\s*<\/div>/');
});

it('qui-sommes-nous has scroll-reveal', function () {
    $html = get('/qui-sommes-nous')->getContent();
    expect($html)->toContain('anim-reveal');
});

it('a-propos-de-bassila has scroll-reveal', function () {
    $html = get('/a-propos-de-bassila')->getContent();
    expect($html)->toContain('anim-reveal');
});
```

- [ ] **Step 2: Extend `PerformanceHintsTest.php` with LCP-protection tests**

At the end of `tests/Feature/Seo/PerformanceHintsTest.php`, append these 2 new tests:

```php
it('home hero h1 has no animation class (LCP-protected)', function () {
    $html = get('/')->getContent();
    preg_match('/<h1\b[^>]*>/i', $html, $m);
    $h1Tag = $m[0] ?? '';
    expect($h1Tag)->not->toContain('anim-');
});

it('home hero img with fetchpriority=high has no animation class (LCP-protected)', function () {
    $html = get('/')->getContent();
    preg_match('/<img\b[^>]*fetchpriority="high"[^>]*>/i', $html, $m);
    $imgTag = $m[0] ?? '';
    expect($imgTag)->not->toBe('')
        ->and($imgTag)->not->toContain('anim-');
});
```

- [ ] **Step 3: Run tests to verify pass**

```bash
php artisan test --filter="AnimationsTest|PerformanceHintsTest"
```

Expected: all tests pass. `AnimationsTest` has 6 tests. `PerformanceHintsTest` now has 6 tests (4 existing + 2 new).

- [ ] **Step 4: Commit**

```bash
git add tests/Feature/Seo/AnimationsTest.php tests/Feature/Seo/PerformanceHintsTest.php
git commit -m "test(animations): add AnimationsTest and LCP-protection assertions"
```

---

## Task 11: Full regression check + build budget verification

**Files:** *(verification only — no code changes)*

- [ ] **Step 1: Run the full SEO test suite**

```bash
php artisan test --filter=Seo
```

Expected: **56 tests pass** (50 existing SEO tests + 6 new animation tests). If any test fails, fix the referenced task before moving on.

- [ ] **Step 2: Run the full test suite to ensure no regression**

```bash
php artisan test
```

Expected: **241 tests pass** (235 previously + 6 new), with the **same 8 pre-existing failures** that were already documented (Contact, Directory/Search, Newsletter, EmailVerification — all unrelated to animations).

- [ ] **Step 3: Run `vite build` to check JS budget**

```bash
npm run build 2>&1 | tail -20
```

Expected: a success message from Vite. Inspect the reported asset sizes — the main JS bundle should grow by **under 2 KB gzip** compared to pre-animation baseline. If it grew more, investigate: likely an unintended import or a missing tree-shake.

- [ ] **Step 4: No commit — this is a verification step.**

If everything is green, proceed to the manual verification step.

---

## Task 12: Manual smoke-test checklist

**Files:** *(manual — no code changes)*

- [ ] **Step 1: Start dev server**

```bash
npm run dev &
php artisan serve
```

- [ ] **Step 2: Visit `http://localhost:8000/` and scroll slowly**

Expected visual behaviour:
- Hero H1 appears immediately (no animation).
- Hero subtitle fades up ~150 ms after page load.
- Hero CTA buttons fade up ~300 ms after page load.
- Stats bar numbers count up from 0 to their final value when they enter the viewport.
- Each content section below the hero fades in + translates up by 20 px as you scroll it into view.
- The 3 pillar cards, 3 blog cards, 3 profile cards, 3 testimonials, 3 "comment ça marche" steps reveal with an 80 ms stagger between each.
- Hovering a blog/profile/pillar card lifts it 2 px with a soft shadow.
- Hovering a "→" link with an SVG arrow slides the arrow 4 px to the right.

- [ ] **Step 3: Open DevTools → Performance → record a scroll-through**

Expected: no "forced reflow" warnings in the log. All layout costs near zero during scroll.

- [ ] **Step 4: Open DevTools → Rendering tab → enable "Emulate CSS media feature prefers-reduced-motion: reduce"**

Reload the page. Expected: all animations disabled. Content appears immediately. Stats show the final value from the start (no count-up). Hovering cards has no transform or shadow effect.

- [ ] **Step 5: Run Lighthouse on `/`**

Expected metrics:
- **LCP** ≤ 2.5 s (unchanged from pre-animation)
- **CLS** ≤ 0.05 (unchanged)
- **INP** ≤ 200 ms (unchanged)

If any regressed, the likely culprit is an animation on the LCP element. Check for `anim-` classes on the hero H1 or `<img>`.

- [ ] **Step 6: Visit `/qui-sommes-nous` and `/a-propos-de-bassila`**

Expected: scroll-reveal animations play on each section. The H1 and subtitle of each page are inside the `anim-reveal` header, so they fade in together at page load (because they're above the fold and immediately intersecting).

- [ ] **Step 7: Stop dev servers**

```bash
# Stop php artisan serve (Ctrl+C) and kill the background npm run dev
kill %1 2>/dev/null
```

---

## Self-Review

**Spec coverage** — each spec section mapped to tasks:

- §Architecture → Tasks 1-4 (files created in the right places)
- §CSS animations → Task 1 (full file)
- §JavaScript → Tasks 2, 3, 4 (3 modules + entry point)
- §View integration (welcome) → Tasks 5, 6, 7 (hero, stats, sections)
- §View integration (a-propos-de-bassila) → Task 8
- §View integration (qui-sommes-nous) → Task 9
- §Testing strategy (AnimationsTest + PerformanceHintsTest extension) → Task 10
- §Testing strategy (full regression + vite build budget) → Task 11
- §Manual verification → Task 12 (checklist)
- §Design principles 1-6 (LCP-protected, reduced-motion, no layout animation, no external lib) → encoded in Task 1 (CSS) and Task 10 (tests that guard them)

All spec sections have at least one task.

**Placeholder scan** — no "TBD", "TODO (implement later)" markers in the plan. Every code step contains the literal code to write.

**Type consistency check** — function names are consistent across tasks: `initScrollReveal()` (Task 2) → called in `initAll()` (Task 4). `initCountUp()` (Task 3) → called in `initAll()` (Task 4). CSS class names consistent between Task 1 (definition) and Tasks 5-9 (usage): `anim-reveal`, `anim-reveal-stagger`, `anim-hero-subtitle`, `anim-hero-cta`, `anim-count`, `hover-lift`, `hover-arrow`.

**Dependency order**: Tasks 1→2→3→4 (foundation). Then Tasks 5-9 can run in any order (they touch different views). Task 10 depends on Tasks 5-9 (tests assert on the wired classes). Task 11 depends on everything. Task 12 is manual and depends on Task 11.

---

## Execution Handoff

**Plan complete and saved to `docs/superpowers/plans/2026-04-12-ui-animations.md`. Two execution options:**

**1. Subagent-Driven (recommended)** — Fresh subagent per task, review between tasks, fast iteration. Best for this plan because tasks 5-9 can parallelize cleanly.

**2. Inline Execution** — Execute tasks in this session using executing-plans, batch execution with checkpoints.

**Which approach?**
