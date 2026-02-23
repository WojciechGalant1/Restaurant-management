import './bootstrap';

import Alpine from 'alpinejs';
import tablesPage from './tables-page';
import dashboardLiveFeed from './dashboard-live-feed';
import shiftCreateForm from './shift-create-form';
import waiterDisplay from './waiter-display';
import kitchenDisplay from './kitchen-display';

window.Alpine = Alpine;

Alpine.data('tablesPage', () => tablesPage(window.__TABLES_PAGE__ || {}));
Alpine.data('dashboardLiveFeed', dashboardLiveFeed);
Alpine.data('shiftCreateForm', () => shiftCreateForm(window.__SHIFT_CREATE__ || {}));
Alpine.data('waiterDisplay', () => waiterDisplay(window.__WAITER__ || {}));
Alpine.data('kitchenDisplay', () => kitchenDisplay(window.__KITCHEN__ || {}));

Alpine.start();
