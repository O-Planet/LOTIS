<?php
namespace LTS;

class CSS extends Quark
{
    public function __construct($id = '')
    {
        parent::__construct($id);
        $this->type = 'CSS';
    }

    public function add($name, $value = null) {
        if($value == null && is_array($name)) {
            foreach($name as $key => $val)
                parent::add($key, $val);
            return $this;
        }
        else
            return parent::add($name, $value);
    }

    public function shine($parent = null)
    {
        $childs = $this->get('childs');

        $css = array();

        if($childs !== false)
            foreach($childs as $name => $val)
                if(strpos($val, '.css') !== false)
                    $this->space->storage['elements'][] = array('type' => 'link', 'href' => $val, 'rel' => 'stylesheet');
                else
                    $css[$name] = $val;

        if(count($css) > 0)
        {
            $rec = array('type' => 'CSS');
            if($this->id != '')
                $rec['class'] = $this->id;
            $rec['style'] = $css;
            $this->space->storage['elements'][] = $rec;
        }
    }

    public function compile() {}
    public function childs() {}
}
?>
