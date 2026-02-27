import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values = {
        targetUrl: String,
        severity: String,
        count: Number
    }

    connect() {
        // Moment WOW: we could add some extra animation or tracking here
        console.log(`Conversion tunnel active for ${this.targetUrlValue} with severity ${this.severityValue}`);
        
        if (window.lucide && typeof window.lucide.createIcons === 'function') {
            window.lucide.createIcons({ icons: window.lucideIcons });
        }
    }
}
