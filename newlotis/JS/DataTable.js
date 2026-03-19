const ltsDataTable = 
{
    rows : {},
    heads : {},
    hidden : {},
    sorted : {},
    hucks : {},
    dataids : {},

    add : function (id, head, rows, hidden)
    {
        this.heads[id] = head;
        this.rows[id] = rows;
        this.hidden[id] = hidden;
        this.sorted[id] = [];
    },

    create : function(id, norows) {
        // Создаем таблицу и фиксированный заголовок
        var $table = $('#' + id);

        // Определяем или получаем thead и tbody
        var $thead = $table.find('thead');
        var $tbody = $table.find('tbody');

        // Если thead нет — создаём
        if ($thead.length === 0) 
            $thead = $('<thead>').appendTo($table);
        else 
            $thead.empty();

        // Если tbody нет — создаём
        if ($tbody.length === 0) 
            $tbody = $('<tbody>').appendTo($table);
        else 
            $tbody.empty();
            
        var headers = this.heads[id];
        var dataArray = this.rows[id];
        var hidden = this.hidden[id];
        var huck = this.hucks[id];
        var sorted = this.sorted[id];

        var sortTypes = {};

        if (sorted) {
            const sortArray = Array.isArray(sorted) ? sorted : [sorted];

            for (const item of sortArray) {
                const match = item.match(/^(.+?)\s+as\s+(.+)$/i);
                if (match) {
                    const field = match[1].trim();
                    const type = match[2].trim().toLowerCase();
                    sortTypes[field] = type;
                }
                else
                    sortTypes[item] = 'string';
            }
        }

        // Создаем заголовки таблицы
        var $headerRow = $('<tr>');
        $.each(headers, function(columnName, headerTitle) {
            var $th = $('<th>').text(headerTitle)
                .addClass('Column_' + columnName)
                .attr('data-field', columnName);
            if(columnName in sortTypes) {
                $th.addClass('ltsSorted');
                $th.attr('data-type', sortTypes[columnName]);
            }
            $headerRow.append($th);
        });
        $thead.append($headerRow);
    
        let dataid = 0;

        // Заполняем таблицу данными
        if(! norows)
        $.each(dataArray, function(index, dataObject) {
            var $row = $('<tr>');
            $row.attr('data-id', ++dataid);
            dataObject['ltsDataId'] = dataid;
            $.each(hidden, function(index, property) {
                if (dataObject.hasOwnProperty(property)) {
                    $row.attr(property, dataObject[property]);
                }
            });
            $.each(headers, function(columnName) {
                var $td = $('<td>').html(dataObject[columnName])
                    .addClass('Column_' + columnName)
                    .attr('data-field', columnName);
                $row.append($td);
            });
            if(typeof huck === 'function')
                huck($row, dataObject);
            $tbody.append($row);
        });

        this.dataids[id] = dataid;    
    },

    append : function (id, dataObject) {
        var headers = this.heads[id];
        var hidden = this.hidden[id];
        var huck = this.hucks[id];
        var rows = this.rows[id];

        var dataArray = [];

        dataArray = this.deepCopyArray(dataObject); 

        let dataid = this.dataids[id] ? this.dataids[id] : 0;

        $.each(dataArray, function (index, dataobj) {
            dataobj['ltsDataId'] = ++dataid;
            rows.push(dataobj);
            var $row = $('<tr>');
            $row.attr('data-id', dataid);
            $.each(hidden, function(index, property) {
                if (dataobj.hasOwnProperty(property)) {
                    $row.attr(property, dataobj[property]);
                }
            });
            $.each(headers, function(columnName) {
                var $td = $('<td>').html(dataobj[columnName])
                    .addClass('Column_' + columnName)
                    .attr('data-field', columnName);
                $row.append($td);
            });
            if(huck && typeof huck === 'function')
                huck($row, dataobj);

            $('#' + id + ' tbody').append($row);
        });

        this.dataids[id] = dataid;
    },

    lastdataid : function (id) { return this.dataids[id] ? this.dataids[id] : false; },

    sort: function (id, name, direction) {
        const $table = $('#' + id);
        if (!$table.length) return;

        const $th = $table.find('thead th[data-field="' + name + '"]');
        const type = $th.data('type') || 'string';

        const parseValue = (value) => {
            if (value == null || value === '') return null;

            switch (type) {
                case 'int': case 'number': return +value || 0;
                case 'float': case 'decimal': return parseFloat(value) || 0;
                case 'date': return this.tryParseDate(value);
                default: return String(value).toLowerCase();
            }
        };

        // Сортируем все строки
        this.rows[id].sort((a, b) => {
            const valA = parseValue(a[name]);
            const valB = parseValue(b[name]);

            // Пустые значения — в конец
            if (valA == null && valB != null) return 1 * direction;
            if (valB == null && valA != null) return -1 * direction;
            if (valA == null && valB == null) return 0;

            if (typeof valA === 'string' && typeof valB === 'string') 
                return direction * valA.localeCompare(valB);
            else
                return direction * (valA - valB);
        });

        // Теперь обновляем DOM, не пересоздавая всю таблицу
        this.reorderTbody(id);
    },

    reorderTbody: function (id) {
        const $table = $('#' + id);
        if (!$table.length) return;

        const $tbody = $table.find('tbody');
        const rowsMap = {};
        
        // Сохраняем строки в map по data-id
        $tbody.find('tr').each(function () {
            const rowId = $(this).data('id');
            if (rowId !== undefined) {
                rowsMap[rowId] = $(this);
            }
        });

        // Очищаем tbody
        $tbody.empty();

        // Восстанавливаем в порядке rows[id]
        this.rows[id].forEach(row => {
            const $existingRow = rowsMap[row.ltsDataId];
            if ($existingRow) {
                $tbody.append($existingRow);
            }
        });
    },

    deepCopyArray : function (originalArray) {
        var result;
        if (typeof structuredClone === 'function') {
            // Поддерживается structuredClone
            result = structuredClone(originalArray);
        } else if (typeof jQuery !== 'undefined' && typeof jQuery.extend === 'function') {
            // Используем jQuery.extend как fallback
            result = jQuery.extend(true, [], originalArray);
        } else {
            // Резервный вариант: JSON.parse(JSON.stringify(...))
            result = JSON.parse(JSON.stringify(originalArray));
        }
        return Array.isArray(result) ? result : [ result ];
    },

    values : function (id, attr, values) {
        const tableRows = this.rows[id] || [];

        // Шаг 1: Если нет фильтра и обновления — возвращаем исходные данные
        if (!attr && !values) return tableRows;

        let filtered = [];

        // Шаг 2: Фильтрация по HTML-строке
        if(attr instanceof HTMLElement) { 
            const dataid = attr.dataset.id;
            const found = tableRows.find(row => row.ltsDataId == dataid);
            if (found) { 
                if (values) 
                    Object.assign(found, values);
                filtered = [ found ];
            }

        // Шаг 3: Фильтрация по jQuery-объекту
        } else if (attr instanceof jQuery) {
            const ids = attr.map(function () {
                return $(this).data('id');
            }).get();

            filtered = tableRows.filter(row => {
                const match = ids.includes(row.ltsDataId);
                if (match && values) Object.assign(row, values); // Мутируем
                return match;
            });

        // Шаг 4: Фильтрация по массиву объектов, например, результат values()
        } else if (attr && Array.isArray(attr)) {
            const ids = attr.map(item => item['ltsDataId']);

            filtered = tableRows.filter(row => {
                const match = ids.includes(row.ltsDataId);
                if (match && values) Object.assign(row, values); // Мутируем
                return match;
            });

        // Шаг 5: Фильтрация по объекту { key: value }
        } else if (attr && typeof attr === 'object') {
            filtered = tableRows.filter(row => {
                const match = Object.entries(attr).every(([key, val]) => {
                    if (typeof val === 'number') 
                        return Number(row[key]) === val;
                    return String(row[key]) === String(val);
                });
                if (match && values) Object.assign(row, values); // Мутируем
                return match;
            });

        // Шаг 6: Если нет фильтра, но есть обновление — обновляем все строки
        } else if (values) {
            filtered = tableRows.map(row => {
                return Object.assign({}, row, values); // Создаём новые объекты
            });
        }

        // Шаг 7: Возврат, если нет values
        if (!values) 
            return filtered;

        // Шаг 8: Отбираем строки для обновления
        const heads = this.heads[id];
        const hidden = this.hidden[id];
        const huck = this.hucks[id];

        const $rows = $(`table#${id} tr`).filter(function() {
            const dataId = $(this).data('id');
            return filtered.some(row => row.ltsDataId == dataId);
        });

        // Шаг 9: Обновляем ячейки по классам
        $rows.each(function () {
            const $row = $(this);

            // Перебираем все пары name: value из values
            Object.entries(values).forEach(([key, value]) => {
                if(key != 'ltsDataId')
                    if (heads.hasOwnProperty(key)) 
                    {
                        const columnClass = `.Column_${key}`;
                        const $cell = $row.find(columnClass);

                        if ($cell.length) 
                            $cell.html(value); // Устанавливаем HTML
                    }
                    else 
                        if(hidden.hasOwnProperty(key)) 
                            if (value === null) 
                                $row.removeAttr(key);
                            else
                                $row.attr(key, value);                
            });

            if(huck && typeof huck === 'function')
            {
                const rowid = $row.data('id');
                let foundrow = tableRows.find(item  => item.ltsDataId == rowid);
                huck($row, foundrow);
            }
        });

        return filtered;
    },

    Row: function (id, dataid) {
        let _dataid = dataid;
        if(typeof dataid === 'object')
            _dataid = dataid['ltsDataId'];
        return $('#' + id + ' tbody tr[data-id="' + _dataid + '"]');
    },

    findRows: function (id, attr) {
        const selector = 'table#' + id + ' tbody tr';

        // Если attr не задан — возвращаем все строки
        if (!attr) {
            return $(selector);
        }

        const tableRows = this.rows[id];
        if (!tableRows) return $([]); // пустой jQuery-объект

        let idsToSelect = [];

        // attr = HTML-строка
        if(attr instanceof HTMLElement) { 
            const dataid = attr.dataset.id;
            const found = tableRows.find(row => row.ltsDataId == dataid);
            if (found)  
                idsToSelect = [ found ];

        // attr - jQuery-объект
        } else if (attr instanceof jQuery) {
            const ids = attr.map(function () {
                return $(this).data('id');
            }).get();

            idsToSelect = tableRows.filter(row => ids.includes(row.ltsDataId));
        }

        // attr — массив объектов (например, результат values())
        else if (Array.isArray(attr)) 
            idsToSelect = attr.map(item => item.ltsDataId);

        // attr — объект { поле: значение } для фильтрации
        else if (attr !== null && typeof attr === 'object') {
            const filtered = tableRows.filter(row => {
                return Object.entries(attr).every(([key, val]) => {
                    const rowValue = row[key];
                    if (typeof val === 'number') {
                        return Number(rowValue) === val;
                    }
                    return String(rowValue) === String(val);
                });
            });
            idsToSelect = filtered.map(row => row.ltsDataId);
        }
        // Неизвестный тип attr
        else {
            return $([]);
        }

        // Если есть ID для выборки — фильтруем DOM-строки по data-id
        if (idsToSelect.length > 0) {
            return $(selector).filter(function () {
                const dataId = $(this).data('id');
                return dataId && idsToSelect.includes(dataId);
            });
        }

        // Если нет подходящих строк — возвращаем пустой jQuery-объект
        return $([]);
    },

    clear : function (id, attr) {
        var selector = 'table#' + id + " tbody tr";

        if(! attr)
        {
            // Удаляем строки из DOM
            $(selector).remove();
            this.rows[id] = [];
            this.dataids[id] = 0;
            return;
        }

        const tableRows = this.rows[id];
        if (!tableRows) return;

        let idsToRemove = [];

        // Случай 1: attr — это HTML-элемент tr
        if(attr instanceof HTMLElement) { 
            idsToRemove = [ attr.dataset.id ];
        }
        // Случай 2: attr — это jQuery-объект (например, $('tr.selected'))
        else if (attr instanceof jQuery) {
            idsToRemove = attr.map(function () {
                return $(this).data('id');
            }).get();
       // Случай 3: attr — массив объектов, полученных через value()
        } else if (attr !== null && Array.isArray(attr)) {
            idsToRemove = attr.map(item => item.ltsDataId);
       // Случай 4: attr — это объект { key: value }
        } else if (typeof attr === 'object' && attr !== null) {
            const filtered = tableRows.filter(row => {
                return Object.entries(attr).every(([key, val]) => {
                    if (typeof val === 'number') {
                        return Number(row[key]) === val;
                    }
                    return String(row[key]) === String(val);
                });
            });
            idsToRemove = filtered.map(row => row.ltsDataId);
        } 

        // Шаг 1: Удаление из this.rows[id]
        this.rows[id] = tableRows.filter(row => !idsToRemove.includes(row.ltsDataId));

        // Шаг 2: Удаление из DOM
        if (idsToRemove.length > 0) {
            $(selector).filter(function () {
                const dataId = $(this).data('id');
                return dataId && idsToRemove.includes(dataId);
            }).remove();
        }
    },
    
    fieldvalues: function (id, fieldName, selectedrows) {
        const rows = this.rows[id];
        if (!rows || !Array.isArray(rows)) return [];

        let filteredRows = rows;

        // Если передан selectedrows
        if (selectedrows) {
            let idsToInclude = [];

            if (Array.isArray(selectedrows)) {
                // Массив объектов (например, результат values() или filter())
                idsToInclude = selectedrows.map(item => item.ltsDataId).filter(id => id !== undefined);
            } else if (selectedrows instanceof jQuery) {
                // jQuery-объект: извлекаем data-id из tr
                idsToInclude = selectedrows.map(function () {
                    return $(this).data('id');
                }).get(); // .get() превращает в обычный массив
            }
            else if(selectedrows instanceof HTMLElement) { 
                // HTML элемент tr
                idsToInclude = [ selectedrows.dataset.id ];
            }

            // Фильтруем строки по ltsDataId
            filteredRows = rows.filter(row => idsToInclude.includes(row.ltsDataId));
        }

        // Извлекаем значения поля и возвращаем уникальные
        return [...new Set(filteredRows.map(row => row[fieldName]).filter(value => value !== undefined))];
    },

    filter: function (id, rules) {
        const tableRows = this.rows[id] || [];
        
        return tableRows.filter(row => {
            return Object.entries(rules).every(([key, value]) => {
                const rowValue = row[key];
                return this.valuesCompare(rowValue, value);
            });
        });
    },

    valuesCompare: function (a, b) {
        // Пустая строка — всегда совпадает
        if (typeof b === 'string' && b.trim() === '') {
            return true;
        }

        // Если b — регулярка
        if (b instanceof RegExp) {
            return b.test(a);
        }

        // Проверяем, являются ли оба значения датами
        const dateA = this.tryParseDate(a);
        const dateB = this.tryParseDate(b);

        if (dateA && dateB) {
            return (
                dateA.getFullYear() === dateB.getFullYear() &&
                dateA.getMonth() === dateB.getMonth() &&
                dateA.getDate() === dateB.getDate()
            );
        }

        // Строка содержит подстроку
        if (typeof b === 'string' || typeof a === 'string') {
            const strA = String(a);
            const strB = String(b);
            return strA.includes(strB);
        }

        // Числа
        if (typeof b === 'number') {
            return a === b;
        }

        // По умолчанию — строгое сравнение
        return a === b;
    },

    tryParseDate: function (input) {
        if (input instanceof Date) return input;
        if (typeof input !== 'string') return null;

        const dateRegex = /^(\d{1,2})\.(\d{1,2})\.(\d{4})(?:\s+(\d{1,2}):(\d{2})(?::(\d{2}))?)?$/;
        const match = input.match(dateRegex);

        if (match) {
            const day = parseInt(match[1], 10);
            const month = parseInt(match[2], 10) - 1;
            const year = parseInt(match[3], 10);
            const date = new Date(year, month, day);
            return isNaN(date.getTime()) ? null : date;
        }

        const isoDateMatch = input.match(/^(\d{4})-(\d{2})-(\d{2})$/);
        if (isoDateMatch) {
            const year = parseInt(isoDateMatch[1], 10);
            const month = parseInt(isoDateMatch[2], 10) - 1;
            const day = parseInt(isoDateMatch[3], 10);
            const date = new Date(year, month, day);
            return isNaN(date.getTime()) ? null : date;
        }

        if (/^\d+$/.test(input.trim()) && input.length < 6) {
            return null;
        }

        const parsed = Date.parse(input);
        if (!isNaN(parsed)) {
            const date = new Date(parsed);
            return isNaN(date.getTime()) ? null : date;
        }

        return null;
    },

    // Обновление DOM — принимает результат filter(...)
    applyFilter: function (id, filtered) {
        const $table = $('#' + id);
        if (!$table.length) return;

        // Показываем только те строки, что прошли фильтр
        $table.find('tbody tr').each(function () {
            const $tr = $(this);
            const rowId = $tr.data('id'); 
            const match = filtered.some(r => r.ltsDataId == rowId);

            $tr.toggle(match); // показываем или скрываем
        });
    },

    // Комбинация: фильтруем и обновляем
    filterAndApply: function (id, rules) {
        // Проверяем, пустой ли фильтр
        const isEmpty = !rules || Object.values(rules).every(val => 
            val === null || 
            val === undefined || 
            (typeof val === 'string' && val.trim() === '')
        );
        if (isEmpty) 
                this.clearFilter(id); // показываем все строки
        else {
            const filtered = this.filter(id, rules);
            this.applyFilter(id, filtered);
        }
    },

    clearFilter: function (id) {
        $('#' + id + ' tbody tr').show(); // показываем все строки
    }
}

