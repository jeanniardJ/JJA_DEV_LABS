import { Controller } from '@hotwired/stimulus';
import Sortable from 'sortablejs';

export default class extends Controller {
    static targets = ["column"];
    static values = {
        updateUrl: String
    }

    connect() {
        this.columnTargets.forEach(column => {
            new Sortable(column, {
                group: 'leads',
                animation: 150,
                ghostClass: 'bg-lab-primary/10',
                dragClass: 'opacity-50',
                onEnd: (evt) => {
                    this.updateStatus(evt);
                }
            });
        });
    }

    async updateStatus(evt) {
        const leadId = evt.item.dataset.id;
        const newStatus = evt.to.dataset.status;
        const oldStatus = evt.from.dataset.status;

        if (newStatus === oldStatus) return;

        const url = this.updateUrlValue.replace('ID_PLACEHOLDER', leadId);

        try {
            const response = await fetch(url, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ status: newStatus })
            });

            if (!response.ok) {
                throw new Error("Failed to update status");
            }

            // Optional: Show toast or update counters
            console.log(`Lead ${leadId} moved to ${newStatus}`);
            this.dispatchToast("Statut mis à jour", "success");
            this.refreshCounters();
        } catch (e) {
            console.error(e);
            this.dispatchToast("Échec de la mise à jour", "error");
            // Revert DOM change? Or show error
            window.location.reload();
        }
    }

    dispatchToast(message, type) {
        window.dispatchEvent(new CustomEvent('toast', {
            detail: { message, type }
        }));
    }

    refreshCounters() {
        this.columnTargets.forEach(column => {
            const count = column.children.length;
            const counter = column.parentElement.querySelector('span.text-lab-muted');
            if (counter) {
                counter.textContent = count;
            }
        });
    }

    showDetails(evt) {
        const leadId = evt.currentTarget.dataset.id;
        const panel = document.getElementById('lead-detail-panel');
        const overlay = document.getElementById('lead-detail-overlay');
        const frame = document.getElementById('lead_detail');

        // Update frame src to load content
        frame.src = `/admin/leads/${leadId}`;

        // Show panel
        panel.classList.remove('translate-x-full');
        
        // Show overlay
        overlay.classList.remove('opacity-0', 'pointer-events-none');
        overlay.classList.add('opacity-100', 'pointer-events-auto');
        
        document.body.style.overflow = 'hidden';
    }

    hideDetails() {
        const panel = document.getElementById('lead-detail-panel');
        const overlay = document.getElementById('lead-detail-overlay');
        
        panel.classList.add('translate-x-full');
        
        // Hide overlay
        overlay.classList.remove('opacity-100', 'pointer-events-auto');
        overlay.classList.add('opacity-0', 'pointer-events-none');
        
        document.body.style.overflow = '';
    }
}
