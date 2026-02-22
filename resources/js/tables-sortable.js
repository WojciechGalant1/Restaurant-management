import Sortable from 'sortablejs';

function revertDomMove(evt) {
    const item = evt.item;
    item.remove();
    const draggables = Array.from(evt.from.querySelectorAll(':scope > .table-card'));
    if (evt.oldIndex < draggables.length) {
        evt.from.insertBefore(item, draggables[evt.oldIndex]);
    } else {
        const emptyDiv = evt.from.querySelector(':scope > [class*="col-span-full"]');
        evt.from.insertBefore(item, emptyDiv || null);
    }
}

function revertRoomDomMove(container, evt) {
    const item = evt.item;
    item.remove();
    const sections = Array.from(container.querySelectorAll(':scope > .room-section'));
    if (evt.oldIndex < sections.length) {
        container.insertBefore(item, sections[evt.oldIndex]);
    } else {
        container.appendChild(item);
    }
}

function initTableSortables(component) {
    if (!component.isManager) return;

    document.querySelectorAll('.tables-sortable').forEach(el => {
        if (el._sortable) el._sortable.destroy();
        el._sortable = Sortable.create(el, {
            group: 'tables',
            animation: 150,
            draggable: '.table-card',
            ghostClass: 'opacity-30',
            onEnd(evt) {
                revertDomMove(evt);
                component.applyTableMove({
                    fromRoomId: evt.from.dataset.roomId,
                    toRoomId: evt.to.dataset.roomId,
                    tableId: parseInt(evt.item.dataset.tableId),
                    oldIndex: evt.oldIndex,
                    newIndex: evt.newIndex,
                });
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
        onEnd(evt) {
            revertRoomDomMove(roomsContainer, evt);
            component.applyRoomMove(evt.oldIndex, evt.newIndex);
        },
    });

    initTableSortables(component);
}

window.initTablesSortables = initSortables;
window.reinitTableSortables = initTableSortables;
