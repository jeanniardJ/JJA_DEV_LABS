import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ["button", "status"];
    static values = {
        publicKey: String,
        subscribeUrl: String
    }

    async connect() {
        console.log("[PWA] Controller connected. Key length:", this.publicKeyValue?.length);
        await this.checkSubscription();
    }

    async checkSubscription() {
        if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
            this.updateStatus("Non supporté", "error");
            this.buttonTarget.disabled = true;
            return;
        }

        try {
            const registration = await navigator.serviceWorker.ready;
            const subscription = await registration.pushManager.getSubscription();

            if (subscription) {
                console.log("[PWA] Subscription found:", subscription.endpoint);
                this.updateStatus("Activé", "success");
                this.buttonTarget.textContent = "DÉSACTIVER NOTIFICATIONS";
                this.buttonTarget.setAttribute('data-active', 'true');
            } else {
                console.log("[PWA] No active subscription found.");
                this.updateStatus("Inactif", "info");
                this.buttonTarget.textContent = "ACTIVER NOTIFICATIONS";
                this.buttonTarget.setAttribute('data-active', 'false');
            }
        } catch (e) {
            console.error("[PWA] Error checking subscription:", e);
        }
    }

    async toggle(event) {
        event.preventDefault();
        const isActive = this.buttonTarget.getAttribute('data-active') === 'true';

        if (isActive) {
            await this.unsubscribe();
        } else {
            await this.subscribe();
        }
    }

    async subscribe() {
        try {
            console.log("[PWA] Starting subscription process...");
            const permission = await Notification.requestPermission();
            if (permission !== 'granted') {
                throw new Error("Permission de notification refusée");
            }

            const registration = await navigator.serviceWorker.ready;
            
            // On force un désabonnement propre au cas où un "fantôme" d'abonnement avec une autre clé existe
            const existingSub = await registration.pushManager.getSubscription();
            if (existingSub) {
                console.log("[PWA] Cleaning up old subscription before re-subscribing...");
                await existingSub.unsubscribe();
            }

            const applicationServerKey = this.urlBase64ToUint8Array(this.publicKeyValue);
            console.log("[PWA] Subscribing with key (bytes):", applicationServerKey.length);

            const subscription = await registration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: applicationServerKey
            });

            const response = await fetch(this.subscribeUrlValue, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(subscription)
            });

            if (!response.ok) throw new Error("Erreur de synchronisation serveur");

            this.updateStatus("Activé", "success");
            this.buttonTarget.textContent = "DÉSACTIVER NOTIFICATIONS";
            this.buttonTarget.setAttribute('data-active', 'true');
            this.dispatchToast("Flux de notifications synchronisé !", "success");

        } catch (e) {
            console.error("[PWA] Subscription failed:", e);
            this.dispatchToast("Échec : " + e.message, "error");
        }
    }

    async unsubscribe() {
        try {
            const registration = await navigator.serviceWorker.ready;
            const subscription = await registration.pushManager.getSubscription();

            if (subscription) {
                await subscription.unsubscribe();
            }

            this.updateStatus("Inactif", "info");
            this.buttonTarget.textContent = "ACTIVER NOTIFICATIONS";
            this.buttonTarget.setAttribute('data-active', 'false');
            this.dispatchToast("Notifications désactivées", "info");
        } catch (e) {
            console.error("[PWA] Unsubscribe failed:", e);
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
