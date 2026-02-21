import Sortable from 'sortablejs';

function initTableSortables(component) {
    if (!component.isManager) return;

    document.querySelectorAll('.tables-sortable').forEach(el => {
        if (el._sortable) el._sortable.destroy();
        el._sortable = Sortable.create(el, {
            group: 'tables',
            animation: 150,
            draggable: '.table-card',
            ghostClass: 'opacity-30',
            onEnd() {
                component.saveOrder();
            },
        });
    });
}

function initSortables(component) {
    if (!component.isManager) return;

    const roomsContainer = document.getElementById('rooms-container');
    if (!roomsContainer) return;

    if (roomsContainer._sortable) roomsContainer._sortable.destroy();
    roomsContainer._sortable = Sortable.create(roomsContainer, {
        animation: 150,
        handle: '.room-drag-handle',
        draggable: '.room-section',
        ghostClass: 'opacity-30',
        onEnd() {
            component.saveOrder();
        },
    });

    initTableSortables(component);
}

window.initTablesSortables = initSortables;
window.reinitTableSortables = initTableSortables;
