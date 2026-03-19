<?php
namespace LTS;

class JS extends Quark
{
    public $area;
    public $compile;

    public function __construct($id = '') 
    {
        if(in_array($id, ['script', 'ready', 'create']))
        {
            $_id = '';
            $this->area = $id;
        }
        else
        {
            $_id = $id;
            $this->area = 'script';
        }

        parent::__construct($_id);

        $this->type = 'JS';
        $this->compile = true;
    }
    // Предварительная обработка
    public function compile() {}

    public function shine($parent = null)
    {
        $childs = $this->get('childs');

        if($childs !== false)
        {
            foreach($childs as $name => $val)
                if(is_object($val) || is_array($val))
                    if(is_numeric($name))
                    {
                        $_val = (object) $val;
                        if(property_exists($_val, 'id'))
                            $this->set('localvars', $_val->id, (array) $val);                            
                    }
                    else
                        $this->set('localvars', $name, (array) $val);

            foreach($childs as $name => $val)
                if(is_string($val))
                {
                    if(strpos($val, '.js') !== false)
                    {
                        if(substr($val, 0, 4) == 'LTS:')
                            $content = file_get_contents(substr($val, 4));
                        else
                        {
                            $this->space->storage['elements'][] = array('type' => 'script', 'src' => $val);
                            continue;
                        }
                    }
                    else
                        $content = $val;

                    //$onevents = strpos($content, 'LTS(events)') > 0;
                    $body = $this->compile ? JS::javascript($content, $this->get('localvars')) : $content;
                    if(strpos($name, '(') !== false || is_numeric($name))
                        $rec = array('type' => 'JS', 'area' => $this->area, 'id' => $name, 'body' => $body, 'on' => false); //, 'eventsready' => $onevents);
                    else
                        $rec = array('type' => 'JS', 
                                'area' => 'ready', 
                                'id' => $name, 
                                'body' => $body, 
                                'on' => true,
                                //'eventsready' => $onevents, 
                                'class' => $this->area, 
                                'parent' => $parent);
                            
                    $this->space->storage['elements'][] = $rec;                    
                }
        }
    }

    public function childs() {}

    static function javascript($body, $local = false)
    {
        $body = str_replace('->', '.', $body);

        // Обработка LTS(var)
        $body = preg_replace_callback(
            '/\bLTS\s*\(\s*([a-zA-Z_][a-zA-Z0-9_]*)\s*\)/',
            function ($matches) use ($local) {
                $varname = $matches[1];
                return JS::resolveVar($varname, $local, 'LTS.get(\'', '\')');
            },
            $body
        );

        // Обработка $(var)
        $body = preg_replace_callback(
            '/\$\(\s*([a-zA-Z_][a-zA-Z0-9_]*)\s*\)/',
            function ($matches) use ($local) {
                $varname = $matches[1];
                return JS::resolveVar($varname, $local, 'jQuery(\'#', '\')');
            },
            $body
        );

        return $body;
    }

    private static function resolveVar($name, $local, $left, $right)
    {
        // 1. Проверяем локальные переменные
        if ($local !== false && array_key_exists($name, $local)) {
            return JS::getVarReplacement($local[$name], $left, $right);
        }

        // 2. Проверяем глобальные переменные
        if (array_key_exists($name, $GLOBALS)) {
            return JS::getVarReplacement($GLOBALS[$name], $left, $right);
        }

        // 3. Неизвестная переменная — оставляем как есть
        return $left . $name . $right;
    }

    private static function getVarReplacement($value, $left, $right)
    {
        if (is_object($value) && isset($value->id)) {
            return "{$left}{$value->id}{$right}";
        }

        if (is_array($value) || is_object($value)) {
            $json = json_encode((object)$value, JSON_UNESCAPED_UNICODE);
            return $json ?: 'null';
        }

        if (is_string($value)) {
            return json_encode($value);
        }

        return $value;
    }

    static function paramobject($arr)
    {
        $result = [];
        
        foreach ($arr as $key => $value) {
            // Ключ
            if (is_int($key) || preg_match('/^[a-zA-Z_$][a-zA-Z0-9_$]*$/', $key)) {
                $jsKey = $key;
            } else {
                // Ключ в кавычках, если содержит спецсимволы
                $jsKey = json_encode((string)$key, JSON_UNESCAPED_UNICODE);
            }

            // Значение
            $jsValue = self::encodeJSValue($value);

            $result[] = "    {$jsKey}: {$jsValue}";
        }

        return "{\n" . implode(",\n", $result) . "\n}";
    }

    private static function encodeJSValue($value)
    {
        if (is_object($value) && method_exists($value, 'id')) {
            return "LTS.get('{$value->id}')";
        }

        if (is_array($value)) {
            return self::paramobject($value);
        }

        if (is_string($value)) {
            // Проверяем, является ли строка функцией
            if (preg_match('/^\s*function\s*\(/', $value)) {
                return trim($value); // Возвращаем как есть
            }
            return json_encode($value, JSON_UNESCAPED_UNICODE);
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if ($value === null) {
            return 'null';
        }

        return (string)$value;
    }
}

?>
