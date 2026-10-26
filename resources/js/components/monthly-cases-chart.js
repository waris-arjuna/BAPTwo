import ApexCharts from 'apexcharts';

export function buildMonthlyCasesOptions(data = {}, isDark = false, height = 200) {
    const incomingData = data.incoming || Array(12).fill(0);
    const completedData = data.completed || Array(12).fill(0);
    const categories = data.labels || [
        'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun',
        'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'
    ];

    return {
        series: [
            {
                name: 'Perkara Masuk',
                data: incomingData,
            },
            {
                name: 'Perkara Selesai',
                data: completedData,
            },
        ],
        chart: {
            type: 'bar',
            height: height,
            toolbar: {
                show: false,
            },
            background: 'transparent',
            foreColor: isDark ? '#98a2b3' : '#667085',
            fontFamily: 'Inter Variable, sans-serif',
            animations: {
                enabled: true,
                easing: 'easeinout',
                speed: 400,
            },
        },
        plotOptions: {
            bar: {
                horizontal: false,
                columnWidth: '44%',
                borderRadius: 4,
                borderRadiusApplication: 'end',
            },
        },
        dataLabels: {
            enabled: false,
        },
        stroke: {
            show: true,
            width: 2,
            colors: ['transparent'],
        },
        colors: ['#465fff', '#12b76a'],
        xaxis: {
            categories: categories,
            labels: {
                style: {
                    colors: isDark ? '#98a2b3' : '#667085',
                    fontSize: '11px',
                    fontFamily: 'Inter Variable, sans-serif',
                    fontWeight: 500,
                },
            },
            axisBorder: {
                color: isDark ? '#344054' : '#e4e7ec',
            },
            axisTicks: {
                color: isDark ? '#344054' : '#e4e7ec',
            },
        },
        yaxis: {
            min: 0,
            forceNiceScale: true,
            labels: {
                style: {
                    colors: isDark ? '#98a2b3' : '#667085',
                    fontSize: '11px',
                    fontFamily: 'Inter Variable, sans-serif',
                    fontWeight: 500,
                },
                formatter: (val) => (Number.isInteger(val) ? val : Math.floor(val)),
            },
        },
        grid: {
            borderColor: isDark ? '#1d2939' : '#f2f4f7',
            strokeDashArray: 3,
            xaxis: {
                lines: {
                    show: false,
                },
            },
            yaxis: {
                lines: {
                    show: true,
                },
            },
        },
        legend: {
            show: false,
        },
        tooltip: {
            theme: isDark ? 'dark' : 'light',
            shared: true,
            intersect: false,
            y: {
                formatter: (val) => `${val} perkara`,
            },
        },
    };
}

export function initMonthlyCasesChart(element, data = {}, options = {}) {
    if (!element) return null;

    const isDarkMode = () => document.documentElement.classList.contains('dark');
    const height = options.height || 200;

    const chartOptions = buildMonthlyCasesOptions(data, isDarkMode(), height);
    const chart = new ApexCharts(element, chartOptions);
    chart.render();

    const themeHandler = () => {
        const dark = isDarkMode();
        chart.updateOptions({
            theme: {
                mode: dark ? 'dark' : 'light',
            },
            chart: {
                type: 'bar',
                foreColor: dark ? '#98a2b3' : '#667085',
                toolbar: {
                    show: false,
                },
            },
            grid: {
                borderColor: dark ? '#1d2939' : '#f2f4f7',
            },
            xaxis: {
                labels: {
                    style: {
                        colors: dark ? '#98a2b3' : '#667085',
                    },
                },
                axisBorder: {
                    color: dark ? '#344054' : '#e4e7ec',
                },
                axisTicks: {
                    color: dark ? '#344054' : '#e4e7ec',
                },
            },
            yaxis: {
                labels: {
                    style: {
                        colors: dark ? '#98a2b3' : '#667085',
                    },
                },
            },
            tooltip: {
                theme: dark ? 'dark' : 'light',
            },
        }, false, false);
    };

    window.addEventListener('theme-changed', themeHandler);

    return {
        chart,
        destroy() {
            window.removeEventListener('theme-changed', themeHandler);
            chart.destroy();
        },
    };
}

export function monthlyCasesChart(initialData = {}) {
    return {
        chartInstance: null,
        data: initialData,
        init() {
            this.$nextTick(() => {
                if (this.$refs.chartContainer) {
                    this.chartInstance = initMonthlyCasesChart(this.$refs.chartContainer, this.data);
                }
            });
        },
        destroy() {
            if (this.chartInstance) {
                this.chartInstance.destroy();
                this.chartInstance = null;
            }
        },
    };
}

export default {
    buildMonthlyCasesOptions,
    initMonthlyCasesChart,
    monthlyCasesChart,
};
