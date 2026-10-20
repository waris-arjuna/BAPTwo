import './bootstrap';
import Alpine from 'alpinejs';

// flatpickr
import flatpickr from 'flatpickr';
import { Indonesian } from 'flatpickr/dist/l10n/id.js';
import 'flatpickr/dist/flatpickr.min.css';

flatpickr.localize(Indonesian);

import tableFilter from './components/table-filter';
import toastManager from './components/toast';

window.Alpine = Alpine;
window.flatpickr = flatpickr;
window.tableFilter = tableFilter;
window.toastManager = toastManager;

Alpine.data('tableFilter', tableFilter);
Alpine.data('toastManager', toastManager);

Alpine.start();
