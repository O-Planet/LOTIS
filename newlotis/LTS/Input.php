<?php
namespace LTS;

class Input extends Element
{
    public $form;

    function __construct($id = '')
    {
        parent::__construct($id);
        $this->tagname = 'input';
        $this->attr('type', 'text'); 
    }

    public function setinput($type = null, $name = '', $caption = '')
    {
        if($type !== null)
            $this->attr('type', $type);
        if($name !== '')
            $this->attr('name', $name);
        if($caption !== '')
        {
            $label = new Html('label');
            $label->attr('for', $this->id);
            $label->caption = $caption;
            $this->add($label);
        }
        return $this;
    }

    public function value($val)
    {
        $type = $this->tagname;
        
        if ($type === 'textarea') {
            $this->caption = $val;
        }
        elseif ($type === 'select') {
            $this->attr('value', $val);
            // Выделить нужный option
            $childs = $this->get('childs');
            foreach ($childs as $child) {
                if ($child->tagname === 'option') {
                    if ($child->attr('value') == $val) {
                        $child->attr('selected', 'selected');
                    } else 
                        $child->removeattr('selected');
                }
            }
        }
        else 
            $this->attr('value', $val);
        
        return $this;
    }

    public function select($name, $caption = '', $values = null)
    {
        $this->tagname = 'select';
        $this->attr('name', $name);
        $this->attr('value', '');
        if($caption !== '')
        {
            $label = new Html('label');
            $label->attr('for', $this->id);
            $label->caption = $caption;
            $this->add($label);
        }
        if($values !== null)
        {
            $arr = \LTS::explodestr($values);
            foreach($arr as $v => $capt)
            {
                $o = new Html('option');
                $o->caption = $capt;
                $o->attr('value', $v);
                $this->add($o);
            }
        }
        $this->js('ready')->add("jQuery('#{$this->id}').val(jQuery('#{$this->id}').attr('value'));");
        return $this;
    }

    public function textarea($name, $caption = '')
    {
        $this->tagname = 'textarea';
        $this->attr('name', $name)
            ->attr('rows', 3);
        if($caption !== '')
        {
            $label = new Html('label');
            $label->attr('for', $this->id);
            $label->caption = $caption;
            $this->add($label);
        }

        return $this;
    }

    public function button($name, $caption = '')
    {
        $this->tagname = 'button';
        $this->setinput('button', $name);
        $this->caption = $caption;
        return $this;
    }

    public function checkbox($name, $caption = '')
    {
        return $this->setinput('checkbox', $name, $caption);
    }
    
    public function file($name, $caption = '')
    {
        return $this->setinput('file', $name, $caption);
    }
    
    public function hidden($name, $caption = '')
    {
        return $this->setinput('hidden', $name, $caption);
    }
    
    public function image($name, $caption = '')
    {
        return $this->setinput('image', $name, $caption);
    }
    
    public function password($name, $caption = '')
    {
        return $this->setinput('password', $name, $caption);
    }

    public function radio($name, $caption = '')
    {
        return $this->setinput('radio', $name, $caption);
    }
    
    public function reset($name, $caption = '')
    {
        $this->setinput('reset', $name);
        $this->value($caption);
        return $this;
    }
    
    public function submit($name, $caption = '')
    {
        $this->setinput('submit', $name);
        $this->value($caption);
        return $this;
    }

    public function text($name, $caption = '')
    {
        return $this->setinput('text', $name, $caption);
    }

    // html 5
    public function email($name, $caption = '')
    {
        return $this->setinput('email', $name, $caption);
    }

    public function tel($name, $caption = '')
    {
        return $this->setinput('tel', $name, $caption);
    }

    public function url($name, $caption = '')
    {
        return $this->setinput('url', $name, $caption);
    }

    public function date($name, $caption = '')
    {
        return $this->setinput('date', $name, $caption);
    }

    public function color($name, $caption = '')
    {
        return $this->setinput('color', $name, $caption);
    }

    public function time($name, $caption = '')
    {
        return $this->setinput('time', $name, $caption);
    }

    public function number($name, $caption = '')
    {
        return $this->setinput('number', $name, $caption);
    }

    public function range($name, $caption = '', $min = 1, $max = 100)
    {
        $this->setinput('range', $name, $caption);
        $this->attr('min', $min);
        $this->attr('max', $max);
        return $this;
    }

    public function label($caption = null)
    {
        $childs = $this->get('childs');
        foreach($childs as $child)
            if($child->type == 'html' && $child->tagname == 'label')
            {
                if($caption !== null)
                    $child->caption = $caption ? $caption : '';

                return $child;
            }

        if($caption !== null)
        {
            $label = new Html('label');
            $label->attr('for', $this->id);
            $label->caption = $caption;
            $this->add($label);
            return $label;
        }

        return false;        
    }

    public function required($r = true)
    {
        if ($r) $this->attr('required', 'required');
        else $this->removeattr('required');
        return $this;
    }

    public function min($min)
    {
        $this->attr('min', $min);
        return $this;
    }

    public function max($max)
    {
        $this->attr('max', $max);
        return $this;
    }

    public function pattern($pattern)
    {
        $this->attr('pattern', $pattern);
        return $this;
    }

    // Compile

    public function compile()
    {
        $tp = $this->attr('type'); 
        if($tp == 'submit' || $tp == 'reset')
                $this->compilemethod('click', "jQuery('#{$this->id}').trigger('click')");
        else
            $this->compilemethod('value(val)',
<<<JS
    const formId = jQuery('#{$this->id}').closest('form').attr('id');
    const name = jQuery('#{$this->id}').attr('name');
    return formId && name ? LTS.get(formId).value(name, val) : '';
JS
            );

        parent::compile();
    }

    // SHINE

    public function shine($parent = null)
    {
        $childs = $this->get('childs');
        foreach($childs as $child)
            if($child->type == 'html' && $child->tagname == 'label')
            {
                $child->create($this->space, $child->owner !== null ? $child->owner : $parent);
                $child->type = 'none';
            }

        parent::shine($parent);
    }
}

?>
