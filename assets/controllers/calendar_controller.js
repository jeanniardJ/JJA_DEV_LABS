import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ["loader"];

    connect() {
        console.log("Calendar controller connected");
        // Vérifie si FullCalendar est disponible globalement
        if (typeof FullCalendar === 'undefined' || typeof FullCalendar.Calendar === 'undefined') {
            console.error("FullCalendar not loaded globally. Check CDN script.");
            // Tenter de rendre le loader visible ou afficher un message d'erreur
            if (this.hasLoaderTarget) {
                this.loaderTarget.classList.remove('hidden'); // S'assurer qu'il est visible
                this.loaderTarget.innerHTML = '<span class="text-lab-danger font-mono">ERREUR : CALENDRIER NON CHARGÉ</span>';
            }
            return;
        }
        this.initCalendar();
    }

    initCalendar() {
        const calendarEl = document.getElementById('calendar');
        if (!calendarEl) {
            console.error("Element #calendar not found");
            return;
        }
        console.log("Initializing calendar on element:", calendarEl);

        // Get color from CSS variable for consistency
        const primaryColor = getComputedStyle(document.documentElement)
            .getPropertyValue('--color-lab-primary')
            .trim() || '#00ffc3';

        const calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'timeGridWeek',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'timeGridWeek,timeGridDay'
            },
            slotMinTime: '09:00:00',
            slotMaxTime: '18:00:00',
            allDaySlot: false,
            slotDuration: '00:15:00',
            selectable: true,
            locale: 'fr',
            events: (info, successCallback, failureCallback) => {
                const date = info.start.toISOString().split('T')[0];
                fetch(`/api/appointments/slots?date=${date}`)
                    .then(response => response.json())
                    .then(data => {
                        const events = data.map(slot => ({
                            title: 'Disponible',
                            start: slot.datetime,
                            end: new Date(new Date(slot.datetime).getTime() + 15 * 60000).toISOString(),
                            display: 'background',
                            backgroundColor: primaryColor
                        }));
                        successCallback(events);
                        if (this.hasLoaderTarget) {
                            this.loaderTarget.classList.add('hidden');
                        }
                    })
                    .catch(failureCallback);
            },
            select: (info) => {
                // When a slot is clicked
                const event = {
                    detail: {
                        datetime: info.startStr,
                        display: info.start.toLocaleString('fr-FR', { 
                            weekday: 'long', 
                            day: 'numeric', 
                            month: 'long', 
                            hour: '2-digit', 
                            minute: '2-digit' 
                        })
                    }
                };
                window.dispatchEvent(new CustomEvent('slot-selected', event));
            }
        });

        calendar.render();
    }
}