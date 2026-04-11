class LTSObject {
    constructor(id) {
        this.id = id;
        this.param = {};
    }

    check(f) { this.checkhuck = f; return this; }
    before(f) { this.beforehuck = f; return this; }
    on(f) { this.onhuck = f; return this; }
    jQuery() { return jQuery ? jQuery('#' + this.id) : false; }
    listen(name, func) {
        const id = this.id;
        const selector = '#' + id;

        const delegatedHandler = (e) => {
            const target = e.target.closest(selector);
            if (target && target.id === id) {
                func.call(LTS.get(id), e, target);
            }
        };

        if (!this._eventListeners) 
            this._eventListeners = new Map();

        this._eventListeners.set(name, { handler: delegatedHandler });
        document.addEventListener(name, delegatedHandler);

        return this;
    }
    off(name) {
        if (!this._eventListeners || !this._eventListeners.has(name)) 
            return this;
        const { handler } = this._eventListeners.get(name);
        document.removeEventListener(name, handler);
        this._eventListeners.delete(name);
        return this;
    }
    method(name, func) { this[name] = func; return this; }

    signal(name, handler, sender) {
        if (handler) {
            const _name = sender ?
                (typeof sender === 'object' && sender.id ?
                    `${sender.id}_${name}` :
                    `${sender}_${name}`
                ) :
                name;
            LTS.onSignal(_name, this, handler);
        } else {
            LTS.signal(this.id + '_' + name);
        }
        return this;
    }

    add(obj) {
        LTS.addto(this.id, obj);
        return this;
    }

    del() {
        const el = document.getElementById(this.id);
        if (el) el.remove();
        return this;
    }

    css(property, value) {
        const el = document.getElementById(this.id);
        if (!el) return this;

        if (value !== undefined) {
            el.style.setProperty(
                property.replace(/([A-Z])/g, '-$1').toLowerCase(),
                value
            );
            return this;
        } else if (typeof property === 'object') {
            Object.entries(property).forEach(([prop, val]) => {
                el.style.setProperty(
                    prop.replace(/([A-Z])/g, '-$1').toLowerCase(),
                    val
                );
            });
            return this;
        } else {
            // Получение значения
            const computed = window.getComputedStyle(el);
            const propName = property.replace(/([A-Z])/g, '-$1').toLowerCase();
            return computed.getPropertyValue(propName);
        }
    }

    attr(name, value) {
        const el = document.getElementById(this.id);
        if (!el) return this;

        if (value !== undefined) 
            el.setAttribute(name, value);
        else if (typeof name === 'string') 
            return el.getAttribute(name);
        else if (typeof name === 'object') 
            for (const [k, v] of Object.entries(name)) 
                el.setAttribute(k, v);

        return this;
    }

    removeattr(name) {
        const el = document.getElementById(this.id);
        if (el) el.removeAttribute(name);
        return this;
    }

    hasclass(className) {
        const el = document.getElementById(this.id);
        return el ? el.classList.contains(className) : false;
    }

    addclass(className) {
        const el = document.getElementById(this.id);
        if (el) el.classList.add(...className.split(' '));
        return this;
    }

    removeclass(className) {
        const el = document.getElementById(this.id);
        if (el) el.classList.remove(...className.split(' '));
        return this;
    }

    toggleclass(className) {
        const el = document.getElementById(this.id);
        if (el) el.classList.toggle(className);
        return this;
    }

    show() {
        const el = document.getElementById(this.id);
        if (el) el.style.display = '';
        return this;
    }

    hide() {
        const el = document.getElementById(this.id);
        if (el) el.style.display = 'none';
        return this;
    }

    html(htmlString) {
        const el = document.getElementById(this.id);
        if (!el) return this;
        if (htmlString === undefined) 
            return el.innerHTML;
        else
            el.innerHTML = htmlString;
        return this;
    }

    text(textContent) {
        const el = document.getElementById(this.id);
        if (!el) return this;
        if (textContent === undefined) 
            return el.textContent;
        else
            el.textContent = textContent;
        return this;        
    }

    val(value) {
        const el = document.getElementById(this.id);
        if (!el) return this;

        if (value === undefined) 
            return el.value;
        else {
            if (el.tagName === 'SELECT') {
                el.value = value;
                Array.from(el.options).forEach(opt => {
                    opt.selected = opt.value == value;
                });
            } else 
                el.value = value;
            el.dispatchEvent(new Event('input', { bubbles: true }));
            el.dispatchEvent(new Event('change', { bubbles: true }));
        }
        return this;
    }

    focus() {
        const el = document.getElementById(this.id);
        if (el) el.focus();
        return this;
    }

    blur() {
        const el = document.getElementById(this.id);
        if (el) el.blur();
        return this;
    }

    disabled(disabled = true) {
        const el = document.getElementById(this.id);
        if (el) el.disabled = disabled;
        return this;
    }

    enable() {
        return this.disabled(false);
    }
}

