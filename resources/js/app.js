import './bootstrap';
import Alpine from 'alpinejs';

// flatpickr
import flatpickr from 'flatpickr';
import { Indonesian } from 'flatpickr/dist/l10n/id.js';
import 'flatpickr/dist/flatpickr.min.css';

flatpickr.localize(Indonesian);

import tableFilter from './components/table-filter';

window.Alpine = Alpine;
window.flatpickr = flatpickr;
window.tableFilter = tableFilter;

Alpine.data('tableFilter', tableFilter);

Alpine.start();
