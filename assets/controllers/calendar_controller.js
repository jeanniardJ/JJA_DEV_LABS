import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ["loader"];

    connect() {
        console.log("Calendar controller connected");
        if (typeof FullCalendar === 'undefined' || typeof FullCalendar.Calendar === 'undefined') {
            console.error("FullCalendar not loaded globally.");
            return;
        }
        this.initCalendar();
    }

    initCalendar() {
        const calendarEl = document.getElementById('calendar');
        if (!calendarEl) return;

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
            hiddenDays: [0, 6], 
            slotMinTime: '09:00:00',
            slotMaxTime: '17:00:00',
            allDaySlot: false,
            slotDuration: '00:30:00',
            selectable: true,
            selectOverlap: false,
            locale: 'fr',
            events: async (info, successCallback, failureCallback) => {
                try {
                    const start = new Date(info.start);
                    const end = new Date(info.end);
                    const allEvents = [];

                    // On boucle sur chaque jour de la plage visible
                    let current = new Date(start);
                    while (current < end) {
                        const dateStr = current.toISOString().split('T')[0];
                        const response = await fetch(`/api/appointments/slots?date=${dateStr}`);
                        const data = await response.json();

                        const dayEvents = data.map(slot => ({
                            id: 'available-slot',
                            title: 'LIBRE',
                            start: slot.datetime,
                            end: new Date(new Date(slot.datetime).getTime() + 30 * 60000).toISOString(),
                            display: 'block',
                            backgroundColor: primaryColor + '33', 
                            borderColor: primaryColor,
                            textColor: '#ffffff'
                        }));

                        allEvents.push(...dayEvents);
                        current.setDate(current.getDate() + 1);
                    }

                    successCallback(allEvents);
                    if (this.hasLoaderTarget) this.loaderTarget.classList.add('hidden');
                } catch (e) {
                    console.error("Error fetching slots:", e);
                    failureCallback(e);
                }
            },
            selectAllow: (selectInfo) => {
                return selectInfo.start >= new Date();
            },
            eventClick: (info) => {
                if (info.event.id === 'available-slot') {
                    const event = {
                        detail: {
                            datetime: info.event.startStr,
                            display: info.event.start.toLocaleString('fr-FR', { 
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
            }
        });

        calendar.render();
    }
}
