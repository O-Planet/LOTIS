<?php
namespace LTS;

class Cells extends Div
{
	public $rowscount;

    public function __construct($id = '')
    {
        parent::__construct($id);
        $this->addclass('Cells');
		$this->rowscount = 0;
    }

	// numb = 1, ...
	public function insert($numb)
	{
		if($numb > $this->rowscount)
			return $this->line($numb);

        $row = new Columns();
        $row->addclass("Row Row{$numb}");

		$newchilds = array(); 

		for($k = 1; $k < $this->rowscount + 1; $k++)
		{
			$obj = $this->get('childs', $k);

			if($k < $numb)
				$ind = $k;
			else 
				if($k == $numb)
				{
					$newchilds[$k] = $row;
					$ind = $k + 1; 
				}
				else
					$ind = $k + 1;

			if($k != $ind)
				$obj->removeclass("Row{$k}")->addclass("Row{$ind}");

			$newchilds[$ind] = $obj;
		}

		$this->storage['childs'] = $newchilds;
		++$this->rowscount;

		return $row;
	}

	// $numb = 1, ...
    public function line($numb = null)
    {
		$_numb = $numb === null ? $this->rowscount + 1 : $numb;
		$row = $this->get('childs', $_numb);
        if($row === false)
		{
			for($k = $this->rowscount + 1; $k <= $_numb; $k++)
			{
            	$row = new Columns();
            	$row->addclass("Row Row{$k}");
            	$this->add($k, $row);
        	}

			if($this->rowscount < $_numb)
				$this->rowscount = $_numb;
		}	

        return $row;
    }

    public function cell($linenumb, $columnnumb)
    {
        return $this->line($linenumb)->cell($columnnumb);
    }

	public function add($name, $value = null)
	{
		if(is_array($name) && $value === null)
		{
			$isrecords = true;
			foreach($name as $val)
				if(is_array($val) || (is_object($val) && ! property_exists($val, 'shineobject')))
					continue;
				else
				{
					$isrecords = false;
					break;
				}

			if($isrecords)
				foreach($name as $val) 
					$this->line()->add($val);
			else
				$this->line()->add($name);

			return $this;
		}

		return parent::add($name, $value);
	}
}

?>