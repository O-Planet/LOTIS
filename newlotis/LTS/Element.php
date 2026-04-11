<?php
namespace LTS;

class Element extends Quark
{
    public $hucksenable = false;

    public function __construct($id = '') 
    { 
        if($id !== '' && substr($id, 0, 1) == '.')
        {
            parent::__construct();
            $this->addclass(substr($id, 1));
        }
        else
            parent::__construct($id);
        $this->tagname = 'div'; 
        $this->type = 'html';
    }

    public function _new($type, $id = '')
    {
        return \LTS::_new($type, $id, $this);
    }

    public function capt($capt)
    {
        $this->caption = $capt;
        return $this;
    }

    public function tag($tagname)
    {
        $this->tagname = $tagname;
        return $this;
    }

    // CSS attr option

    public function css($name = '', $value = null)
    {
        if($value !== null) {
            if(is_array($value))
                $this->css($name)->add($value);
            else
                $this->css()->add($name, $value);
            return $this;
        }

        if(is_array($name)) {
            $this->css()->add($name);
            return $this;
        }
        
        $_name = $name == '' ? "#{$this->id}" : "#{$this->id}{$name}";

        $childs = $this->get('childs');
        if($childs !== false)
            foreach($childs as $child)
                if($child->type == 'CSS' && $child->id == $_name)
                    return $child;

        $css = new CSS($_name);
        $this->add($css);

        return $css;
    }

    public function gridarea($name)
    {
        $this->css()->add('grid-area', $name);
        return $this;
    }

    public function display($name = 'block')
    {
        $this->css()->add('display', $name);
        return $this;
    }

    public function option($name, $val = null)
	{
		return $val === null ? $this->get('options', $name) : $this->set('options', $name, $val);
	}
	
    public function attr($name, $val = null)
    {
        return $val === null ? $this->get('attr', $name) : $this->set('attr', $name, $val);
    }

    public function removeattr($name)
    {
        $attrs = $this->get('attr');
        if ($attrs !== false && array_key_exists($name, $attrs)) 
            unset($this->storage['attr'][$name]);
        return $this;
    }

    public function left($l)
    {
        $this->css()->add('left', $l);
        return $this;
    }

    public function top($t)
    {
        $this->css()->add('top', $t);
        return $this;
    }

    public function width($w)
    {
        $this->css()->add('width', $w);
        return $this;
    }

    public function height($h)
    {
        $this->css()->add('height', $h);
        return $this;
    }

    public function click($body)
    {
        return $this->on('click', $body);
    }

    public function on($func, $body)
    {
        $p = strpos($func, '(');
        if($p !== false)
            $funcname = substr($func, $p);
        else
            $funcname = $func;
        $this->js('ready')->add($funcname, $body)
            ->add("LTS.get('{$this->id}').{$funcname} = function () { jQuery('#{$this->id}').trigger('{$funcname}'); };");
        return $this;
    }

    public function openhucks($names = null) {
        if($names === null) 
            $this->hucksenable = true; 
        else {
            $_names = \LTS::explodestr($names);
            foreach($_names as $name)
                $this->set('openedhucks', $name);
        }
        return $this; 
    }

    public function method($func, $body)
    {
        // Извлекаем имя функции и параметры через регулярное выражение
        if (!preg_match('/^([a-zA-Z_]\w*)\s*(?:\(([^)]*)\))?$/', $func, $matches)) 
            throw new InvalidArgumentException("Invalid function signature: {$func}");

        $funcname = $matches[1];
        $params   = isset($matches[2]) ? $matches[2] : '';
        $paramList = array_map('trim', array_filter(explode(',', $params)));

        // Формируем безопасную строку присвоения значений из ltsRetValues
        $varsstr = '';
        foreach ($paramList as $var) 
            if ($var !== '') 
                $varsstr .= "\nif ('{$var}' in ltsRetValues) {$var} = ltsRetValues.{$var};";

        // Формируем параметры для подписи функции
        $paramSignature = $params === '' ? '()' : "({$params})";

        $this->js()->add(
<<<JS
            LTS.get('{$this->id}').{$funcname} = function {$paramSignature} { 
                // Проверка допуска
                if (this.check{$funcname} && !this.check{$funcname}{$paramSignature}) 
                    return;
                
                // Вызов before-хука и распаковка значений
                if (this.before{$funcname}) {
                    const ltsRetValues = this.before{$funcname}{$paramSignature};{$varsstr}
                }
                
                // Основное тело
                {$body}; 
                
                // После выполнения
                if (this.on{$funcname}) 
                    this.on{$funcname}{$paramSignature};
            };
JS
    );

        return $this;
    }

