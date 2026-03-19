class Vars {
    id = '';
    values = {};
    constructor (id) {
        this.id = id;
    }

    get(name) {
        return this.values[name] === undefined ? null : this.values[name];
    }

    set(name, value) {
        if(value === undefined || value === null)
        {
            if(name in this.values)
                delete this.values[name];
        }
        else
            this.values[name] = value;

        return this;
    }

    varsname() {
        return this.id ? '__globals' + this.id : '__globals';
    }

    store() {
        var _varsname = this.varsname();
        var vars = {};
        vars[_varsname] = this.values;
        var args = { __ltsVars : vars, __ltspageurl : 'ok' };
        LTS.post('__ltssavevars', window.location.href, args);
    }

    localsave(name) {
        if(name && ! name in this.values)
            return;
        var _varsname = this.varsname();
        var myVariablesObj;
        if(name)
        {
            var myVariables = localStorage.getItem(_varsname);
            if(myVariables)
            {
                myVariablesObj = JSON.parse(myVariables);
                myVariablesObj[name] = this.values[name];
            }
            else
                myVariablesObj = { [name]: this.values[name] };
        }
        else
            myVariablesObj = this.values;

        localStorage.setItem(_varsname, JSON.stringify(myVariablesObj));
    }

    localload(name) {
        var _varsname = this.varsname();
        var myVariables = localStorage.getItem(_varsname);
        if(myVariables)
        {
            var myVariablesObj = JSON.parse(myVariables);
            if(name)
            {
                if(name in myVariablesObj)
                {
                    this.values[name] = myVariablesObj[name];
                    return this.values[name];
                }
                else
                    return null;
            }
            else
            {
                this.values = myVariablesObj;
                return true;
            }
        }

        return false;
    }
}