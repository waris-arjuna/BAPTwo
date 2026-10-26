import './bootstrap';
import Alpine from 'alpinejs';

// flatpickr
import flatpickr from 'flatpickr';
import { Indonesian } from 'flatpickr/dist/l10n/id.js';
import 'flatpickr/dist/flatpickr.min.css';

flatpickr.localize(Indonesian);

import tableFilter from './components/table-filter';
import toastManager from './components/toast';
import { initMonthlyCasesChart, monthlyCasesChart } from './components/monthly-cases-chart';

window.Alpine = Alpine;
window.flatpickr = flatpickr;
window.tableFilter = tableFilter;
window.toastManager = toastManager;
window.initMonthlyCasesChart = initMonthlyCasesChart;
window.monthlyCasesChart = monthlyCasesChart;

Alpine.data('tableFilter', tableFilter);
Alpine.data('toastManager', toastManager);
Alpine.data('monthlyCasesChart', monthlyCasesChart);

Alpine.start();
