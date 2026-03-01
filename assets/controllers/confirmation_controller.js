import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values = {
        message: String
    }

    connect() {
        this.modal = document.getElementById('confirm-modal');
        this.modalBox = document.getElementById('confirm-modal-box');
        this.messageEl = document.getElementById('confirm-modal-message');
        this.btnOk = document.getElementById('confirm-modal-ok');
        this.btnCancel = document.getElementById('confirm-modal-cancel');
    }

    confirm(event) {
        event.preventDefault();
        const form = event.currentTarget;
        
        // Configurer le message
        this.messageEl.textContent = this.messageValue || "Voulez-vous vraiment exécuter cette action ?";
        
        // Afficher le modal
        this.modal.classList.remove('hidden');
        setTimeout(() => {
            this.modal.classList.remove('opacity-0');
            this.modalBox.classList.remove('scale-95');
            this.modalBox.classList.add('scale-100');
        }, 10);

        // Gérer les clics
        const handleOk = () => {
            this.close();
            form.submit();
        };

        const handleCancel = () => {
            this.close();
        };

        // Une seule exécution (once: true) pour éviter les accumulations d'event listeners
        this.btnOk.onclick = handleOk;
        this.btnCancel.onclick = handleCancel;
    }

    close() {
        this.modal.classList.add('opacity-0');
        this.modalBox.classList.remove('scale-100');
        this.modalBox.classList.add('scale-95');
        
        setTimeout(() => {
            this.modal.classList.add('hidden');
        }, 300);
    }
}