    // С учетом hucksenable
    protected function compilemethod($func, $body = null, $js = null) {
        $openedhucks = $this->get('openedhucks');
        if($openedhucks === false)
            $funcname = '';
        else {
            $p = strpos($func, '(');
            $funcname = $p === false ? trim($func) : trim(substr($func, 0, $p));
        }
        if($this->hucksenable || ($openedhucks !== false && in_array($funcname, $openedhucks))) {
            if($js === null)
                $js = $this->js();
            if($body === null)
                $js->add($func);
            else
                $js->method($func, $body);
        }
        else {
            if($js === null) {
                $js = new JS();
                $js->compile = false;
                $this->add($js);
            }
            if($body === null)
                $js->add($func);
            else {
                // Извлекаем имя функции и параметры через регулярное выражение
                if (!preg_match('/^([a-zA-Z_]\w*)\s*(?:\(([^)]*)\))?$/', $func, $matches)) 
                    throw new InvalidArgumentException("Invalid function signature: {$func}");

                $funcname = $matches[1];
                $params   = isset($matches[2]) ? $matches[2] : '';
                $paramSignature = $params === '' ? '()' : "({$params})";

                $js->add(
<<<JS
    LTS.get('{$this->id}').{$funcname} = function {$paramSignature} {
        {$body};
    };
JS
                );
            }
        }

        return $js;
    }

    // Signal подписка
    public function signal($name, $handler, $sender = null)
    {
        if(strpos(trim($handler), 'function') === 0)
            $_handler = $handler;
        else
            $_handler = "function (param) { {$handler}; }";

        $signalname = $sender === null ? $name : "{$sender->id}_{$name}";
        
        $this->js()->add("LTS.get('{$this->id}').signal('{$signalname}', {$_handler})");
        return $this;
    }

    // Class

    public function addclass($classname, $toend = true)
    {
        $classes = $this->getClassesArray();
        $newClasses = explode(' ', trim($classname));

        foreach ($newClasses as $cls) 
        {
            $cls = trim($cls);
            if ($cls !== '' && !in_array($cls, $classes)) 
                if ($toend) 
                    $classes[] = $cls;
                else 
                    array_unshift($classes, $cls);
        }

        $this->classname = implode(' ', $classes);
        return $this;
    }

    public function hasclass($classname)
    {
        if (empty($this->classname)) return false;
        
        $classes = explode(' ', $this->classname);
        return in_array($classname, $classes);
    }

    public function removeclass($classname)
    {
        $classes = $this->getClassesArray();
        $remove = explode(' ', trim($classname));

        foreach ($remove as $cls) 
        {
            $cls = trim($cls);
            if ($cls !== '') 
            {
                $key = array_search($cls, $classes);
                if ($key !== false) 
                    unset($classes[$key]);
            }
        }

        $this->classname = implode(' ', $classes);
        return $this;
    }

    // Вспомогательный метод
    private function getClassesArray()
    {
        return $this->classname === '' 
            ? [] 
            : array_filter(array_map('trim', explode(' ', $this->classname)));
    }
    
    // SHINE

    public function shine($parent = null)
    {
        $this->set('element', 'type', $this->type);
        $attrMap = $this->get('attr');
        if ($attrMap !== false && count($attrMap) > 0) {
            $attrs = [];
            foreach ($attrMap as $name => $val) {
                if ($val === false) continue;
                if ($val === true) {
                    $attrs[$name] = ''; 
                } else {
                    $attrs[$name] = $val;
                }
            }
            $this->set('element', 'attr', $attrs);
        }

        if($this->tagname != '')
            $this->set('element', 'tagname', $this->tagname);
        if($this->id != '')
            $this->set('element', 'id', $this->id);
        if($parent !== null)
            $this->set('element', 'parent', $parent);
        if($this->classname != '')
            $this->set('element', 'class', $this->classname);
        if($this->caption != '')
            $this->set('element', 'caption', $this->caption);
    }
}
?>
