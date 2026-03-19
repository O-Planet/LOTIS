// Grid.js
const ltsGrid = {
    grids: {}, // { id: { modes, devices, currentDevice, activeMode } }

    init(id, modes, devices, priorityOrder) {
        this.grids[id] = {
            modes: modes,
            devices: devices,
            priorityOrder: priorityOrder,
            currentDevice: 'desktop',
            activeMode: null,
            wrapperDisplay: {}          
        };
    },

    grid(id) {
        return this.grids[id];
    },

    deviceQuery(id, device, func) {
        if (!this.grids[id]) return this;
        const fn = typeof func === 'function' ? func : new Function('return ' + func);
        this.grids[id].devices[device] = fn;
        return this;
    },

    setMode(id, modeName) {
        if (!this.grids[id]) return this;
        this.currentSetModeId = id;
        this.currentSetModeName = modeName;
        this.currentSetDeviceName = 'desktop';
        if (!this.grids[id].modes[modeName]) {
            this.grids[id].modes[modeName] = {};
        }
        return this;
    },

    device(device) {
        if (!this.currentSetModeId || !this.currentSetModeName) return this;
        
        const id = this.currentSetModeId;
        const mode = this.currentSetModeName;

        // Создаём объект режима для устройства, если его нет
        if (!this.grids[id].modes[mode][device]) {
            this.grids[id].modes[mode][device] = {};
        }

        this.currentSetDeviceName = device;
        return this;
    },

    priority(id, orderString) {
        const grid = this.grids[id];
        if (!grid) return this;

        const names = orderString.split(',').map(s => s.trim());
        const newPriorityOrder = {};

        names.forEach((name, index) => {
            if (grid.devices[name]) {
                newPriorityOrder[name] = index;
            }
        });

        grid.priorityOrder = newPriorityOrder;

        return this;
    },

    _setConfig(key, value) {
        const id = this.currentSetModeId;
        const mode = this.currentSetModeName;
        const dev = this.currentSetDeviceName;

        if (!id || !mode) return;

        if (!this.grids[id].modes[mode][dev]) {
            this.grids[id].modes[mode][dev] = {};
        }
        this.grids[id].modes[mode][dev][key] = value;
    },

    areas(areas) { this._setConfig('areas', areas); return this; },
    rows(rows)   { this._setConfig('rows', rows);     return this; },
    columns(cols){ this._setConfig('columns', cols);  return this; },

    getActiveDevice(id) {
        const grid = this.grids[id];
        if (!grid) return 'desktop';

        const devices = Object.entries(grid.devices);

        // Сортируем: сначала те, что в priorityOrder (по индексу), потом остальные
        devices.sort((a, b) => {
            const prioA = grid.priorityOrder[a[0]];
            const prioB = grid.priorityOrder[b[0]];

            if (prioA !== undefined && prioB !== undefined) return prioA - prioB;
            if (prioA !== undefined) return -1;
            if (prioB !== undefined) return 1;
            return 0; // Остальные без приоритета — в порядке добавления
        });

        for (const [name, func] of devices) {
            try {
                if (func()) return name;
            } catch (e) {
                console.error('Ошибка в условии устройства:', name, e);
            }
        }
        return 'desktop';
    },

    _getAreasFromConfig(config) {
        const areasSet = new Set();
        if (!config) return areasSet;

        config.areas?.forEach(line => {
            line.split(' ').forEach(area => {
                if (area.trim()) areasSet.add(area.trim());
            });
        });

        return areasSet;
    },

    _hideWrapper(areaId) {
        const $wrapper = $(`#${areaId}`);
        if ($wrapper.length > 0) {
            const id = areaId.split('_area_')[0]; // Извлекаем id Grid
            const grid = this.grids[id];
            if (grid && !grid.wrapperDisplay[areaId]) {
                const wrapperDisplay = $wrapper.css('display') || 'block';
                grid.wrapperDisplay[areaId] = wrapperDisplay == 'none' ? 'block' : wrapperDisplay;
            }
            $wrapper.css('display', 'none');
        }
    },

    _showWrapper(areaId) {
        const $wrapper = $(`#${areaId}`);
        if ($wrapper.length === 0) return;

        const wrapperDisplay = $wrapper.css('display') || 'block';
        if(wrapperDisplay == 'none')
        {
            const id = areaId.split('_area_')[0];
            const grid = this.grids[id];
            const savedDisplay = grid?.wrapperDisplay[areaId];
            $wrapper.css('display', savedDisplay || 'block');
        }
    },

    check(id, f) {
        const grid = this.grids[id];
        if (grid) grid.checkhuck = f;
        return this;
    },
    before(id, f) {
        const grid = this.grids[id];
        if (grid) grid.beforehuck = f;
        return this;
    },
    on(id, f) {
        const grid = this.grids[id];
        if (grid) grid.onhuck = f;
        return this;
    },

    mode(id, name) {
        const grid = this.grids[id];
        if (!grid || !grid.modes[name]) return;

        const device = this.getActiveDevice(id);
        const newConfig = grid.modes[name][device] || grid.modes[name]['desktop'];

        if(grid.checkhuck && ! grid.checkhuck(name, device)) return;
        if(grid.beforehuck) grid.beforehuck(name, device);

        if (newConfig) {
            // Получаем множество новых активных областей
            const newAreas = this._getAreasFromConfig(newConfig);

            // Находим все врапперы этого grid
            const prefix = `${id}_area_`;
            const allWrapperIds = document.querySelectorAll(`[id^="${prefix}"]`);

            allWrapperIds.forEach(wrapper => {
                const areaId = wrapper.id;
                const areaName = areaId.substring(prefix.length);

                // Если эта область НЕ входит в новый список — скрываем
                if (!newAreas.has(areaName)) {
                    this._hideWrapper(areaId);
                }
                // Если входит и была скрыта — показываем
                else {
                    this._showWrapper(areaId);
                }
            });

            // Применяем стили грида
            const el = document.getElementById(id);
            if (el) {
                if (newConfig.areas)
                    el.style.gridTemplateAreas = newConfig.areas.map(line => `"${line}"`).join(' ');
                if (newConfig.rows)
                    el.style.gridTemplateRows = newConfig.rows;
                if (newConfig.columns)
                    el.style.gridTemplateColumns = newConfig.columns;
            }
        }

        // Обновляем состояние
        grid.activeMode = name;
        grid.currentDevice = device;

        if(grid.onhuck) grid.onhuck(name, device);
    },

    handleResize() {
        Object.keys(this.grids).forEach(id => {
            const grid = this.grids[id];
            if (grid.activeMode) {
                const currentDevice = this.getActiveDevice(id) || 'desktop';
                if (currentDevice !== grid.currentDevice) {
                    this.mode(id, grid.activeMode);
                }
            }
        });
    }
};

// Инициализация
document.addEventListener('DOMContentLoaded', () => {
    window.ltsGrid = ltsGrid;
    window.addEventListener('resize', () => ltsGrid.handleResize());
});