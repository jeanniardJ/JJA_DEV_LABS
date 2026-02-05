import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ["button", "status"];
    static values = {
        publicKey: String,
        subscribeUrl: String
    }

    connect() {
        this.checkSubscription();
    }

    async checkSubscription() {
        if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
            this.updateStatus("Non supporté", "error");
            this.buttonTarget.disabled = true;
            return;
        }

        const registration = await navigator.serviceWorker.ready;
        const subscription = await registration.pushManager.getSubscription();

        if (subscription) {
            this.updateStatus("Activé", "success");
            this.buttonTarget.textContent = "DÉSACTIVER NOTIFICATIONS";
        } else {
            this.updateStatus("Inactif", "info");
            this.buttonTarget.textContent = "ACTIVER NOTIFICATIONS";
        }
    }

    async toggle(event) {
        event.preventDefault();
        
        const registration = await navigator.serviceWorker.ready;
        const subscription = await registration.pushManager.getSubscription();

        if (subscription) {
            await subscription.unsubscribe();
            // TODO: Optional API call to remove from server
            this.updateStatus("Inactif", "info");
            this.buttonTarget.textContent = "ACTIVER NOTIFICATIONS";
            this.dispatchToast("Notifications désactivées", "info");
        } else {
            this.subscribe();
        }
    }

    async subscribe() {
        try {
            const permission = await Notification.requestPermission();
            if (permission !== 'granted') {
                throw new Error("Permission refusée");
            }

            const registration = await navigator.serviceWorker.ready;
            const subscription = await registration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: this.urlBase64ToUint8Array(this.publicKeyValue)
            });

            const response = await fetch(this.subscribeUrlValue, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(subscription)
            });

            if (!response.ok) {
                throw new Error("Erreur serveur lors de l'abonnement");
            }

            this.updateStatus("Activé", "success");
            this.buttonTarget.textContent = "DÉSACTIVER NOTIFICATIONS";
            this.dispatchToast("Notifications activées !", "success");

        } catch (e) {
            console.error(e);
            this.dispatchToast(e.message, "error");
        }
    }

    updateStatus(text, type) {
        this.statusTarget.textContent = text;
        const colors = {
            success: 'text-lab-terminal',
            error: 'text-lab-danger',
            info: 'text-lab-muted'
        };
        this.statusTarget.className = `text-[9px] font-mono uppercase ${colors[type]}`;
    }

    dispatchToast(message, type) {
        window.dispatchEvent(new CustomEvent('toast', {
            detail: { message, type }
        }));
    }

    urlBase64ToUint8Array(base64String) {
        const padding = '='.repeat((4 - base64String.length % 4) % 4);
        const base64 = (base64String + padding)
            .replace(/\-/g, '+')
            .replace(/_/g, '/');

        const rawData = window.atob(base64);
        const outputArray = new Uint8Array(rawData.length);

        for (let i = 0; i < rawData.length; ++i) {
            outputArray[i] = rawData.charCodeAt(i);
        }
        return outputArray;
    }
}
