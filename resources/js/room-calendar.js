import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';

const element = document.getElementById('room-calendar');
if (element) {
    const error = document.getElementById('calendar-error');
    const room = document.getElementById('calendar-room');
    const info = document.getElementById('calendar-event');
    const calendar = new Calendar(element, {
        plugins: [dayGridPlugin, interactionPlugin],
        initialView: 'dayGridMonth',
        initialDate: element.dataset.today,
        now: element.dataset.today,
        timeZone: 'UTC', // Date-only all-day events; no browser timezone shift.
        height: 'auto',
        headerToolbar: { left: 'prev,next today', center: 'title', right: 'dayGridMonth,dayGridWeek,dayGridDay' },
        buttonText: { month: 'Month', week: 'Week', day: 'Day' },
        firstDay: 1,
        dayMaxEvents: true,
        eventDisplay: 'block',
        editable: false,
        events: async (range, success, failure) => {
            error.classList.add('hidden');
            const url = new URL(element.dataset.feed, window.location.origin);
            url.searchParams.set('start', range.startStr.slice(0, 10));
            url.searchParams.set('end', range.endStr.slice(0, 10));
            if (room.value) url.searchParams.set('room_id', room.value);
            try {
                const response = await fetch(url, { headers: { Accept: 'application/json' }, credentials: 'same-origin' });
                if (!response.ok) throw new Error('Calendar could not load. Refresh the page or sign in again.');
                success(await response.json());
            } catch (cause) {
                error.textContent = cause.message;
                error.classList.remove('hidden');
                failure(cause);
            }
        },
        eventClick: ({ event }) => {
            info.textContent = `${event.extendedProps.room} · ${event.extendedProps.kind} · ${event.startStr} to ${event.endStr} (end date excluded)`;
        },
        dateClick: ({ dateStr }) => {
            const start = document.getElementById('maintenance-start');
            const end = document.getElementById('maintenance-end');
            if (!start || dateStr < start.min) return;
            start.value = dateStr;
            const next = new Date(`${dateStr}T00:00:00Z`);
            next.setUTCDate(next.getUTCDate() + 1);
            end.value = next.toISOString().slice(0, 10);
            const select = document.querySelector('[name="villa_id"]');
            if (room.value && select) select.value = room.value;
            start.focus();
        },
    });
    room.addEventListener('change', () => { info.textContent = ''; calendar.refetchEvents(); });
    calendar.render();
}
