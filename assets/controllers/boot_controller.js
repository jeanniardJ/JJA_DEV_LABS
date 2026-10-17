import { Controller } from '@hotwired/stimulus';

let bootSequenceStarted = false;

export default class extends Controller {
    static targets = ["screen", "bar", "status"];

    connect() {
        const alreadyBooted = sessionStorage.getItem('jja-lab-booted') === 'true';

        if (alreadyBooted || bootSequenceStarted) {
            this.skipBootSequence();
            return;
        }

        bootSequenceStarted = true;

        if (this.hasScreenTarget) {
            this.screenTarget.classList.remove('hidden');
        }

        this.startBootSequence();
    }

    disconnect() {
        this.clearTimers();
    }

    skipBootSequence() {
        if (this.hasScreenTarget) {
            this.screenTarget.classList.add(
                'hidden',
                'opacity-0',
                'pointer-events-none'
            );
        }

        window.dispatchEvent(new CustomEvent('boot:complete'));
    }

    startBootSequence() {
        sessionStorage.setItem('jja-lab-booted', 'true');

        this.timers = [];

        this.timers.push(
            setTimeout(() => {
                if (this.hasBarTarget) {
                    this.barTarget.style.width = '100%';
                }
            }, 100)
        );

        if (this.hasStatusTarget) {
            this.timers.push(
                setTimeout(() => {
                    this.statusTarget.textContent = 'DÉMARRAGE SERVICES...';
                }, 800)
            );

            this.timers.push(
                setTimeout(() => {
                    this.statusTarget.textContent = 'PRÊT';
                }, 1800)
            );
        }

        this.timers.push(
            setTimeout(() => {
                if (this.hasScreenTarget) {
                    this.screenTarget.classList.add(
                        'hidden',
                        'opacity-0',
                        'pointer-events-none'
                    );

                    window.dispatchEvent(new CustomEvent('boot:complete'));
                }
            }, 2200)
        );
    }

    clearTimers() {
        if (!this.timers) {
            return;
        }

        this.timers.forEach((timer) => clearTimeout(timer));
        this.timers = [];
    }
}
