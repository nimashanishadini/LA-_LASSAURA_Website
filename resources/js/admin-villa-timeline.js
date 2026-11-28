const DAY = 86400000;

const parseDate = (value) =>
    new Date(`${value.slice(0, 10)}T00:00:00Z`);

const dateKey = (date) =>
    date.toISOString().slice(0, 10);

const addDays = (date, days) =>
    new Date(date.getTime() + days * DAY);

const formatDate = (date, options) =>
    new Intl.DateTimeFormat('en-GB', {
        ...options,
        timeZone: 'UTC',
    }).format(date);

export function initVillaTimeline() {
    const root = document.getElementById('villa-timeline');

    if (!root || root.dataset.initialized) return;

    root.dataset.initialized = 'true';

    const calendar = document.getElementById('room-calendar');
    const roomFilter = document.getElementById('calendar-room');

    const grid = document.getElementById('timeline-grid');
    const title = document.getElementById('timeline-title');
    const loading = document.getElementById('timeline-loading');
    const error = document.getElementById('timeline-error');
    const retry = document.getElementById('timeline-retry');
    const info = document.getElementById('calendar-event');

    const rooms = JSON.parse(root.dataset.rooms || '[]');

    let selectedDate = parseDate(calendar.dataset.today);
    let view = 'week';
    let request = null;

    function getRange() {
        let start = new Date(selectedDate);
        let end;

        if (view === 'day') {
            end = addDays(start, 1);
        } else if (view === 'week') {
            const offset = (start.getUTCDay() + 6) % 7;

            start = addDays(start, -offset);
            end = addDays(start, 7);
        } else {
            start = new Date(Date.UTC(
                start.getUTCFullYear(),
                start.getUTCMonth(),
                1
            ));

            end = new Date(Date.UTC(
                start.getUTCFullYear(),
                start.getUTCMonth() + 1,
                1
            ));
        }

        return {
            start,
            end,
            days: Math.round((end - start) / DAY),
        };
    }

    function updateTitle(range) {
        if (view === 'month') {
            title.textContent = formatDate(range.start, {
                month: 'long',
                year: 'numeric',
            });
        } else if (view === 'day') {
            title.textContent = formatDate(range.start, {
                day: 'numeric',
                month: 'long',
                year: 'numeric',
            });
        } else {
            title.textContent =
                formatDate(range.start, {
                    day: 'numeric',
                    month: 'short',
                }) +
                ' – ' +
                formatDate(addDays(range.end, -1), {
                    day: 'numeric',
                    month: 'short',
                    year: 'numeric',
                });
        }

        root.querySelectorAll('[data-timeline-view]')
            .forEach((button) => {
                const active =
                    button.dataset.timelineView === view;

                button.classList.toggle('is-active', active);

                button.setAttribute(
                    'aria-pressed',
                    String(active)
                );
            });
    }

    function createElement(tag, className, text = '') {
        const element = document.createElement(tag);

        element.className = className;
        element.textContent = text;

        return element;
    }

    function eventType(event) {
        const kind = String(
            event.extendedProps?.kind || ''
        ).toLowerCase();

        if (kind.includes('maintenance')) {
            return 'maintenance';
        }

        if (kind === 'confirmed stay') {
            return 'confirmed';
        }

        return 'unknown';
    }

    function showEvent(event) {
        const props = event.extendedProps || {};

        info.textContent = [
            props.room || 'Villa',
            props.kind || 'Calendar event',
            `${event.start} to ${event.end}`,
            '(end date excluded)',
        ].join(' · ');

        info.scrollIntoView({
            behavior: 'auto',
            block: 'nearest',
        });
    }

    function selectMaintenanceDate(date, villaId) {
        const start = document.getElementById(
            'maintenance-start'
        );

        const end = document.getElementById(
            'maintenance-end'
        );

        const select = document.getElementById(
            'maintenance-room'
        );

        if (!start || !end || date < start.min) return;

        start.value = date;

        end.value = dateKey(
            addDays(parseDate(date), 1)
        );

        if (select) {
            select.value = String(villaId);
        }

        start.focus();
    }

    function render(range, events) {
        grid.replaceChildren();

        grid.style.setProperty(
            '--timeline-days',
            range.days
        );

        const visibleRooms = rooms.filter((room) =>
            !roomFilter.value ||
            String(room.id) === roomFilter.value
        );

        const header = createElement(
            'div',
            'timeline-header'
        );

        header.append(
            createElement(
                'div',
                'timeline-villa-heading',
                'VILLA'
            )
        );

        for (let i = 0; i < range.days; i++) {
            const date = addDays(range.start, i);

            const cell = createElement(
                'div',
                'timeline-date-heading'
            );

            if (dateKey(date) === calendar.dataset.today) {
                cell.classList.add('is-today');
            }

            cell.append(
                createElement(
                    'span',
                    'timeline-weekday',
                    formatDate(date, {
                        weekday: 'short',
                    })
                ),
                createElement(
                    'strong',
                    'timeline-day-number',
                    String(date.getUTCDate())
                )
            );

            header.append(cell);
        }

        grid.append(header);

        if (!visibleRooms.length) {
            grid.append(
                createElement(
                    'p',
                    'timeline-empty',
                    'No villas available.'
                )
            );

            return;
        }

        visibleRooms.forEach((room) => {
            const row = createElement(
                'div',
                'timeline-room-row'
            );

            const label = createElement(
                'div',
                'timeline-room-name',
                room.name
            );

            const track = createElement(
                'div',
                'timeline-track'
            );

            for (let i = 0; i < range.days; i++) {
                const date = dateKey(
                    addDays(range.start, i)
                );

                const cell = createElement(
                    'button',
                    'timeline-cell'
                );

                cell.type = 'button';

                cell.style.gridColumn = String(i + 1);
                cell.style.gridRow = '1 / -1';

                if (date === calendar.dataset.today) {
                    cell.classList.add('is-today');
                }

                cell.setAttribute(
                    'aria-label',
                    `${room.name}, ${date}`
                );

                const maintenanceStart =
                    document.getElementById(
                        'maintenance-start'
                    );

                cell.disabled =
                    !maintenanceStart ||
                    date < maintenanceStart.min;

                cell.addEventListener('click', () => {
                    selectMaintenanceDate(date, room.id);
                });

                track.append(cell);
            }

            const roomEvents = events
                .filter((event) => {
                    const props =
                        event.extendedProps || {};

                    if (props.room_id != null) {
                        return String(props.room_id) ===
                            String(room.id);
                    }

                    return props.room === room.name;
                })
                .map((event) => {
                    if (!event.start || !event.end) {
                        return null;
                    }

                    const start = Math.max(
                        parseDate(event.start).getTime(),
                        range.start.getTime()
                    );

                    const end = Math.min(
                        parseDate(event.end).getTime(),
                        range.end.getTime()
                    );

                    if (end <= start) return null;

                    return {
                        event,
                        start: (start - range.start) / DAY,
                        end: (end - range.start) / DAY,
                    };
                })
                .filter(Boolean)
                .sort((a, b) =>
                    a.start - b.start || a.end - b.end
                );

            const lanes = [];

            roomEvents.forEach(({ event, start, end }) => {
                let lane = lanes.findIndex(
                    (lastEnd) => lastEnd <= start
                );

                if (lane === -1) {
                    lane = lanes.length;
                }

                lanes[lane] = end;

                const type = eventType(event);

                const card = createElement(
                    'button',
                    `timeline-booking ${type}`
                );

                card.type = 'button';

                card.style.gridColumn =
                    `${start + 1} / ${end + 1}`;

                card.style.gridRow = String(lane + 1);

                const props = event.extendedProps || {};

                card.append(
                    createElement(
                        'strong',
                        'timeline-booking-title',
                        props.room || room.name
                    ),
                    createElement(
                        'span',
                        'timeline-booking-subtitle',
                        props.kind || 'Calendar event'
                    )
                );

                card.title =
                    `${room.name} · ${props.kind || 'Event'}`
                    + ` · ${event.start} to ${event.end}`;

                card.setAttribute(
                    'aria-label',
                    card.title
                );

                card.addEventListener('click', () => {
                    showEvent(event);
                });

                track.append(card);
            });

            track.style.gridTemplateRows =
                `repeat(${Math.max(lanes.length, 1)}, minmax(62px, auto))`;

            row.append(label, track);
            grid.append(row);
        });
    }

    async function load() {
        request?.abort();

        const controller = new AbortController();

        request = controller;

        const range = getRange();

        updateTitle(range);

        grid.replaceChildren();

        loading.hidden = false;
        error.hidden = true;
        retry.hidden = true;

        root.setAttribute('aria-busy', 'true');

        info.textContent = '';

        const url = new URL(
            calendar.dataset.feed,
            window.location.origin
        );

        url.searchParams.set(
            'start',
            dateKey(range.start)
        );

        url.searchParams.set(
            'end',
            dateKey(range.end)
        );

        if (roomFilter.value) {
            url.searchParams.set(
                'room_id',
                roomFilter.value
            );
        }

        try {
            const response = await fetch(url, {
                headers: {
                    Accept: 'application/json',
                },
                credentials: 'same-origin',
                signal: controller.signal,
            });

            if (!response.ok) {
                throw new Error(
                    'Unable to load villa calendar.'
                );
            }

            const events = await response.json();

            if (!Array.isArray(events)) {
                throw new Error(
                    'Invalid calendar response.'
                );
            }

            if (controller.signal.aborted) return;

            render(range, events);
        } catch (cause) {
            if (controller.signal.aborted) return;

            error.textContent =
                'Calendar could not load. Please try again.';

            error.hidden = false;
            retry.hidden = false;
        } finally {
            if (request === controller) {
                loading.hidden = true;

                root.setAttribute(
                    'aria-busy',
                    'false'
                );
            }
        }
    }

    function navigate(direction) {
        if (view === 'month') {
            selectedDate = new Date(Date.UTC(
                selectedDate.getUTCFullYear(),
                selectedDate.getUTCMonth() + direction,
                1
            ));
        } else {
            selectedDate = addDays(
                selectedDate,
                direction * (view === 'week' ? 7 : 1)
            );
        }

        load();
    }

    document.getElementById('timeline-prev')
        .addEventListener('click', () => navigate(-1));

    document.getElementById('timeline-next')
        .addEventListener('click', () => navigate(1));

    document.getElementById('timeline-today')
        .addEventListener('click', () => {
            selectedDate = parseDate(
                calendar.dataset.today
            );

            load();
        });

    root.querySelectorAll('[data-timeline-view]')
        .forEach((button) => {
            button.addEventListener('click', () => {
                view = button.dataset.timelineView;

                load();
            });
        });

    roomFilter.addEventListener('change', load);

    retry.addEventListener('click', load);

    load();
}