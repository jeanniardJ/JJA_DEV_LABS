import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['submitButton'];

    connect() {
        // Form is connected
    }

    onPostSubmit(event) {
        // If the response is successful (Redirect or 200)
        if (event.detail.success) {
            // We can handle success here if we don't do a full redirect
            // But the controller currently redirects
        }
    }

    // This can be used if we add data-action="submit->contact-form#handleSubmit"
    handleSubmit() {
        if (this.hasSubmitButtonTarget) {
            this.submitButtonTarget.disabled = true;
            this.submitButtonTarget.classList.add('opacity-50', 'cursor-not-allowed');
            this.submitButtonTarget.querySelector('span').textContent = 'ENVOI_EN_COURS...';
        }
    }
}
