import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ["screen", "bar", "status"];

    connect() {
        this.startBootSequence();
    }

    startBootSequence() {
        // Start progress bar animation
        setTimeout(() => {
            if (this.hasBarTarget) {
                this.barTarget.style.width = '100%';
            }
        }, 100);

        // Update text status
        if (this.hasStatusTarget) {
            setTimeout(() => { this.statusTarget.innerText = "DÉMARRAGE SERVICES..." }, 800);
            setTimeout(() => { this.statusTarget.innerText = "PRÊT" }, 1800);
        }

        // Remove boot screen
        setTimeout(() => {
            if (this.hasScreenTarget) {
                this.screenTarget.style.opacity = '0';
                this.screenTarget.style.pointerEvents = 'none';
                
                // Enable scroll on body
                document.body.style.overflowY = 'auto';

                // Optional: Trigger terminal typing if it exists globally or dispatch event
                // window.dispatchEvent(new CustomEvent('boot:complete'));
            }
        }, 2200);
    }
}
