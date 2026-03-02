import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['dropdown'];
    static values = {
        url: String
    };

    connect() {
        this.closeHandler = this.closeExternal.bind(this);
        document.addEventListener('click', this.closeHandler);
    }

    disconnect() {
        document.removeEventListener('click', this.closeHandler);
    }

    toggle(event) {
        event.stopPropagation();
        const isHidden = this.dropdownTarget.classList.contains('pointer-events-none');
        
        if (isHidden) {
            this.open();
        } else {
            this.close();
        }
    }

    open() {
        this.dropdownTarget.classList.remove('opacity-0', 'translate-y-2', 'pointer-events-none');
        this.dropdownTarget.classList.add('opacity-100', 'translate-y-0');
    }

    close() {
        this.dropdownTarget.classList.add('opacity-0', 'translate-y-2', 'pointer-events-none');
        this.dropdownTarget.classList.remove('opacity-100', 'translate-y-0');
    }

    closeExternal(event) {
        if (!this.element.contains(event.target)) {
            this.close();
        }
    }

    async readAll(event) {
        event.preventDefault();
        
        // On récupère l'URL soit de l'attribut data-notifications-url-value du bouton,
        // soit de la valeur de configuration du contrôleur.
        const url = event.currentTarget.dataset.notificationsUrlValue || this.urlValue;
        
        if (!url) {
            console.error("No URL provided for readAll action");
            return;
        }

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (response.ok) {
                window.location.reload();
            } else {
                console.error("Server responded with an error", response.status);
            }
        } catch (e) {
            console.error("Failed to mark notifications as read", e);
        }
    }
}
