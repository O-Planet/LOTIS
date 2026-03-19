<?php
namespace LTS;

class Vars extends Construct
{
    private $elements;    
    public $storage;
    static $all = array();

    public function __construct($id = '')
    {
        parent::__construct($id);
        $this->id = $id;
        $this->type = 'Vars';
        $this->storage = array();
        $this->restore();
        $varsname = "__globals{$id}";
        Vars::$all[$varsname] = $this;
    }

    public function restore()
    {
        $varsname = "__globals{$this->id}";

		if(array_key_exists($varsname, $_SESSION)) 
			$jsonvars = $_SESSION[$varsname];
        else
            return $this;

        if(! empty($jsonvars))
        {
            if(is_array($jsonvars) || is_object($jsonvars))
                $this->storage = (array) $jsonvars; 
        }

        return $this;
    }

    public function store()
    {
        $varsname = "__globals{$this->id}";
        $_SESSION[$varsname] = $this->storage;

        return $this;
    }

    public function clearsession()
    {
        $varsname = "__globals{$this->id}";
        if(array_key_exists($varsname, $_SESSION))
            unset($_SESSION[$varsname]); 

        return true;
    }

    public function clearvalues($session = true)
    {
        foreach($this->storage as $name => $val)
            $this->storage[$name] = '';

        if($session)
            $this->clearsession();

        return true;
    }

    public function clear($session = true)
    {
        $this->storage = array();

        if($session)
            $this->clearsession();

        return true;
    }

	public function get($name)
	{
        return (array_key_exists($name, $this->storage) ? $this->storage[$name] : null);
	}
		
    public function set($name, $value = null) 
    {
        if($value === null)
        {
            if(array_key_exists($name, $this->storage))
                unset($this->storage[$name]);
        }
        else
            $this->storage[$name] = $value;

		return $this;
	}

    public function value($name, $value = null)
    {
        if($value === null)
            return $this->get($name);

        $this->set($name, $value);

        return $this;
    }

    public function del($name)
    {
        if(array_key_exists($name, $this->storage))
            unset($this->storage[$name]);

		return $this;
    }

    // ----------
    public function shine($parent = null) 
    {
        $body = json_encode($this->storage);
        $this->elements = array('type' => 'VARS', 'area' => 'script', 'id' => $this->id, 'body' => $body, 'on' => false);
    }
    
    public function addinspace()
    {
        $varsname = "__globals{$this->id}";
        $this->space->set('elements', $varsname, $this->elements);
    }
}