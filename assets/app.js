import './stimulus_bootstrap.js';
/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import './styles/app.css';
import * as lucide from 'lucide';

// Expose lucide and icons globally for Stimulus controllers
window.lucide = lucide;
window.lucideIcons = lucide.icons;

const refreshIcons = () => {
    lucide.createIcons({ icons: lucide.icons });
};

// Initial load
refreshIcons();

// Re-run on Turbo navigation and Frame loads
document.addEventListener('turbo:load', refreshIcons);
document.addEventListener('turbo:frame-load', refreshIcons);

// --- SERVICE WORKER REGISTRATION ---
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/service-worker.js')
            .catch(error => {
                // Silent fail in production or log to monitoring
            });
    });
}

console.log('This log comes from assets/app.js - welcome to AssetMapper! 🎉');
