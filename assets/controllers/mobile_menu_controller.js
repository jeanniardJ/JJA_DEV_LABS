import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ["menu", "icon"];

    connect() {
        this.isOpen = false;
    }

    toggle() {
        this.isOpen = !this.isOpen;
        if (this.isOpen) {
            this.menuTarget.classList.remove('hidden');
            this.iconTarget.setAttribute('data-lucide', 'x');
        } else {
            this.menuTarget.classList.add('hidden');
            this.iconTarget.setAttribute('data-lucide', 'menu');
        }
        // Re-initialize lucide icons if needed, or rely on mutation observer if lucide uses one
        if (window.lucide) {
            window.lucide.createIcons();
        }
    }
}
