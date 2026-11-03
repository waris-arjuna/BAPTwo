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
import {
    tkpWilayahForm,
    ppnsTimForm,
    tersangkaAlamatModalForm,
    pasalPerkaraForm
} from './components/perkara-form';

window.Alpine = Alpine;
window.flatpickr = flatpickr;
window.tableFilter = tableFilter;
window.toastManager = toastManager;
window.initMonthlyCasesChart = initMonthlyCasesChart;
window.monthlyCasesChart = monthlyCasesChart;
window.tkpWilayahForm = tkpWilayahForm;
window.ppnsTimForm = ppnsTimForm;
window.tersangkaAlamatModalForm = tersangkaAlamatModalForm;
window.pasalPerkaraForm = pasalPerkaraForm;

Alpine.data('tableFilter', tableFilter);
Alpine.data('toastManager', toastManager);
Alpine.data('monthlyCasesChart', monthlyCasesChart);
Alpine.data('tkpWilayahForm', tkpWilayahForm);
Alpine.data('ppnsTimForm', ppnsTimForm);
Alpine.data('tersangkaAlamatModalForm', tersangkaAlamatModalForm);
Alpine.data('pasalPerkaraForm', pasalPerkaraForm);

Alpine.start();
