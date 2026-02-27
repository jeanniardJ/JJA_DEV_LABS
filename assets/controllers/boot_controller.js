import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ["screen", "bar", "status"];

    connect() {
        if (sessionStorage.getItem('jja-lab-booted')) {
            this.skipBootSequence();
        } else {
            this.startBootSequence();
        }
    }

    skipBootSequence() {
        if (this.hasScreenTarget) {
            this.screenTarget.classList.add('hidden', 'opacity-0', 'pointer-events-none');
            // Still dispatch event for components that wait for boot
            window.dispatchEvent(new CustomEvent('boot:complete'));
        }
    }

    startBootSequence() {
        sessionStorage.setItem('jja-lab-booted', 'true');
        // Start progress bar animation
        setTimeout(() => {
            if (this.hasBarTarget) {
                this.barTarget.style.width = '100%';
            }
        }, 100);

        // Update text status
        if (this.hasStatusTarget) {
            setTimeout(() => { this.statusTarget.textContent = "DÉMARRAGE SERVICES..." }, 800);
            setTimeout(() => { this.statusTarget.textContent = "PRÊT" }, 1800);
        }

        // Remove boot screen
        setTimeout(() => {
            if (this.hasScreenTarget) {
                this.screenTarget.classList.add('opacity-0', 'pointer-events-none');
                
                // Optional: Trigger terminal typing
                window.dispatchEvent(new CustomEvent('boot:complete'));
            }
        }, 2200);
    }
}
