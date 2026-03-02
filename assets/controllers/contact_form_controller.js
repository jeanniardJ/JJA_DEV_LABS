import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['submitButton'];

    connect() {
        // Rendu manuel de Turnstile (nécessaire avec Turbo et render=explicit)
        this.renderTurnstile();
    }

    disconnect() {
        // Nettoyage pour éviter les fuites de mémoire
        if (this.widgetId && typeof window.turnstile !== 'undefined') {
            window.turnstile.remove(this.widgetId);
        }
    }

    renderTurnstile() {
        const container = this.element.querySelector('.cf-turnstile');
        if (!container) return;

        // Si l'API est chargée, on rend. Sinon on attend le chargement.
        if (typeof window.turnstile !== 'undefined') {
            // Nettoyage avant rendu pour éviter les doublons
            container.innerHTML = '';
            
            this.widgetId = window.turnstile.render(container, {
                sitekey: container.dataset.sitekey,
                theme: 'dark',
                callback: (token) => {
                    // Optionnel: on peut synchroniser un champ caché ici si besoin
                    const symfonyInput = this.element.querySelector('input[type="hidden"][name*="[turnstile]"]');
                    if (symfonyInput) symfonyInput.value = token;
                }
            });
        } else {
            // Réessayer après un court délai si le script n'est pas encore là
            setTimeout(() => this.renderTurnstile(), 100);
        }
    }

    handleSubmit(event) {
        if (this.hasSubmitButtonTarget) {
            this.submitButtonTarget.disabled = true;
            this.submitButtonTarget.classList.add('opacity-50', 'cursor-not-allowed');
            const span = this.submitButtonTarget.querySelector('span');
            if (span) span.textContent = 'ENVOI_EN_COURS...';
        }
    }

    onPostSubmit(event) {
        const { success } = event.detail;
        if (!success) {
            this.enableButton();
            if (typeof window.turnstile !== 'undefined' && this.widgetId) {
                window.turnstile.reset(this.widgetId);
            }
        }
    }

    enableButton() {
        if (this.hasSubmitButtonTarget) {
            this.submitButtonTarget.disabled = false;
            this.submitButtonTarget.classList.remove('opacity-50', 'cursor-not-allowed');
            const span = this.submitButtonTarget.querySelector('span');
            if (span) span.textContent = 'EXÉCUTER_ENVOI';
        }
    }
}
