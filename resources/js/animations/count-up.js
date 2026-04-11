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
