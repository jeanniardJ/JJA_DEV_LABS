import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ["link"];

    connect() {
        this.observer = new IntersectionObserver(this.handleIntersection.bind(this), {
            rootMargin: '-20% 0px -70% 0px'
        });

        this.linkTargets.forEach(link => {
            const anchor = link.dataset.anchor;
            if (anchor && anchor.startsWith('#')) {
                const element = document.querySelector(anchor);
                if (element) {
                    this.observer.observe(element);
                }
            }
        });
    }

    disconnect() {
        if (this.observer) {
            this.observer.disconnect();
        }
    }

    handleIntersection(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const id = entry.target.id;
                this.setActive(`#${id}`);
            }
        });
    }

    setActive(anchor) {
        this.linkTargets.forEach(link => {
            if (link.dataset.anchor === anchor) {
                link.classList.add('text-lab-primary');
                link.setAttribute('aria-current', 'location');
                // Target the span for number color change if needed
                const span = link.querySelector('span');
                if (span) span.classList.add('text-lab-cyan');
            } else {
                link.classList.remove('text-lab-primary');
                link.removeAttribute('aria-current');
                const span = link.querySelector('span');
                if (span) span.classList.remove('text-lab-cyan');
            }
        });
    }
}
