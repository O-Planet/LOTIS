const ltsEvents = {
    async: true,
    script: '',

    post: function (name, id, args) {
        // Собираем FormData
        const formData = new FormData();
        formData.append('__ltsEvent', name);
        formData.append('__ltsEventId', id);

        const jsonkeys = [];

        if (args instanceof FormData) {
            args.forEach((value, key) => {
                if (typeof value === 'string' && value.substring(0, 6) == '<JSON>') {
                    formData.append(key, value.substring(6));
                    jsonkeys.push(key);
                } else {
                    formData.append(key, value);
                }
            });
        } else {
            for (const key in args) {
                const value = args[key];
                if ((typeof value === 'object' && !(value instanceof File)) || Array.isArray(value)) {
                    formData.append(key, JSON.stringify(value));
                    jsonkeys.push(key);
                } else {
                    formData.append(key, value);
                }
            }
        }

        // Передаём Vars
        let hasVars = false;
        const vars = {};
        for (const varname in LTS.varsstorage) {
            vars[varname] = LTS.varsstorage[varname].values;
            hasVars = true;
        }
        if (hasVars) {
            formData.append('__ltsVars', JSON.stringify(vars));
        }

        if (jsonkeys.length > 0) {
            formData.append('__ltsJSONKeys', JSON.stringify(jsonkeys));
        }

        // Используем LTS.post() — обработчики уже установлены в compile()
        LTS.post(name, this.script, formData);
    }
};