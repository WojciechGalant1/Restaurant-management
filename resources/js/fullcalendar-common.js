import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import tippy from 'tippy.js';
import 'tippy.js/dist/tippy.css';
import 'tippy.js/themes/light-border.css';

/**
 * @param {{
 *   elementId: string;
 *   eventsUrl: string;
 *   createUrl?: string;
 *   buildTooltipContent: (info: import('@fullcalendar/core').EventContentArg) => string;
 *   extraParams?: () => Record<string, string>;
 *   headerToolbarRight?: string;
 *   dayMaxEvents?: number;
 * }} options
 * @returns {{ calendar: import('@fullcalendar/core').Calendar }}
 */
export function initFullCalendar(options) {
    const {
        elementId,
        eventsUrl,
        createUrl = '',
        buildTooltipContent,
        extraParams,
        headerToolbarRight = 'dayGridMonth,timeGridWeek,timeGridDay',
        dayMaxEvents = 6,
    } = options;

    const el = document.getElementById(elementId);
    if (!el) {
        return { calendar: null };
    }

    const calendar = new Calendar(el, {
        plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: headerToolbarRight,
        },
        timeZone: 'local',
        height: 'auto',
        firstDay: 1,
        nowIndicator: true,
        editable: false,
        selectable: false,
        dayMaxEvents,

        events: {
            url: eventsUrl,
            method: 'GET',
            ...(typeof extraParams === 'function' ? { extraParams } : {}),
            failure: () => {
                console.error('Failed to load calendar events');
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
                maxWidth: 280,
                appendTo: document.body,
            });
        },

        dateClick: (info) => {
            if (createUrl) {
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                const clicked = new Date(info.date);
                clicked.setHours(0, 0, 0, 0);
                if (clicked >= today) {
                    window.location.href = `${createUrl}?date=${info.dateStr}`;
                }
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

    return { calendar };
}