$(document).ready(function() {
    $(document).on('click', 'table.data-table thead th.ltsSorted', function () {
        var $th = $(this);
        var $table = $th.closest('table');
        var tableId = $table.attr('id');

        if (!tableId) return;

        // Ищем имя колонки
        var columnName = $th.data('field');
        if(! columnName)
            return;

        // Получаем текущее направление сортировки из data-атрибута
        var currentOrder = parseInt($th.attr('data-sort-order'), 10);

        // Если нет значения или не число — ставим 1 (по возрастанию)
        var newOrder = isNaN(currentOrder) ? 1 : (currentOrder === 1 ? -1 : 1);

        // Удаляем у других колонок этот атрибут (если нужна только одна активная сортировка)
        $th.siblings().removeAttr('data-sort-order');

        // Обновляем атрибут на новый порядок
        $th.attr('data-sort-order', newOrder);

        // Сортируем
        ltsDataTable.sort(tableId, columnName, newOrder);
    });

    $(document).on('click', 'table.data-table tbody td.ltsEditable', function(event) {
        event.stopPropagation(); 
        let $td = $(this);
        const field = $td.data('field');
        const $tr = $td.closest('tr');
        const idtable = $td.closest('table').attr('id'); 
        LTS.get(idtable).selectedrow = $tr;
        const currentText = $td.text();
        let $input = $('<input type="text" class="full-width-input"/>').val(currentText).appendTo($td.empty()).focus();
        $input.on('blur keypress', function(e) {
            if (e.type === 'blur' || (e.type === 'keypress' && e.which === 13)) {
                const newText = $input.val();
                ltsDataTable.values(idtable, $tr, { [field] : newText });
                LTS.signal(idtable + '_ElementSave');
            }
        });
    });

    $(document).on('click', 'table.data-table tbody .ltsRowDelbutton', function(event) {
        event.stopPropagation(); 
        const $tr = $(this).closest('tr');
        const idtable = $tr.closest('table').attr('id');
        const iddataview = $tr.closest('table').attr('dataview');
        if(iddataview in LTS.objects && Object.hasOwn(LTS.objects[iddataview], 'rowsdelete')) {
            const dataview = LTS.get(iddataview);
            const row = dataview.values($tr);
            dataview.rowsdelete(row);
        }
        else
        if(idtable in LTS.objects && Object.hasOwn(LTS.objects[idtable], 'clear')) 
            LTS.objects[idtable].clear($tr);
        else {    
            ltsDataTable.clear(idtable, $tr); 
            LTS.signal(idtable + '_RowsDel');
        }
    });
});
