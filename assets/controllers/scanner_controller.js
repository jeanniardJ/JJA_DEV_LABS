import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ["idle", "verifying", "scanning", "completed", "input", "error", "verifyError", "statusLabel", "percentage", "progressBar", "urlDisplay", "urlDisplayScanning", "tokenDisplay", "tokenDisplayDNS", "downloadBtn", "conversion"];
    static values = {
        submitUrl: String,
        statusUrl: String,
        verifyUrl: String,
        downloadUrl: String,
        conversionUrl: String,
        csrfToken: String
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
                headers: { 
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': this.csrfTokenValue
                },
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
        console.log("Démarrage de la vérification pour ID:", this.scanId);
        
        try {
            const url = this.verifyUrlValue.replace('PLACEHOLDER', this.scanId);
            const response = await fetch(url, {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': this.csrfTokenValue
                },
                body: JSON.stringify({ method: 'file' })
            });

            const data = await response.json();
            console.log("Réponse vérification:", data);

            if (!response.ok) {
                this.showVerifyError(data.error || "La vérification a échoué.");
                return;
            }

            console.log("Passage à l'état : scanning");
            this.showState("scanning");
            this.pollStatus();
        } catch (e) {
            console.error("Erreur lors de la vérification JS:", e);
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
                    await this.loadConversionCta();
                    setTimeout(() => this.showState("completed"), 1000);
                }
            } catch (e) {
                this.stopPolling();
                this.showError("Lien rompu avec le moteur de scan.");
            }
        }, 1000);
    }

    async loadConversionCta() {
        try {
            const url = this.conversionUrlValue.replace('PLACEHOLDER', this.scanId);
            const response = await fetch(url);
            if (response.ok && response.status !== 204) {
                const html = await response.text();
                this.conversionTarget.innerHTML = html;
            }
        } catch (e) {
            console.error("Failed to load conversion CTA", e);
        }
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
        
        if (window.lucide && typeof window.lucide.createIcons === 'function') {
            window.lucide.createIcons({ icons: window.lucideIcons });
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

        copyToken(event) {
            if (!this.token) return;
    
            const btn = event.currentTarget;
            if (!btn) return;
    
            navigator.clipboard.writeText(this.token).then(() => {
                const originalIcon = btn.innerHTML;
                
                // Disable button during feedback
                btn.disabled = true;
                
                // Global toast notification (Priority)
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: {
                        message: "TOKEN COPIÉ",
                        type: "success"
                    }
                }));
    
                            // Temporary visual feedback on button
                            btn.innerHTML = '<i data-lucide="check" class="w-3 h-3 text-lab-terminal"></i>';
                            if (window.lucide && typeof window.lucide.createIcons === 'function') {
                                window.lucide.createIcons({ icons: window.lucideIcons });
                            }
                            
                            setTimeout(() => {
                                btn.innerHTML = originalIcon;
                                btn.disabled = false;
                                if (window.lucide && typeof window.lucide.createIcons === 'function') {
                                    window.lucide.createIcons({ icons: window.lucideIcons });
                                }
                            }, 2000);
                
            })
    .catch(err => {
            console.error('Failed to copy token: ', err);
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
