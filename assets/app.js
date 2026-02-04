import './stimulus_bootstrap.js';
/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import './styles/app.css';
import * as lucide from 'lucide';

// Expose lucide globally for Stimulus controllers
window.lucide = lucide;

lucide.createIcons({ icons: lucide.icons });

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
