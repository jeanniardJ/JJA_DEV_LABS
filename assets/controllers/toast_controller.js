import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    connect() {
        this.container = document.getElementById('toast-container');
        
        // Listen for custom 'toast' events on window
        window.addEventListener('toast', (event) => {
            this.show(event.detail.message, event.detail.type || 'info');
        });
    }

    show(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `pointer-events-auto min-w-[250px] p-4 rounded-sm border shadow-xl flex items-center gap-3 animate-fade-in-up transition-all duration-500`;
        
        const colors = {
            success: 'bg-lab-terminal/10 border-lab-terminal text-lab-terminal',
            error: 'bg-lab-danger/10 border-lab-danger text-lab-danger',
            info: 'bg-lab-primary/10 border-lab-primary text-lab-primary'
        };

        const icons = {
            success: 'check-circle',
            error: 'alert-triangle',
            info: 'info'
        };

        toast.classList.add(...colors[type].split(' '));
        
        toast.innerHTML = `
            <i data-lucide="${icons[type]}" class="w-5 h-5"></i>
            <span class="text-xs font-mono font-bold uppercase tracking-tight">${message}</span>
        `;

        this.container.appendChild(toast);
        
        if (window.lucide && typeof window.lucide.createIcons === 'function') {
            window.lucide.createIcons({ icons: window.lucideIcons });
        }

        // Auto remove
        setTimeout(() => {
            toast.classList.add('opacity-0', 'translate-x-full');
            setTimeout(() => toast.remove(), 500);
        }, 4000);
    }
}
