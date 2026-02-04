import { Controller } from '@hotwired/stimulus';
import { Calendar } from '@fullcalendar/core';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';

export default class extends Controller {
    static targets = ["loader"];

    connect() {
        this.initCalendar();
    }

    initCalendar() {
        const calendarEl = document.getElementById('calendar');
        if (!calendarEl) return;

        // Get color from CSS variable for consistency
        const primaryColor = getComputedStyle(document.documentElement)
            .getPropertyValue('--color-lab-primary')
            .trim() || '#00ffc3';

        const calendar = new Calendar(calendarEl, {
            plugins: [ timeGridPlugin, interactionPlugin ],
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