const LTS = {
    objects: {},        // Хранилище JS-объектов по ID
    varsstorage: {},    // Глобальные переменные (сохраняются в сессии)
    requests: {},       // Обработчики ответов
    errors: {},         // Обработчики ошибок
    busi: {},           // Блокировка повторных запросов
    signals: {},        // { signalName: [ { obj, handler } ] }
    async: true,        // Асинхронные запросы по умолчанию
    isonerequest: false,// Только один активный запрос
    onebusi: false,     // Флаг активного запроса
    languages: {},      // Локализация
    isfetch: (typeof window.fetch === 'function'), // Поддержка fetch
    preloaderopen: null,    // функция открыть прелоадер 
    preloaderclose: null,   // функция закрыть прелоадер
    preloaderoff: false,    // не использовать прелоадер в текущем запросе

    get: function (id) {
        if (id) {
            if (!(id in this.objects))
                this.objects[id] = new LTSObject(id);
            return this.objects[id];
        } 
        return false;
    },

    addto: function (idContainer, childOrId) {
        const container = document.getElementById(idContainer);
        if (!container) {
            console.error(`LTS.add: Контейнер с id="${idContainer}" не найден`);
            return;
        }

        let childEl;

        if (typeof childOrId === 'string') 
            childEl = document.getElementById(childOrId);
        else if (typeof childOrId === 'object' && childOrId !== null && childOrId.id) 
            childEl = document.getElementById(childOrId.id);

        // Если нашли DOM-элемент — добавляем
        if (childEl) 
            container.appendChild(childEl);
        else
            console.warn(`LTS.add: Не удалось найти элемент для добавления`);
    },

    vars: function (name, values) {
        var varsname = name ? '__globals' + name : '__globals';
        if (values) {
            if (varsname in this.varsstorage)
                this.varsstorage[varsname].values = values;
            else {
                var v = new Vars(name);
                v.values = values;
                this.varsstorage[varsname] = v;
            }
        } else
            return varsname in this.varsstorage ? this.varsstorage[varsname] : null;
    },

    // инициируем сигнал с данными
    signal: function(name, data) {
        if (!this.signals[name]) return;
        this.signals[name].forEach(({ obj, handler }) => {
            try {
                handler.call(obj, data, name);
            } catch (e) {
                console.error(`Ошибка в обработчике сигнала "${name}"`, e);
            }
        });
    },
    // привязываем объект к прослушке сигналов
    onSignal: function(name, obj, handler) {
        if (!this.signals[name]) {
            this.signals[name] = [];
        }
        this.signals[name].push({ obj, handler });
    },

    asyncpost: async function (name, script, args) {
        if (this.isonerequest && this.onebusi) return;
        this.onebusi = true;

        if (! this.preloaderoff && this.preloaderopen) this.preloaderopen();

        try {
            const response = await fetch(script, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(args)
            });

            if (response.ok) {
                const result = await response.text();
                if (name in this.requests && typeof this.requests[name] === 'function') {
                    this.requests[name](result);
                }
            }
        } catch (error) {
            if (name in this.errors && typeof this.errors[name] === 'function') {
                this.errors[name](error);
            }
        } finally {
            this.onebusi = false;
            delete this.busi[name];
            if(this.preloaderoff)
                this.preloaderoff = false;
            else
                if (this.preloaderclose) this.preloaderclose();
        }
    },

    post: function (name, script, args) {
        if (this.isonerequest) {
            if (this.onebusi) return;
            this.onebusi = true;
        }

        if (name in this.busi) return;
        this.busi[name] = true;

        const self = this;

        if (! this.preloaderoff && this.preloaderopen) this.preloaderopen();

        // Определяем тип данных
        let body, contentType;

        if (args instanceof FormData) {
            body = args;
            contentType = null; // let browser set Content-Type with boundary
        } else {
            body = JSON.stringify(args);
            contentType = 'application/json';
        }

        if (this.async) {
            if (this.isfetch) {
                fetch(script, {
                    method: 'POST',
                    headers: contentType ? { 'Content-Type': contentType } : {},
                    body: body
                })
                .then(response => {
                    if (!response.ok) throw new Error(`HTTP ${response.status}`);
                    return response.text();
                })
                .then(text => {
                    try {
                        const result = JSON.parse(text);
                        if (name in self.requests && typeof self.requests[name] === 'function') {
                            self.requests[name](result);
                        }
                    } catch (e) {
                        if (name in self.errors && typeof self.errors[name] === 'function') {
                            self.errors[name](new Error('Parse error: ' + e.message));
                        }
                    }
                })
                .catch(error => {
                    if (name in self.errors && typeof self.errors[name] === 'function') {
                        self.errors[name](error);
                    }
                })
                .finally(() => {
                    self.onebusi = false;
                    delete self.busi[name];
                    if(self.preloaderoff)
                        self.preloaderoff = false;
                    else
                        if (self.preloaderclose) self.preloaderclose();
                });
            } else {
                const xhr = new XMLHttpRequest();
                xhr.open("POST", script, true);
                if (contentType) xhr.setRequestHeader("Content-Type", contentType);

                xhr.onreadystatechange = function () {
                    if (xhr.readyState === XMLHttpRequest.DONE) {
                        if (xhr.status === 200) {
                            try {
                                const result = JSON.parse(xhr.responseText);
                                if (name in self.requests && typeof self.requests[name] === 'function') {
                                    self.requests[name](result);
                                }
                            } catch (e) {
                                if (name in self.errors && typeof self.errors[name] === 'function') {
                                    self.errors[name](new Error('Parse error'));
                                }
                            }
                        }
                        self.onebusi = false;
                        delete self.busi[name];
                        if(self.preloaderoff)
                            self.preloaderoff = false;
                        else
                            if (self.preloaderclose) self.preloaderclose();
                    }
                };
                xhr.send(body);
            }
        } else {
            const xhr = new XMLHttpRequest();
            xhr.open("POST", script, false);
            if (contentType) xhr.setRequestHeader("Content-Type", contentType);

            try {
                xhr.send(body);
                if (xhr.status === 200) {
                    const result = JSON.parse(xhr.responseText);
                    if (name in self.requests && typeof self.requests[name] === 'function') {
                        self.requests[name](result);
                    }
                }
            } catch (error) {
                if (name in self.errors && typeof self.errors[name] === 'function') {
                    self.errors[name](error);
                }
            } finally {
                self.onebusi = false;
                delete self.busi[name];
                if(self.preloaderoff)
                    self.preloaderoff = false;
                else
                    if (self.preloaderclose) self.preloaderclose();
            }
        }
    },

    page: function (url) {
        if(this.preloaderopen !== null)
            this.preloaderopen();
        this.preloaderclose = null;
        var vars = {};
        for (var name in this.varsstorage)
            vars[name] = this.varsstorage[name].values;
        var args = { __ltsVars: vars, __ltspageurl: url };
        LTS.onebusi = false;
        this.post('page', window.location.href, args);
    },

    lang(lng, words) {
        this.languages['lng' + lng] = words;
    },

    say(word, key, lng) {
        const langKey = 'lng' + (lng || '');
        const dict = this.languages[langKey];

        if (!dict) return word;

        const lookupKey = key || word;

        if (lookupKey in dict) {
            return dict[lookupKey];
        }

        return word;
    },

    request: function (name, func) {
        this.requests[name] = func;
    },

    error: function (name, func) {
        this.errors[name] = func;
    },

    inic: function () {
        this.request('page', function (page) 
        { 
            window.location = page.url; 
        });
    }
};

LTS.inic();