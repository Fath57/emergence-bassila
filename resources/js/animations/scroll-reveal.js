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
