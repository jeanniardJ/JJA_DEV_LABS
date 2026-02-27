import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['loader'];

    connect() {
        // Show loader on connect (initial page load)
        this.showLoader();
        // Hide loader when the page is fully loaded (e.g., after all assets are loaded)
        window.addEventListener('load', this.hideLoader.bind(this));
        // Fallback for cases where 'load' event might not fire as expected or for fast loads
        setTimeout(() => {
            if (!this.loaderTarget.classList.contains('hidden')) {
                this.hideLoader();
            }
        }, 3000); // Hide after 3 seconds if not already hidden
    }

    disconnect() {
        window.removeEventListener('load', this.hideLoader.bind(this));
    }

    showLoader() {
        this.loaderTarget.classList.remove('hidden');
    }

    hideLoader() {
        this.loaderTarget.classList.add('hidden');
    }
}
