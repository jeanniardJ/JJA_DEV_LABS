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
        this.submitBtnTarget.textContent = "ENVOI EN COURS...";

        try {
            const response = await fetch('/api/appointments/book', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (response.ok) {
                document.getElementById('booking-form').classList.add('hidden');
                document.getElementById('success-message').classList.remove('hidden');
            } else {
                alert(result.error || "Une erreur est survenue.");
                this.submitBtnTarget.disabled = false;
                this.submitBtnTarget.textContent = "CONFIRMER LE RDV";
            }
        } catch (e) {
            alert("Impossible de contacter le serveur.");
            this.submitBtnTarget.disabled = false;
            this.submitBtnTarget.textContent = "CONFIRMER LE RDV";
        }
    }

    disconnect() {
        window.removeEventListener('slot-selected', this.handleSlotSelected);
    }
}
