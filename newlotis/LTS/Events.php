<?php
namespace LTS;

class Events extends Quark
{
    public $async;
    public $eventresult;
    public $checktrue;
    public $checkerror = '';

    static $all = array();

    public function __construct($id = '')
    {
        parent::__construct($id);
        $this->type = 'Events';
        $this->async = true;
        Events::$all[$this->id] = $this;
    }

    public function client($name, $body)
    {
        $this->js()->add($name, $body);
        return $this;
    }

    public function server($name, $body)
    {
        $p = strpos($name, '(');
        if($p !== false) {
            $_name = trim(substr($name, 0, $p));
            return parent::add($_name, $body);
        }

        $this->add($name, $body);
        return $this;
    }

    public function add($name, $value = null) {
        if($value !== null && is_callable($value)) {
            $p = strpos($name, '(');
            if($p !== false) {
                $_name = trim(substr($name, 0, $p));
                return parent::add($_name, $value);
            }
        }
        
        return parent::add($name, $value);
    }

    public function error($name, $body)
    {
        $p1 = strpos($name, '(');
        if ($p1 !== false) 
            $_name = trim(substr($name, 0, $p1));
        else
            $_name = $name;

        if(strpos(trim($body), 'function') !== 0)
            $_body = "function (error) { {$body}; }";
        else
            $_body = $body;

        $this->set('errors', "{$this->id}_{$_name}", $_body);

        return $this;
    }

    public function compile()
    {
        $prefixes = ['check', 'before', 'on'];
        
        $currentFile = $_SERVER['SCRIPT_FILENAME'];
        $scriptname = basename($currentFile); 
        
        $childs = $this->js()->get('childs');

        $js = new JS(); 
        $js->area = 'ready';

        $errors = $this->get('errors');
        
        if (!$this->async)
            $js->add('ltsEvents.async = false;');
                
        if ($childs !== false) {
            foreach ($childs as $name => $child) {
                
                if (is_string($child)) {

                    $prefixFound = false;

                    foreach ($prefixes as $prefix) 
                        if (strpos($name, $prefix) === 0) {
                            $prefixFound = $prefix;
                            break;
                        }
                        
                    if($prefixFound !== false) {
                        $pos = strpos($name, '(');
                        $_name = $pos !== false ? trim(substr($name, 0, $pos)) : trim($name);
                        $_vars = $pos !== false ? trim(substr($name, $pos)) : '()';
                        $child = trim($child);
                        if(strpos($child, 'function') === 0)
                            $_child = $child;
                        else
                            $_child = "function {$_vars} { {$child}; }";
                        $js->add("LTS.get('{$this->id}').{$_name} = {$_child};");
                    }
                    else {
                        $p1 = strpos($name, '(');
                        $p2 = strpos($name, ')');
                        $_vars = '';
                        $strfunc = 'var args = {};';
                        
                        if ($p1 !== false && $p2 !== false) {
                            $_vars = trim(substr($name, $p1 + 1, $p2 - $p1 - 1));
                            $_name = trim(substr($name, 0, $p1));

                            $args = array_map('trim', array_filter(explode(',', $_vars)));
                            
                            if (count($args) == 1) {
                                $a = $args[0];
                                $strfunc = "var args = ({$a} instanceof FormData ? {$a} : {'{$a}' : {$a}});";
                            } else 
                                foreach ($args as $a) 
                                    $strfunc .= "\nargs['{$a}'] = {$a};";
                        } else 
                            $_name = trim($name);
                        
                        $child = trim($child) . ';' . 
<<<JS
    if(LTS.get('{$this->id}').on{$_name})
        LTS.get('{$this->id}').on{$_name}(result);
JS
                        ;
                        if(strpos($child, 'function') === 0)
                            $_child = $child;
                        else
                            $_child = "function (result) { {$child} }";

                        // Генерируем JS-код
                        $js->add(
<<<JS
ltsEvents.script = '{$scriptname}';
LTS.get('{$this->id}').{$_name} = function({$_vars}) { ltsEvents.{$this->id}_{$_name}({$_vars}); };

ltsEvents.{$this->id}_{$_name} = function ({$_vars}) {
    {$strfunc}
    if(LTS.get('{$this->id}').check{$_name} && LTS.get('{$this->id}').check{$_name}({$_vars}) !== true)
        return;
    if(LTS.get('{$this->id}').before{$_name}) {
        const ltsRetValues = LTS.get('{$this->id}').before{$_name}({$_vars});
        if(ltsRetValues instanceof FormData)
            args = ltsRetValues;
        else if(typeof ltsRetValues == 'object') 
            Object.entries(ltsRetValues).forEach(([key, value]) => {
                if(args instanceof FormData)
                    if(typeof value == 'object' || typeof value == 'array')
                        args.set(key, JSON.stringify(value));
                    else
                        args.set(key, value);
                else
                    args[key] = value;
            });
    }
    this.post('{$this->id}_{$_name}', '{$this->id}', args);
}
ltsEvents.__event{$this->id}_{$_name} = {$_child};

LTS.request('{$this->id}_{$_name}', function(resultData) {
    if (typeof resultData !== 'object' || resultData === null) {
        console.error('Incorrect response for event {$_name}');
        return;
    }

    if ('__ltsVars' in resultData) {
        for (const varsname in resultData['__ltsVars']) {
            LTS.vars(varsname, resultData['__ltsVars'][varsname]);
        }
    }

    const functionName = '__event{$this->id}_{$_name}';
    if (functionName in ltsEvents && typeof ltsEvents[functionName] === 'function') {
        ltsEvents[functionName](resultData['{$this->id}_{$_name}']);
    }
});
JS
. ($errors !== false && array_key_exists($_name, $errors) ? "LTS.error('{$this->id}_{$_name}', {$errors[$_name]});" :
<<<JS
LTS.error('{$this->id}_{$_name}', function(error) {
    console.log('Error in event {$this->id}_{$_name}:', error);
});
JS
));
                    }
                }
            }
        }

        $this->js()->type = 'none';
        $this->add($js);
    }

