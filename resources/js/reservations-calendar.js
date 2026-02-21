import { initFullCalendar } from './fullcalendar-common.js';

function buildTooltipContent(info) {
    const props = info.event.extendedProps;
    const startTime = info.event.start
        ? info.event.start.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
        : '';
    const endTime = info.event.end
        ? info.event.end.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
        : '';

    let html = `<div style="font-weight:600;color:#111827;margin-bottom:2px">${props.customerName}</div>`;
    html += `<div style="color:#6b7280;font-size:11px;margin-bottom:6px">${props.phoneNumber || ''}</div>`;
    html += `<div style="color:#374151;margin-bottom:4px">`;
    html += `<svg style="width:12px;height:12px;vertical-align:-2px;margin-right:4px;display:inline" fill="none" stroke="#9ca3af" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>`;
    html += `${startTime} – ${endTime}</div>`;
    html += `<span style="display:inline-block;padding:1px 8px;border-radius:9999px;font-size:11px;font-weight:500;background:#f3f4f6;color:#374151;margin-right:4px">Table #${props.tableNumber}</span>`;
    html += `<span style="font-size:11px;color:#6b7280">${props.partySize} guests · ${props.status}</span>`;
    if (props.notes) {
        html += `<div style="color:#6b7280;font-size:11px;margin-top:4px;border-top:1px solid #e5e7eb;padding-top:4px">${props.notes}</div>`;
    }
    html += `<div style="color:#6366f1;font-size:11px;margin-top:6px">Click to edit</div>`;

    return html;
}

function initReservationsCalendar() {
    const el = document.getElementById('reservations-calendar');
    if (!el) return;

    initFullCalendar({
        elementId: 'reservations-calendar',
        eventsUrl: el.dataset.eventsUrl,
        createUrl: el.dataset.createUrl || '',
        buildTooltipContent,
        headerToolbarRight: 'dayGridMonth,timeGridWeek,timeGridDay',
        dayMaxEvents: 6,
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initReservationsCalendar);
} else {
    initReservationsCalendar();
}
