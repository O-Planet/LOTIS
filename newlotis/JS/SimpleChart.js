// ltsSimpleChart.js
const ltsSimpleChart = {
    charts: {}, // <--- Переменная должна быть здесь, на уровне объекта

    /**
     * Создаёт новый график или обновляет существующий
     */
    create(id, config) {
        const canvas = document.getElementById(id); // <-- Получаем уже существующий canvas
        if (!canvas) {
            console.error(`Canvas с id="${id}" не найден`);
            return;
        }

        const ctx = canvas.getContext('2d');
        
        // Сохраняем конфиг
        this.charts[id] = {
            config: config,
            ctx: ctx,
            canvas: canvas
        };

        // Запускаем рендеринг
        this.render(id);
    },

    /**
     * Отрисовывает график
     */
    render(id) {
        const chart = this.charts[id];
        if (!chart) return;

        const { ctx, config } = chart;
        const { width, height } = chart.canvas;
        const padding = 50;
        const chartWidth = width - padding * 2;
        const chartHeight = height - padding * 2;

        // Очистка
        ctx.clearRect(0, 0, width, height);

        const data = config.data;
        const datasets = data.datasets;
        const labels = data.labels;
        const type = config.type || 'line';

        // Определяем центр и радиус для всех типов диаграмм
        const centerX = width / 2;
        const centerY = height / 2;
        const maxSize = Math.min(width, height);
        const radius = maxSize / 2 - padding;

        let xScale, yScale; 

        // --- Логика для line и bar: оси и масштаб ---
        if (type === 'line' || type === 'bar') {
            // Масштабирование
            let maxVal = Math.max(...datasets.flatMap(ds => ds.data));
            maxVal = Math.ceil(maxVal / 10) * 10; // округление вверх до 10

            xScale = chartWidth / Math.max(labels.length - 1, 1);
            yScale = chartHeight / maxVal;

            // Сетка и ось Y
            ctx.strokeStyle = '#eee';
            ctx.font = '12px sans-serif';
            ctx.textAlign = 'right';
            for (let i = 0; i <= maxVal; i += Math.ceil(maxVal / 5)) {
                const y = padding + chartHeight - (i * yScale);
                ctx.beginPath();
                ctx.moveTo(padding, y);
                ctx.lineTo(padding + chartWidth, y);
                ctx.stroke();
                ctx.fillText(i, padding - 10, y + 4);
            }

            // Ось X
            const labelSpacing = chartWidth / labels.length; // Ширина одного сегмента
            labels.forEach((label, i) => {
                const x = padding + (i + 0.5) * labelSpacing; // Центрируем метку в сегменте
                ctx.fillText(label, x, height - 10);
            });

            // Рисуем данные
            datasets.forEach((dataset, dsIndex) => {
                const color = dataset.borderColor || this.getColor(dsIndex);
                ctx.strokeStyle = color;
                ctx.fillStyle = dataset.backgroundColor || color.replace('1)', '0.3)');
                ctx.lineWidth = dataset.borderWidth || 2;

                if (type === 'bar') {
                    this.drawBars(ctx, dataset.data, xScale, yScale, padding, chartWidth, chartHeight, dsIndex, datasets.length);
                } else {
                    this.drawLine(ctx, dataset.data, xScale, yScale, padding, chartHeight);
                }
            });
        }
        // --- Логика для pie/doughnut ---
        else if (type === 'pie' || type === 'doughnut') {
            const totalRadius = radius * 0.8;
            this.drawPie(ctx, datasets, centerX, centerY, radius, totalRadius);
        }
        // --- Логика для stacked-pie ---
        else if (type === 'stacked-pie') {
            this.drawStackedPie(ctx, datasets, centerX, centerY, radius);
        }
        // --- Другие типы (по умолчанию пусто) ---
        else {
            console.warn(`Тип диаграммы "${type}" не поддерживается`);
        }

        // Легенда (рисуем всегда)
        this.drawLegend(ctx, datasets, padding, width);
    },

    drawLine(ctx, data, xScale, yScale, padding, chartHeight) {
        ctx.beginPath();
        data.forEach((val, i) => {
            const x = padding + i * xScale;
            const y = padding + chartHeight - (val * yScale);
            if (i === 0) ctx.moveTo(x, y);
            else ctx.lineTo(x, y);
        });
        ctx.stroke();
        // Точки
        ctx.fillStyle = ctx.strokeStyle;
        data.forEach((val, i) => {
            const x = padding + i * xScale;
            const y = padding + chartHeight - (val * yScale);
            ctx.beginPath();
            ctx.arc(x, y, 4, 0, 2 * Math.PI);
            ctx.fill();
        });
    },

    drawBars(ctx, data, xScale, yScale, padding, chartWidth, chartHeight, index, total) {
        const labelsCount = data.length;
        const groupWidth = chartWidth * 0.8 / labelsCount; // Ширина одной группы
        const barWidth = groupWidth / total; // Ширина одного столбика
        const groupSpacing = chartWidth * 0.2 / (labelsCount + 1); // Отступ с краю и между группами

        data.forEach((val, i) => {
            const x = padding + groupSpacing + i * (groupWidth + groupSpacing) + index * barWidth;
            const y = padding + chartHeight - (val * yScale);
            const height = val * yScale;

            ctx.fillRect(x, y, barWidth, height);
            ctx.strokeRect(x, y, barWidth, height);
        });
    },

    /**
     * Рисует круговую диаграмму (pie) или кольцевую (doughnut)
     */
    drawPie(ctx, datasets, centerX, centerY, radius, totalRadius) {
        const datasetsCount = datasets.length;
        
        // Вычисляем общую сумму всех данных для определения веса каждого кольца
        const datasetTotals = datasets.map(ds => ds.data.reduce((a, b) => a + b, 0));
        const grandTotal = datasetTotals.reduce((a, b) => a + b, 0);

        let currentInnerRadius = radius - totalRadius;

        // Массив для хранения информации о границах секторов
        const boundaries = [];

        datasets.forEach((dataset, dsIndex) => {
            const color = dataset.borderColor || this.getColor(dsIndex);
            ctx.fillStyle = dataset.backgroundColor || color.replace('1)', '0.7)');
            
            let startAngle = 0;
            const values = dataset.data;
            const total = datasetTotals[dsIndex];

            // Пропорциональная толщина кольца
            const ringThickness = (total / grandTotal) * totalRadius;
            const outerRadius = currentInnerRadius + ringThickness;

            values.forEach((val, i) => {
                const sliceAngle = 2 * Math.PI * val / total;
                
                // Сохраняем параметры границы
                boundaries.push({
                    startAngle: startAngle,
                    innerRadius: currentInnerRadius,
                    outerRadius: outerRadius,
                    color: color
                });

                // Рисуем сектор
                ctx.beginPath();
                ctx.arc(centerX, centerY, outerRadius, startAngle, startAngle + sliceAngle);
                ctx.arc(centerX, centerY, currentInnerRadius, startAngle + sliceAngle, startAngle, true);
                ctx.closePath();
                ctx.fill();

                startAngle += sliceAngle;
            });

            // Переходим к следующему кольцу
            currentInnerRadius = outerRadius;
        });

        // --- После отрисовки всех колец рисуем границы ---
        boundaries.forEach(boundary => {
            ctx.beginPath();
            ctx.moveTo(
                centerX + boundary.innerRadius * Math.cos(boundary.startAngle),
                centerY + boundary.innerRadius * Math.sin(boundary.startAngle)
            );
            ctx.lineTo(
                centerX + boundary.outerRadius * Math.cos(boundary.startAngle),
                centerY + boundary.outerRadius * Math.sin(boundary.startAngle)
            );
            ctx.strokeStyle = '#fff'; // Белые линии для чёткости
            ctx.lineWidth = 2;
            ctx.stroke();
        });
    },

    /**
     * Рисует составную круговую диаграмму (stacked pie)
     * Один сектор — одна метка (например, день), 
     * внутри сектора — части по каждому датасету.
     */
    drawStackedPie(ctx, datasets, centerX, centerY, radius) {
        const labels = datasets[0].data.length; // Количество меток
        const totalByLabel = Array(labels).fill(0); // Сумма по каждой метке

        // Вычисляем общую сумму для каждого label
        datasets.forEach(dataset => {
            dataset.data.forEach((val, i) => {
                totalByLabel[i] += val;
            });
        });

        // Общая сумма всех данных
        const grandTotal = totalByLabel.reduce((a, b) => a + b, 0);

        let startAngle = 0;

        // Для каждой метки (сектора)
        for (let i = 0; i < labels; i++) {
            const sectorValue = totalByLabel[i];
            const sectorAngle = 2 * Math.PI * sectorValue / grandTotal;

            let sliceStartAngle = startAngle;

            // Рисуем части сектора по каждому датасету
            datasets.forEach((dataset, dsIndex) => {
                const value = dataset.data[i] || 0;
                if (value === 0) return;

                const sliceAngle = sectorAngle * (value / sectorValue);
                const color = dataset.borderColor || this.getColor(dsIndex);
                ctx.fillStyle = dataset.backgroundColor || color.replace('1)', '0.7)');

                ctx.beginPath();
                ctx.moveTo(centerX, centerY);
                ctx.arc(centerX, centerY, radius, sliceStartAngle, sliceStartAngle + sliceAngle);
                ctx.lineTo(centerX, centerY);
                ctx.fill();

                // Границы между частями сектора
                ctx.strokeStyle = '#fff';
                ctx.lineWidth = 1;
                ctx.beginPath();
                ctx.moveTo(centerX, centerY);
                ctx.lineTo(
                    centerX + radius * Math.cos(sliceStartAngle),
                    centerY + radius * Math.sin(sliceStartAngle)
                );
                ctx.stroke();

                sliceStartAngle += sliceAngle;
            });

            // Граница между секторами
            ctx.strokeStyle = '#000';
            ctx.lineWidth = 1;
            ctx.beginPath();
            ctx.moveTo(centerX, centerY);
            ctx.lineTo(
                centerX + 1.1 * radius * Math.cos(startAngle),
                centerY + 1.1 * radius * Math.sin(startAngle)
            );
            ctx.stroke();

            startAngle += sectorAngle;
        }
    },

    drawLegend(ctx, datasets, padding, width) {
        ctx.font = '14px sans-serif';
        ctx.textAlign = 'left';
        let x = padding;
        datasets.forEach(ds => {
            const color = ds.borderColor || this.getColor(datasets.indexOf(ds));
            ctx.fillStyle = color;
            ctx.fillRect(x, 10, 15, 15);
            ctx.fillStyle = '#000';
            ctx.fillText(ds.label || 'Данные', x + 20, 22);
            x += 100;
            if (x > width - 100) {
                x = padding;
                ctx.translate(0, 30);
            }
        });
    },

    /**
     * Генерирует уникальный цвет на основе индекса, используя "золотой угол".
     */
    getColor(index) {
        const hue = (index * 137.508) % 360; // Золотой угол для максимального разнообразия
        const saturation = 70 + (index % 4) * 5; // Варьируем насыщенность: 70%, 75%, 80%, 85%
        const lightness = 55 + Math.floor(index / 3) % 3 * 10; // Варьируем светлоту: 55%, 65%, 75%

        return this.hslToRgba(hue, saturation, lightness);
    },

    /**
     * Конвертирует HSL в строку RGBA.
     */
    hslToRgba(h, s, l, a = 0.7) {
        h /= 360;
        s /= 100;
        l /= 100;

        let r = l, g = l, b = l;

        if (s !== 0) {
            const q = l < 0.5 ? l * (1 + s) : l + s - l * s;
            const p = 2 * l - q;
            r = this.hue2rgb(p, q, h + 1/3);
            g = this.hue2rgb(p, q, h);
            b = this.hue2rgb(p, q, h - 1/3);
        }

        return `rgba(${Math.round(r * 255)}, ${Math.round(g * 255)}, ${Math.round(b * 255)}, ${a})`;
    },

    /**
     * Вспомогательная функция для преобразования оттенка в RGB.
     */
    hue2rgb(p, q, t) {
        if (t < 0) t += 1;
        if (t > 1) t -= 1;
        if (t < 1/6) return p + (q - p) * 6 * t;
        if (t < 1/2) return q;
        if (t < 2/3) return p + (q - p) * (2/3 - t) * 6;
        return p;
    },

    /**
     * Обновляет данные графика
     */
    update(id, newData) {
        if (this.charts[id]) {
            Object.assign(this.charts[id].config.data, newData);
            this.render(id);
        }
    },

    /**
     * Изменяет тип графика
     */
    setType(id, type) {
        if (this.charts[id]) {
            this.charts[id].config.type = type;
            this.render(id);
        }
    },

    /**
     * Добавляет новый набор данных
     */
    addDataset(id, dataset) {
        if (this.charts[id]) {
            this.charts[id].config.data.datasets.push(dataset);
            this.render(id);
        }
    },

    /**
     * Удаляет график
     */
    destroy(id) {
        if (this.charts[id]) {
            const canvas = this.charts[id].canvas;
            if (canvas && canvas.parentNode) {
                canvas.parentNode.removeChild(canvas);
            }
            delete this.charts[id];
        }
    }
}
