import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ["idle", "verifying", "scanning", "completed", "input", "error", "verifyError", "statusLabel", "percentage", "progressBar", "urlDisplay", "urlDisplayScanning", "tokenDisplay", "tokenDisplayDNS", "downloadBtn"];
    static values = {
        submitUrl: String,
        statusUrl: String,
        verifyUrl: String,
        downloadUrl: String
    }

    start() {
        const url = this.inputTarget.value;
        if (!url) {
            this.showError("Veuillez entrer une URL.");
            return;
        }

        this.hideError();
        this.submit(url);
    }

    async submit(url) {
        try {
            const response = await fetch(this.submitUrlValue, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ url: url })
            });

            const data = await response.json();

            if (!response.ok) {
                this.showError(data.error || "Une erreur est survenue.");
                return;
            }

            this.scanId = data.scan_id;
            this.token = data.token;
            
            this.urlDisplayTarget.textContent = url;
            this.urlDisplayScanningTarget.textContent = url;
            this.tokenDisplayTarget.textContent = data.token;
            this.tokenDisplayDNSTarget.textContent = data.token;

            this.showState("verifying");
        } catch (e) {
            this.showError("Impossible de contacter l'API.");
        }
    }

    async verify() {
        this.hideVerifyError();
        try {
            const url = this.verifyUrlValue.replace('PLACEHOLDER', this.scanId);
            const response = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ method: 'file' })
            });

            const data = await response.json();

            if (!response.ok) {
                this.showVerifyError(data.error || "La vérification a échoué.");
                return;
            }

            this.showState("scanning");
            this.pollStatus();
        } catch (e) {
            this.showVerifyError("Erreur lors de la vérification.");
        }
    }

    pollStatus() {
        this.pollingInterval = setInterval(async () => {
            try {
                const url = this.statusUrlValue.replace('PLACEHOLDER', this.scanId);
                const response = await fetch(url);
                const data = await response.json();

                if (!response.ok) {
                    this.stopPolling();
                    this.showError("Échec de la récupération du statut.");
                    return;
                }

                this.updateProgress(data);

                if (data.status === 'completed') {
                    this.stopPolling();
                    setTimeout(() => this.showState("completed"), 1000);
                }
            } catch (e) {
                this.stopPolling();
                this.showError("Lien rompu avec le moteur de scan.");
            }
        }, 1000);
    }

    updateProgress(data) {
        this.percentageTarget.textContent = `${data.percentage}%`;
        this.progressBarTarget.style.width = `${data.percentage}%`;
        this.statusLabelTarget.textContent = data.current_step;
    }

    download() {
        const url = this.downloadUrlValue.replace('PLACEHOLDER', this.scanId);
        window.location.href = url;
    }

    stopPolling() {
        if (this.pollingInterval) {
            clearInterval(this.pollingInterval);
        }
    }

    showState(state) {
        this.idleTarget.classList.add('hidden');
        this.verifyingTarget.classList.add('hidden');
        this.scanningTarget.classList.add('hidden');
        this.completedTarget.classList.add('hidden');

        this[state + 'Target'].classList.remove('hidden');
        
        if (window.lucide) {
            window.lucide.createIcons();
        }
    }

    showError(msg) {
        this.errorTarget.textContent = msg;
        this.errorTarget.classList.remove('hidden');
    }

    hideError() {
        this.errorTarget.classList.add('hidden');
    }

    showVerifyError(msg) {
        this.verifyErrorTarget.textContent = msg;
        this.verifyErrorTarget.classList.remove('hidden');
    }

    hideVerifyError() {
        this.verifyErrorTarget.classList.add('hidden');
    }

    copyToken() {
        navigator.clipboard.writeText(this.token).then(() => {
            // Success feedback could be added here
        });
    }

    reset() {
        this.stopPolling();
        this.inputTarget.value = "";
        this.showState("idle");
    }

    disconnect() {
        this.stopPolling();
    }
}
