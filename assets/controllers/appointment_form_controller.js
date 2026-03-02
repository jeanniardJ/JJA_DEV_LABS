import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ["datetime", "submitBtn"];

    connect() {
        window.addEventListener('slot-selected', this.handleSlotSelected.bind(this));
    }

    handleSlotSelected(event) {
        const { datetime, display } = event.detail;
        this.datetimeTarget.value = datetime;
        
        const infoBox = document.getElementById('selected-slot-info');
        const displayEl = document.getElementById('display-datetime');
        
        infoBox.classList.remove('hidden');
        displayEl.textContent = display;
        
        this.submitBtnTarget.disabled = false;
    }

    async submit(event) {
        event.preventDefault();
        
        const formData = new FormData(event.target);
        const data = Object.fromEntries(formData.entries());
        
        this.submitBtnTarget.disabled = true;
        this.submitBtnTarget.classList.add('opacity-50', 'cursor-not-allowed');
        this.submitBtnTarget.textContent = "ENVOI EN COURS...";

        try {
            const response = await fetch('/api/appointments/book', {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(data)
            });

            // If 404, it might be due to URL being different in prod/dev or routing issue
            if (response.status === 404) {
                throw new Error("Point d'entrée introuvable (404).");
            }

            const result = await response.json();

            if (response.ok) {
                document.getElementById('booking-form').classList.add('hidden');
                const successEl = document.getElementById('success-message');
                successEl.classList.remove('hidden');
                successEl.textContent = result.message || "Rendez-vous enregistré.";
                this.dispatchToast(result.message || "Rendez-vous enregistré.", "success");
            } else {
                this.dispatchToast(result.message || result.error || "Échec de la validation.", "error");
                this.enableButton();
            }
        } catch (e) {
            console.error("Submit error:", e);
            this.dispatchToast(e.message || "Impossible de contacter le noyau.", "error");
            this.enableButton();
        }
    }

    enableButton() {
        this.submitBtnTarget.disabled = false;
        this.submitBtnTarget.classList.remove('opacity-50', 'cursor-not-allowed');
        this.submitBtnTarget.textContent = "CONFIRMER LE RDV";
    }

    dispatchToast(message, type) {
        window.dispatchEvent(new CustomEvent('toast', {
            detail: { message, type }
        }));
    }

    disconnect() {
        window.removeEventListener('slot-selected', this.handleSlotSelected);
    }
}