    public function eventjoin($eventname, $event, $args) {
        if($event === false || ! is_callable($event))
            return false;

        $checkevent = $this->get('childs', "check{$eventname}");
        $beforeevent = $this->get('childs', "before{$eventname}");
        $onevent = $this->get('childs', "on{$eventname}");

        $this->checktrue = true;
        if($checkevent !== false && is_callable($checkevent)) {
            $checkres = call_user_func($checkevent, $args);
            if(! is_bool($checkres))
                $this->checkerror = $checkres;
            if($checkres !== true) {
                $this->checktrue = false;
                return false;
            }
        }

        if($beforeevent !== false && is_callable($beforeevent))
            $args = call_user_func($beforeevent, $args);

        $result = call_user_func($event, $args); 

        if($onevent !== false && is_callable($onevent))
            $result = call_user_func($onevent, $args, $result);

        return $result;
    }

    public function join($id_eventname)
    {
        $l = strlen($this->id);
        $eventname = substr($id_eventname, $l + 1); // отрезаем id_
        $event = $this->get('childs', $eventname);

        if($event === false || ! is_callable($event))
            return false;

        if(array_key_exists('__ltsJSONKeys', $_POST))
            $jsonkeys = json_decode($_POST['__ltsJSONKeys']);
        else
            $jsonkeys = array();

        $args = array();
        foreach($_POST as $name => $val)
            if($name !== '__ltsEvent' && $name !== '__ltsVars' && $name !== '__ltsJSONKeys')
            {
                if(in_array($name, $jsonkeys))
                    $args[$name] = json_decode($val, true);
                else
                    $args[$name] = $val;
            }

        foreach($_FILES as $name => $file)
            $args[$name] = $file;

        $result = $this->eventjoin($eventname, $event, $args);
        if(! $this->checktrue)
            return false;
        $this->eventresult = $result;

        return true;
    }

    static function build()
    {
        if(! array_key_exists('__ltsEvent', $_POST))
            return;
        
        $eventname = $_POST['__ltsEvent'];
        $eventid = $_POST['__ltsEventId'];

        $result = [$eventname => false];

        if(array_key_exists($eventid, Events::$all))
        {
            $event = Events::$all[$eventid];
            if($event->join($eventname))
                $result[$eventname] = $event->eventresult; 
            elseif(! empty($event->checkerror))
                $result[$eventname] = $event->checkerror;
        }

        $varsarr = array();
        foreach(Vars::$all as $varsob)
        {
            $varsarr[$varsob->id] = $varsob->storage;
            $varsob->store();
        }

        $result['__ltsVars'] = $varsarr;

        echo json_encode($result);             
        exit();
    }
}
?>