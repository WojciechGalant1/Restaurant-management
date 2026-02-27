import './bootstrap';

import tippy from 'tippy.js';
import 'tippy.js/dist/tippy.css';
import 'tippy.js/themes/light-border.css';

import Alpine from 'alpinejs';

window.tippy = tippy;
import tablesPage from './tables-page';
import dashboardLiveFeed from './dashboard-live-feed';
import shiftCreateForm from './shift-create-form';
import reservationCreateForm from './reservation-create-form';
import waiterDisplay from './waiter-display';
import kitchenDisplay from './kitchen-display';

window.Alpine = Alpine;

Alpine.data('tablesPage', () => tablesPage(window.__TABLES_PAGE__ || {}));
Alpine.data('dashboardLiveFeed', dashboardLiveFeed);
Alpine.data('shiftCreateForm', () => shiftCreateForm(window.__SHIFT_CREATE__ || {}));
Alpine.data('reservationCreateForm', () => reservationCreateForm(window.__RESERVATION_CREATE__ || {}));
Alpine.data('waiterDisplay', () => waiterDisplay(window.__WAITER__ || {}));
Alpine.data('kitchenDisplay', () => kitchenDisplay(window.__KITCHEN__ || {}));

Alpine.start();
