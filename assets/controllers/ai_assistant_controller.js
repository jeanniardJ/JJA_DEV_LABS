import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ["button", "draftArea", "loader", "container", "sendButton"];
    static values = {
        generateUrl: String,
        sendUrl: String
    }

    async generate(event) {
        event.preventDefault();
        
        // UI State
        this.buttonTarget.disabled = true;
        this.loaderTarget.classList.remove('hidden');
        this.draftAreaTarget.classList.add('opacity-50');
        this.containerTarget.classList.remove('hidden');

        try {
            const response = await fetch(this.generateUrlValue, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (data.error) {
                throw new Error(data.error);
            }

            // Success: Update textarea
            this.draftAreaTarget.value = data.draft;
            this.draftAreaTarget.classList.remove('opacity-50');
            this.dispatchToast("Brouillon généré", "success");
            
            // Adjust height
            this.draftAreaTarget.style.height = 'auto';
            this.draftAreaTarget.style.height = (this.draftAreaTarget.scrollHeight) + 'px';

        } catch (e) {
            console.error(e);
            this.dispatchToast(e.message, "error");
        } finally {
            this.buttonTarget.disabled = false;
            this.loaderTarget.classList.add('hidden');
        }
    }

    async send(event) {
        event.preventDefault();
        
        const content = this.draftAreaTarget.value;
        if (!content) return;

        this.sendButtonTarget.disabled = true;
        this.sendButtonTarget.textContent = "ENVOI EN COURS...";

        try {
            const response = await fetch(this.sendUrlValue, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ content: content })
            });

            const data = await response.json();

            if (data.error) {
                throw new Error(data.error);
            }

            this.dispatchToast("Email envoyé !", "success");
            this.containerTarget.classList.add('hidden');

        } catch (e) {
            console.error(e);
            this.dispatchToast(e.message, "error");
        } finally {
            this.sendButtonTarget.disabled = false;
            this.sendButtonTarget.textContent = "ENVOYER LA RÉPONSE";
        }
    }

    dispatchToast(message, type) {
        window.dispatchEvent(new CustomEvent('toast', {
            detail: { message, type }
        }));
    }
}
