import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import tippy from 'tippy.js';
import 'tippy.js/dist/tippy.css';
import 'tippy.js/themes/light-border.css';

function buildTooltipContent(info) {
    const props = info.event.extendedProps;
    const startTime = info.event.start
        ? info.event.start.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
        : '';
    const endTime = info.event.end
        ? info.event.end.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
        : '';

    let html = `<div style="font-weight:600;color:#111827;margin-bottom:2px">${props.userName}</div>`;
    html += `<div style="color:#6b7280;font-size:11px;margin-bottom:6px">${props.role}</div>`;
    html += `<div style="color:#374151;margin-bottom:4px">`;
    html += `<svg style="width:12px;height:12px;vertical-align:-2px;margin-right:4px;display:inline" fill="none" stroke="#9ca3af" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>`;
    html += `${startTime} – ${endTime}</div>`;
    html += `<span style="display:inline-block;padding:1px 8px;border-radius:9999px;font-size:11px;font-weight:500;background:#f3f4f6;color:#374151;margin-bottom:4px">${props.shiftType}</span>`;
    if (props.notes) {
        html += `<div style="color:#6b7280;font-size:11px;margin-top:4px;border-top:1px solid #e5e7eb;padding-top:4px">${props.notes}</div>`;
    }
    html += `<div style="color:#6366f1;font-size:11px;margin-top:6px">Click to edit</div>`;

    return html;
}

function initShiftsCalendar() {
    const el = document.getElementById('shifts-calendar');
    if (!el) return;

    const eventsUrl = el.dataset.eventsUrl;
    const createUrl = el.dataset.createUrl;

    let activeRole = '';

    const calendar = new Calendar(el, {
        plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek',
        },
        timeZone: 'local',
        height: 'auto',
        firstDay: 1,
        nowIndicator: true,
        editable: false,
        selectable: false,
        dayMaxEvents: 4,

        events: {
            url: eventsUrl,
            method: 'GET',
            extraParams: () => {
                const params = {};
                if (activeRole) params.role = activeRole;
                return params;
            },
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
            info.el.style.cursor = 'pointer';

            tippy(info.el, {
                content: buildTooltipContent(info),
                allowHTML: true,
                theme: 'light-border',
                placement: 'top',
                interactive: true,
                delay: [200, 0],
                maxWidth: 260,
                appendTo: document.body,
            });
        },

        dateClick: (info) => {
            if (createUrl) {
                window.location.href = `${createUrl}?date=${info.dateStr}`;
            }
        },
    });

    calendar.render();

    const filterButtons = document.querySelectorAll('[data-role-filter]');
    filterButtons.forEach((btn) => {
        btn.addEventListener('click', () => {
            const role = btn.dataset.roleFilter;
            activeRole = activeRole === role ? '' : role;

            filterButtons.forEach((b) => {
                const isActive = b.dataset.roleFilter === activeRole;
                b.classList.toggle('bg-indigo-600', isActive);
                b.classList.toggle('text-white', isActive);
                b.classList.toggle('bg-gray-100', !isActive);
                b.classList.toggle('text-gray-700', !isActive);
            });

            calendar.refetchEvents();
        });
    });

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
