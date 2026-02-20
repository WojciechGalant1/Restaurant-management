import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';

function initShiftsCalendar() {
    const el = document.getElementById('shifts-calendar');
    if (!el) return;

    const eventsUrl = el.dataset.eventsUrl;
    const createUrl = el.dataset.createUrl;
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    const calendar = new Calendar(el, {
        plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek',
        },
        height: 'auto',
        firstDay: 1,
        nowIndicator: true,
        editable: false,
        selectable: false,
        dayMaxEvents: 4,

        events: {
            url: eventsUrl,
            method: 'GET',
            extraParams: () => ({ _token: csrfToken }),
            failure: () => {
                console.error('Failed to load shift events');
            },
        },

        eventClick: (info) => {
            const props = info.event.extendedProps;
            if (props.editUrl) {
                window.location.href = props.editUrl;
            }
        },

        eventDidMount: (info) => {
            const props = info.event.extendedProps;
            const parts = [`${props.shiftType} shift`];
            if (props.role) parts.push(`Role: ${props.role}`);
            if (props.notes) parts.push(`Notes: ${props.notes}`);
            info.el.title = parts.join('\n');
        },

        dateClick: (info) => {
            if (createUrl) {
                window.location.href = `${createUrl}?date=${info.dateStr}`;
            }
        },
    });

    calendar.render();

    const tabObserver = new MutationObserver(() => {
        if (el.offsetParent !== null) {
            calendar.updateSize();
        }
    });
    tabObserver.observe(el.closest('[x-data]') || document.body, {
        attributes: true,
        subtree: true,
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initShiftsCalendar);
} else {
    initShiftsCalendar();
}
