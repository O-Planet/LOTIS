/**
 * LotisBuilder - клиентский построитель интерфейса с поддержкой событий
 */
const LotisBuilder = {
    metadata: null,
    doc: null,
    
    /**
     * Основной метод построения интерфейса
     */
    build(metadata) {
        this.metadata = metadata;
        this.doc = {
            link: [],
            css: [],
            scripts: [],
            ready: [],
            eventsready: [],
            body: {},
            childs: []
        };

        // Обработка метаданных
        this.processMetadata();
        
        // Построение head
        this.buildHead();
        
        // Построение body
        this.buildBody();
        
        // Инициализация событий
        this.initEvents();
    },

    /**
     * Обработка всех типов метаданных
     */
    processMetadata() {
        Object.values(this.metadata).forEach(el => {
            switch (el.type) {
                case 'CSS':
                    this.processCSS(el);
                    break;
                case 'link':
                    this.processLink(el);
                    break;
                case 'script':
                    this.processScript(el);
                    break;
                case 'file':
                    this.processFile(el);
                    break;
                case 'VARS':
                    this.processVARS(el);
                    break;
                case 'JS':
                    this.processJS(el);
                    break;
                case 'html':
                    this.processHTML(el);
                    break;
            }
        });
    },

    /**
     * Обработка CSS-правил
     */
    processCSS(el) {
        let cssText = '';
        for (const name in el.style) {
            if (typeof name === 'number') {
                cssText += el.style[name];
            } else {
                cssText += `\n${name}: ${el.style[name]};`;
            }
        }
        this.doc.css.push(`${el.class} {${cssText}\n}`);
    },

    /**
     * Обработка link-элементов
     */
    processLink(el) {
        this.doc.link.push(`<link href="${el.href}" rel="${el.rel}">`);
    },

    /**
     * Обработка script-элементов
     */
    processScript(el) {
        const attrs = [];
        if (el.async) attrs.push('async');
        if (el.defer) attrs.push('defer');
        const attrStr = attrs.length ? ' ' + attrs.join(' ') : '';
        this.doc.link.push(`<script src="${el.src}"${attrStr}></script>`);
    },

    /**
     * Обработка файлов
     */
    processFile(el) {
        if (!el.filename) return;
        
        try {
            const content = this.loadFile(el.filename);
            const oper = { body: content };
            const id = el.id || '';
            const parent = el.parent || '';
            
            if (id) {
                if (parent && this.doc.body[parent]) {
                    this.doc.body[parent].childs.push(id);
                }
                this.doc.body[id] = oper;
                if (!parent) {
                    this.doc.childs.push(id);
                }
            } else {
                this.doc.body['file_' + Date.now()] = oper;
            }
        } catch (e) {
            console.error('Ошибка загрузки файла:', el.filename, e);
        }
    },

    /**
     * Синхронная загрузка файла (для простоты)
     */
    loadFile(filename) {
        const xhr = new XMLHttpRequest();
        xhr.open('GET', filename, false);
        xhr.send();
        if (xhr.status === 200) {
            return xhr.responseText;
        }
        throw new Error(`Файл не найден: ${filename}`);
    },

    /**
     * Обработка переменных
     */
    processVARS(el) {
        this.doc.scripts.push(`LTS.vars("${el.id}", ${el.body});`);
    },

    /**
     * Обработка JavaScript-кода
     */
    processJS(el) {
        let str = '';

        if (el.area === 'script') {
            str = isNaN(el.id)
                ? `function ${el.id}(){\n    ${el.body};\n}`
                : `${el.body};`;
            this.doc.scripts.push(str);
        }
        else if (el.area === 'ready') {
            if (el.on) {
                const parent = el.parent || '';
                const cls = el.class ? (el.class.startsWith('.') ? el.class : '.' + el.class) : '';
                const selector = `#${parent}${cls}`;
                const name = el.id.includes('(') ? el.id.substr(el.id.indexOf('(')) : el.id;
                str = `jQuery('${selector}').on('${name}', function (event) {${el.body}; });`;
            }
            else {
                str = isNaN(el.id)
                    ? `function ${el.id}(){\n${el.body};\n}`
                    : `${el.body};`;
            }
            this.doc.ready.push(str);
        }
    },

    /**
     * Обработка HTML-элементов
     */
    processHTML(el) {
        const tagname = el.tagname || 'div';
        const id = el.id || '';
        const parent = el.parent || '';
        const attr = el.attr ? ' ' + el.attr : '';
        const className = el.class ? ` class="${el.class}"` : '';
        const caption = el.caption || '';

        const oper = {
            tag: `<${tagname} id="${id}"${className}${attr}>`,
            tagname: tagname,
            parent: parent,
            childs: [],
            caption: caption
        };

        if (id) {
            this.doc.body[id] = oper;
            if (parent && this.doc.body[parent]) {
                this.doc.body[parent].childs.push(id);
            }
            if (!parent) {
                this.doc.childs.push(id);
            }
        } else {
            this.doc.body['anon_' + Math.random()] = oper;
        }
    },

    /**
     * Построение head секции
     */
    buildHead() {
        const head = document.head;

        // Подключение внешних ресурсов
        this.doc.link.forEach(linkHtml => {
            const link = document.createElement('link');
            const hrefMatch = linkHtml.match(/href="([^"]+)"/);
            const relMatch = linkHtml.match(/rel="([^"]+)"/);
            
            if (hrefMatch && relMatch) {
                link.href = hrefMatch[1];
                link.rel = relMatch[1];
                head.appendChild(link);
            }
        });

        // CSS стили
        if (this.doc.css.length) {
            const style = document.createElement('style');
            style.textContent = this.doc.css.join('\n');
            head.appendChild(style);
        }

        // Глобальные скрипты
        if (this.doc.scripts.length) {
            const script = document.createElement('script');
            script.textContent = this.doc.scripts.join('\n');
            head.appendChild(script);
        }

        // Скрипты для ready-события
        if (this.doc.ready.length || this.doc.eventsready.length) {
            const readyScript = document.createElement('script');
            readyScript.textContent = `
                jQuery(document).ready(function () {
                    ${this.doc.ready.join('\n')}
                    ${this.doc.eventsready.join('\n')}
                });
            `;
            head.appendChild(readyScript);
        }
    },

    /**
     * Построение body секции
     */
    buildBody() {
        const body = document.body;
        body.innerHTML = '';

        // Построение корневых элементов
        this.doc.childs.forEach(id => {
            this.buildChild(id, body);
        });
    },

    /**
     * Рекурсивное построение дочерних элементов
     */
    buildChild(id, parent) {
        const el = this.doc.body[id];
        if (!el) return;

        const dom = document.createElement(el.tagname);
        dom.id = id;
        
        if (el.class) dom.className = el.class;
        if (el.caption) dom.textContent = el.caption;

        parent.appendChild(dom);

        if (el.childs && el.childs.length) {
            el.childs.forEach(childId => {
                this.buildChild(childId, dom);
            });
        }
    },

    /**
     * Инициализация обработчиков событий
     */
    initEvents() {
        // Установка обработчиков для событий из метаданных
        Object.values(this.metadata).forEach(el => {
            if (el.type === 'JS' && el.area === 'ready' && el.on) {
                try {
                    // Выполняем код обработчика
                    eval(el.body);
                } catch (e) {
                    console.error('Ошибка при инициализации события:', e);
                }
            }
        });
    },

    /**
     * Вспомогательные методы
     */
    loadScript(src, callback) {
        const script = document.createElement('script');
        script.src = src;
        if (callback) script.onload = callback;
        document.head.appendChild(script);
    },

    loadLink(href) {
        const link = document.createElement('link');
        link.href = href;
        link.rel = 'stylesheet';
        document.head.appendChild(link);
    }
};

// Автозапуск при наличии метаданных
if (window.__LOTIS_METADATA__) {
    document.addEventListener('DOMContentLoaded', () => {
        if (typeof LotisBuilder !== 'undefined') {
            LotisBuilder.build(window.__LOTIS_METADATA__);
        }
    });
}
