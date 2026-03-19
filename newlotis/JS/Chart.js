// --- Клиентский класс LTS.Chart ---
class ltsChart {
    constructor(id) {
        this.id = id;
        this.data = { labels: [], datasets: [] };
        this.options = {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                xAxes: [{ ticks: { autoSkip: true } }],
                yAxes: [{ ticks: { beginAtZero: true } }]
            }
        };
        this.type = 'line';
    }

    // Устанавливает тип графика
    type(type) {
        this.type = type;
        return this;
    }

    // Добавляет метку для оси X
    label(label) {
        this.data.labels.push(label);
        return this;
    }

    // Добавляет набор данных
    dataset(data, options = {}) {
        const colors = [
            'rgba(54, 162, 235, 0.7)', // blue
            'rgba(255, 99, 132, 0.7)', // red
            'rgba(75, 192, 192, 0.7)', // green
            'rgba(255, 205, 86, 0.7)', // yellow
            'rgba(153, 102, 255, 0.7)'  // purple
        ];
        const index = this.data.datasets.length % colors.length;

        const dataset = {
            data: Array.isArray(data) ? data : [data],
            backgroundColor: options.backgroundColor || colors[index],
            borderColor: options.borderColor || colors[index].replace('0.7', '1'),
            borderWidth: options.borderWidth || 1
        };

        Object.assign(dataset, options);
        this.data.datasets.push(dataset);
        return this;
    }

    // Устанавливает опции
    options(opts) {
        this.options = Object.assign(this.options, opts);
        return this;
    }

    // Включает/отключает сетку
    grid(show = true) {
        this.options.scales.yAxes[0].gridLines.display = show;
        return this;
    }

    // Устанавливает заголовок
    title(text, size = 16) {
        this.options.title = { display: true, text, fontSize: size };
        return this;
    }

    // Настраивает ось Y
    yscale(min = null, max = null) {
        const ticks = this.options.scales.yAxes[0].ticks || {};
        if (min !== null) ticks.suggestedMin = min;
        if (max !== null) ticks.suggestedMax = max;
        this.options.scales.yAxes[0].ticks = ticks;
        return this;
    }

    // Обновляет только данные, сохраняя стиль
    updateData(data) {
        Object.assign(this.data, data);
        this.render(); // или this.chart.update() при интеграции с Chart.js
        return this;
    }

    // Отрисовывает график
    render() {
        const canvas = document.getElementById(this.id);
        if (!canvas) {
            console.error(`Canvas с id="${this.id}" не найден`);
            return;
        }

        // Если график уже существует, уничтожаем его
        if (window.__ltsCharts && window.__ltsCharts[this.id]) {
            window.__ltsCharts[this.id].destroy();
        }

        const ctx = canvas.getContext('2d');
        const chart = new Chart(ctx, {
            type: this.type,
            data: this.data,
            options: this.options
        });

        // Сохраняем ссылку для последующего уничтожения
        if (!window.__ltsCharts) window.__ltsCharts = {};
        window.__ltsCharts[this.id] = chart;

        return this;
    }
};
