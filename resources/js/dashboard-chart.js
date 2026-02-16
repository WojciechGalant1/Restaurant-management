import ApexCharts from 'apexcharts';

function buildChartOptions(categories, series, options = {}) {
    return {
        chart: {
            type: 'bar',
            height: 280,
            toolbar: { show: false },
            fontFamily: 'inherit',
        },
        plotOptions: {
            bar: {
                borderRadius: 4,
                columnWidth: '60%',
                dataLabels: { position: 'top' },
            },
        },
        dataLabels: {
            enabled: true,
            formatter: (val) => (val ? `${Number(val).toFixed(0)}` : ''),
            offsetY: -20,
            style: { fontSize: '12px' },
        },
        xaxis: {
            categories,
            labels: {
                formatter: (val) => {
                    if (typeof val !== 'string') return val;
                    try {
                        const [y, m, d] = val.split('-');
                        return d && m ? `${d}.${m}` : val;
                    } catch {
                        return val;
                    }
                },
            },
        },
        yaxis: {
            labels: {
                formatter: (val) => (val ? `${Number(val).toFixed(0)}` : '0'),
            },
        },
        colors: ['#4F46E5'],
        fill: { opacity: 1 },
        grid: {
            borderColor: '#e5e7eb',
            strokeDashArray: 4,
            xaxis: { lines: { show: false } },
        },
        tooltip: {
            y: {
                formatter: (val) => (val != null ? `${Number(val).toFixed(2)} PLN` : ''),
            },
        },
        series: [{ name: options.seriesName || 'Revenue', data: series }],
        ...options,
    };
}

function initRevenueChart() {
    const data = window.dashboardRevenueData;
    const el = document.getElementById('revenue-chart');
    if (!el || !data) return;

    const data7 = data.data7 || {};
    const data30 = data.data30 || {};
    const categories7 = Object.keys(data7).sort();
    const categories30 = Object.keys(data30).sort();
    const series7 = categories7.map((d) => Number(data7[d]) || 0);
    const series30 = categories30.map((d) => Number(data30[d]) || 0);

    const hasData = categories7.length > 0 || categories30.length > 0;
    if (!hasData) return;

    const pickFirst = () => {
        if (categories7.length > 0) return { categories: categories7, series: series7, mode: '7' };
        return { categories: categories30, series: series30, mode: '30' };
    };
    const initial = pickFirst();

    const chart = new ApexCharts(el, buildChartOptions(initial.categories, initial.series, { seriesName: 'PLN' }));
    chart.render();

    const btn7 = document.getElementById('revenue-chart-btn-7');
    const btn30 = document.getElementById('revenue-chart-btn-30');
    const btnCustom = document.getElementById('revenue-chart-btn-custom');
    const customPanel = document.getElementById('revenue-custom-range');
    const customFrom = document.getElementById('revenue-custom-from');
    const customTo = document.getElementById('revenue-custom-to');
    const customApply = document.getElementById('revenue-custom-apply');

    const buttons = [
        { el: btn7, mode: '7', categories: categories7, series: series7 },
        { el: btn30, mode: '30', categories: categories30, series: series30 },
    ];

    function setActive(mode) {
        buttons.forEach((b) => {
            if (!b.el) return;
            if (b.mode === mode) {
                b.el.classList.add('bg-indigo-600', 'text-white');
                b.el.classList.remove('bg-gray-200', 'text-gray-700');
            } else {
                b.el.classList.remove('bg-indigo-600', 'text-white');
                b.el.classList.add('bg-gray-200', 'text-gray-700');
            }
        });
        if (btnCustom) {
            if (mode === 'custom') {
                btnCustom.classList.add('bg-indigo-600', 'text-white');
                btnCustom.classList.remove('bg-gray-200', 'text-gray-700');
                if (customPanel) customPanel.classList.remove('hidden');
            } else {
                btnCustom.classList.remove('bg-indigo-600', 'text-white');
                btnCustom.classList.add('bg-gray-200', 'text-gray-700');
                if (customPanel) customPanel.classList.add('hidden');
            }
        }
    }

    function applyData(categories, series) {
        chart.updateOptions({
            xaxis: { categories },
            series: [{ name: 'PLN', data: series }],
        });
    }

    if (btn7 && categories7.length > 0) {
        btn7.addEventListener('click', () => {
            applyData(categories7, series7);
            setActive('7');
        });
    }
    if (btn30 && categories30.length > 0) {
        btn30.addEventListener('click', () => {
            applyData(categories30, series30);
            setActive('30');
        });
    }
    if (btnCustom) {
        btnCustom.addEventListener('click', () => setActive('custom'));
    }

    if (customFrom && customTo && customApply && data.customUrl) {
        const today = new Date().toISOString().slice(0, 10);
        const monthAgo = new Date(Date.now() - 30 * 24 * 60 * 60 * 1000).toISOString().slice(0, 10);
        customFrom.value = monthAgo;
        customTo.value = today;

        customApply.addEventListener('click', () => {
            const from = customFrom.value;
            const to = customTo.value;
            if (!from || !to || from > to) return;
            customApply.disabled = true;
            fetch(`${data.customUrl}?from=${encodeURIComponent(from)}&to=${encodeURIComponent(to)}`, {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            })
                .then((r) => r.json())
                .then((res) => {
                    const d = res.data || {};
                    const cat = Object.keys(d).sort();
                    const ser = cat.map((c) => Number(d[c]) || 0);
                    if (cat.length > 0) {
                        applyData(cat, ser);
                        setActive('custom');
                    }
                })
                .finally(() => { customApply.disabled = false; });
        });
    }

    setActive(initial.mode);
}

function initPaymentDonut() {
    const data = window.dashboardPaymentData;
    const el = document.getElementById('payment-donut');
    if (!el || !data || typeof data !== 'object') return;

    const methodOrder = ['cash', 'card', 'online'];
    const labels = methodOrder.map((m) => m.charAt(0).toUpperCase() + m.slice(1));
    const series = methodOrder.map((m) => Number(data[m]) || 0);
    const total = series.reduce((a, b) => a + b, 0);

    if (total === 0) {
        el.innerHTML = '<p class="text-gray-400 text-sm text-center py-8">No payments today</p>';
        return;
    }

    const colors = ['#10b981', '#3b82f6', '#8b5cf6'];
    new ApexCharts(el, {
        chart: { type: 'donut', height: 200 },
        labels,
        series,
        colors: colors.slice(0, series.length),
        legend: { position: 'bottom' },
        dataLabels: { formatter: (val) => `${Math.round(val)}%` },
        plotOptions: {
            pie: {
                donut: { size: '65%' },
                dataLabels: { offset: -8 },
            },
        },
        tooltip: {
            y: { formatter: (val) => (val != null ? `${Number(val).toFixed(2)} PLN` : '') },
        },
    }).render();
}

function initDashboardCharts() {
    initRevenueChart();
    initPaymentDonut();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initDashboardCharts);
} else {
    initDashboardCharts();
}
