import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ["menu", "icon"];

    connect() {
        this.isOpen = false;
        this.closeHandler = this.closeOnOutsideClick.bind(this);
        this.resizeHandler = this.closeOnResize.bind(this);
        document.addEventListener('click', this.closeHandler);
        window.addEventListener('resize', this.resizeHandler);
    }

    disconnect() {
        document.removeEventListener('click', this.closeHandler);
        window.removeEventListener('resize', this.resizeHandler);
    }

    toggle(event) {
        if (event) event.stopPropagation();
        this.isOpen = !this.isOpen;
        this.updateMenuState();
    }

    closeOnOutsideClick(event) {
        if (this.isOpen && !this.element.contains(event.target)) {
            this.isOpen = false;
            this.updateMenuState();
        }
    }

    closeOnResize() {
        if (this.isOpen && window.innerWidth >= 768) { // md breakpoint
            this.isOpen = false;
            this.updateMenuState();
        }
    }

    updateMenuState() {
        if (this.isOpen) {
            this.menuTarget.classList.remove('hidden');
            this.iconTarget.setAttribute('data-lucide', 'x');
        } else {
            this.menuTarget.classList.add('hidden');
            this.iconTarget.setAttribute('data-lucide', 'menu');
        }
        if (window.lucide) {
            window.lucide.createIcons();
        }
    }
}
