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
