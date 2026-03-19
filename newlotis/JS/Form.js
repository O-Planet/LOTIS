const ltsForm = {
    values: function (formId, vals)
    {
        var formData = {};

        jQuery('#' + formId).find('input, select, textarea').each(function() 
            {
                var el = $(this);
                var type = el.attr('type');
                var name = el.attr('name');

                if (!name) return;

                // Проверяем, что элемент не является кнопкой
                if (type !== 'button' && type !== 'submit' && type !== 'reset') 
                    if (type === 'checkbox') 
                    {
                        if(vals !== undefined)
                            if(name in vals)
                                el.prop('checked', vals[name] ? true : false);
                            else
                                el.prop('checked', false);
                            
                        // Для чекбоксов добавляем 1 или 0 в зависимости от состояния
                        formData[name] = el.is(':checked') ? 1 : 0;
                    }
                    else 
                        if (type === 'radio')
                        { 
                            if(vals !== undefined)
                                if(name in vals)
                                    el.prop('checked', el.val() == vals[name]);
                                else
                                    el.prop('checked', false);    

                            if(!el.is(':checked'))    
                                return; // Пропускаем невыбранные радиобоксы
                            else
                                formData[name] = el.val();
                        }
                        else 
                        {
                            const lookupid = el.attr('lookupfield');
                            if(lookupid)
                            {
                                let lookup = LTS.get(lookupid);
                                if(vals !== undefined)
                                    if(name in vals && vals[name]) 
                                    {                            
                                        if(typeof vals[name] == 'object')
                                            lookup.selected = vals[name];
                                        else
                                        {
                                            const fieldmap = lookup.fieldmap;
                                            const selectedObj = Object.fromEntries(
                                                Object.entries(fieldmap)
                                                    .filter(([key, valueKey]) => valueKey != null && valueKey in vals)
                                                    .map(([key, valueKey]) => [key, vals[valueKey]])
                                            );
                                            if(selectedObj)
                                                lookup.selected = selectedObj;
                                        }
                                        if(lookup.selected)        
                                            el.val(ltsForm.normalizeInputValue(type, lookup.selected[lookup.searchfield]));
                                        else
                                            el.val('');
                                    }
                                    else   
                                    {
                                        lookup.selected = null; 
                                        el.val('');
                                    }

                                if(lookup.selected)
                                    formData[name] = lookup.selected['id'];
                                else
                                    formData[name] = '';
                                //formData[name + '_presentation'] = el.val();
                            }
                            else
                            {
                                if(vals !== undefined)
                                    if(name in vals)
                                        el.val(ltsForm.normalizeInputValue(type, vals[name]));
                                    else    
                                        el.val('');

                                // Для остальных элементов просто добавляем значение
                                const val = el.val();
                                formData[name] = val ? val : '';
                            }
                        }
            });

        return formData;
    },

    data : function (formId) 
    {
        // Создаем новый объект FormData
        var combinedData = new FormData();

        jQuery('#' + formId).find('input, select, textarea').each(function() 
        {
            var el = $(this);
            var type = el.attr('type');
            var name = el.attr('name');

            // Проверяем, что элемент не является кнопкой
            if (type !== 'button' && type !== 'submit' && type !== 'reset') 
                if (type === 'checkbox') 
                    // Для чекбоксов добавляем 1 или 0 в зависимости от состояния
                    combinedData.append(name, el.is(':checked') ? 1 : 0);
                else 
                    if (type === 'radio')
                        if(!el.is(':checked'))    
                            return; // Пропускаем невыбранные радиобоксы
                        else
                            combinedData.append(name, el.val());
                    else 
                        if (type === 'file')
                        {
                            // Если это поле для загрузки файла, добавляем файл
                            var elfile = el[0].files[0]; // Получаем первый файл
                            if (elfile)
                                combinedData.append(name, elfile); // Добавляем файл
                        }
                        else
                        {
                            // если это LookupField
                            const lookupid = el.attr('lookupfield');
                            if(lookupid)
                            {
                                const lookup = LTS.get(lookupid);
                                if(lookup.selected)
                                    combinedData.append(name, lookup.selected['id']);
                                else
                                    combinedData.append(name, '');
                                //combinedData.append(name + '_presentation', el.val());
                            }
                            else {
                            // Для остальных элементов просто добавляем значение
                                const val = el.val();
                                combinedData.append(name, val ? val : '');
                            }
                        }
        });

        return combinedData;
    },

    clear: function (formId)
    {
        this.values(formId, {});
    },

    value: function (formId, name, val) 
    {
        var value = '';
    
        // Находим элемент по имени в указанной форме
        var element = jQuery('#' + formId).find('[name="' + name + '"]');
        
        // Проверяем тип элемента
        if (element.length) 
        {
            var type = element.attr('type');
            
            // Обрабатываем разные типы элементов
            if (type == 'button' || type == 'submit' || type == 'reset') 
                return '';

            if (type === 'checkbox') 
                if(val !== undefined)
                    // Устанавливаем состояние чекбокса
                    element.prop('checked', val ? true : false);
                else
                    // Возвращаем состояние чекбокса
                    value = element.is(':checked') ? 1 : 0;
            else 
                if (type === 'radio') 
                    if(val !== undefined)
                        // Устанавливаем выбранное значение для радио-кнопок
                        element.each(function() {
                            jQuery(this).prop('checked', jQuery(this).val() == val);
                        });
                    else               
                    {
                        // Для группы радиокнопок возвращаем значение отмеченной
                        var checkedRadio = element.filter(':checked');
                        if (checkedRadio.length) 
                            value = checkedRadio.val();
                    } 
                else
                { 
                    // если это LookupField
                    const lookupid = element.attr('lookupfield');
                    if(lookupid)
                    {
                        let lookup = LTS.get(lookupid);
                        if(val !== undefined)
                        {
                            if(typeof val == 'object')
                                lookup.selected = val;
                            else
                                if(! val)
                                    lookup.selected = null;
                            if(lookup.selected)
                                element.val(this.normalizeInputValue(type, lookup.selected[lookup.searchfield]));
                            else
                                element.val('');
                        }
                        
                        if(lookup.selected)
                            value = lookup.selected['id'];
                        else
                            value = '';
                    }
                    else 
                    {
                        if(val !== undefined)
                            // Для остальных типов просто устанавливаем значение
                            element.val(this.normalizeInputValue(type, val));
                        
                        // Для остальных типов просто получаем значение
                        value = element.val();
                    }
                }
        }
        
        return value ? value : '';        
    }, 

    /**
     * Преобразует строковое значение под формат HTML5-поля указанного типа.
     *
     * @param {string} type - тип input'а (например: 'date', 'time', 'email', 'number' и т.д.)
     * @param {string} val  - исходное строковое значение
     * @returns {string|null} - корректное значение для input.value или null, если преобразование невозможно
     */
    normalizeInputValue : function (type, val) 
    {
        if (val == null) return null;
        const str = String(val).trim();

        switch (type) {
            // --- Дата и время ---
            case 'date':
                // Ожидается YYYY-MM-DD
                const dateMatch = str.match(/^(\d{4})-(\d{2})-(\d{2})/);
                if (dateMatch) {
                    const [_, y, m, d] = dateMatch;
                    const date = new Date(`${y}-${m}-${d}T00:00:00`);
                    // Проверка валидности даты
                    if (date.getFullYear() == y && date.getMonth() + 1 == m && date.getDate() == d) {
                        return `${y}-${m}-${d}`;
                    }
                }
                return null;

            case 'time':
                // Поддерживаем HH:mm и HH:mm:ss
                const timeMatch = str.match(/^(\d{2}):(\d{2})(?::(\d{2}))?/);
                if (timeMatch) {
                    const [_, h, min, sec = '00'] = timeMatch;
                    const hour = parseInt(h, 10);
                    const minute = parseInt(min, 10);
                    const second = parseInt(sec, 10);
                    if (hour >= 0 && hour <= 23 && minute >= 0 && minute <= 59 && second >= 0 && second <= 59) {
                        return sec ? `${h}:${min}:${sec}` : `${h}:${min}`;
                    }
                }
                return null;

            case 'datetime-local':
                // Формат: YYYY-MM-DDTHH:mm или YYYY-MM-DDTHH:mm:ss
                const dtMatch = str.match(/^(\d{4})-(\d{2})-(\d{2})[T\s](\d{2}):(\d{2})(?::(\d{2}))?/);
                if (dtMatch) {
                    const [_, y, m, d, h, min, sec = '00'] = dtMatch;
                    const date = new Date(`${y}-${m}-${d}T${h}:${min}:${sec}`);
                    if (
                        date.getFullYear() == y &&
                        date.getMonth() + 1 == m &&
                        date.getDate() == d &&
                        date.getHours() == h &&
                        date.getMinutes() == min &&
                        date.getSeconds() == sec
                    ) {
                        return sec ? `${y}-${m}-${d}T${h}:${min}:${sec}` : `${y}-${m}-${d}T${h}:${min}`;
                    }
                }
                return null;

            case 'month':
                // YYYY-MM
                const monthMatch = str.match(/^(\d{4})-(\d{2})/);
                if (monthMatch) {
                    const [_, y, m] = monthMatch;
                    const month = parseInt(m, 10);
                    if (month >= 1 && month <= 12) {
                        return `${y}-${m}`;
                    }
                }
                return null;

            case 'week':
                // YYYY-Www (неделя от 01 до 53)
                const weekMatch = str.match(/^(\d{4})-W(\d{2})/);
                if (weekMatch) {
                    const [_, y, w] = weekMatch;
                    const week = parseInt(w, 10);
                    if (week >= 1 && week <= 53) {
                        return `${y}-W${w}`;
                    }
                }
                return null;

            // --- Числа ---
            case 'number':
            case 'range':
                const num = parseFloat(str);
                if (!isNaN(num) && isFinite(num)) {
                    return String(num);
                }
                return null;

            // --- URL ---
            case 'url':
                try {
                    // Простая проверка: должен содержать протокол
                    const url = new URL(str);
                    return url.href;
                } catch (e) {
                    // Если нет протокола — попробуем добавить http://
                    try {
                        const url = new URL('http://' + str);
                        return url.href;
                    } catch (_) {
                        return null;
                    }
                }

            // --- Email ---
            case 'email':
                // Простая проверка на наличие @ и точки в домене (не идеально, но достаточно для большинства случаев)
                if (/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(str)) {
                    return str;
                }
                return null;

            // --- Color ---
            case 'color':
                if (/^#[0-9A-Fa-f]{6}$/.test(str)) {
                    return str.toLowerCase();
                }
                return null;

            // --- Типы, не требующие преобразования ---
            case 'text':
            case 'password':
            case 'search':
            case 'tel':
            case 'hidden':
                return str;

            // --- По умолчанию: оставляем как есть (но можно вернуть null для строгости) ---
            default:
                return str;
        }
    }
};