<?php
namespace LTS;

class Columns extends Div
{
    public $columnscount;

    public function __construct($id = '')
    {
        parent::__construct($id);
        $this->addclass('Columns');
		$this->columnscount = 0;
    }

	public function cell($columnnumb = null)
	{
        $_columnnumb = $columnnumb === null ? $this->columnscount + 1 : $columnnumb;

		if(is_int($_columnnumb)) // если номер столбца
		{
			if($_columnnumb > $this->columnscount) // если он больше кол-ва столбцов
			{
				for($k = $this->columnscount + 1; $k <= $_columnnumb; $k++) // добавляем пустые столбцы
				{
					$cell = new Div();
					$cell->addclass("Column Column{$k}");
					$this->set('keys', $k); // добавляем ключи
					parent::add($k, $cell); // добавляем столбцы
					++$this->columnscount;  // увеличиваем их кол-во
				}
			}
			else // если хотим получить ранее созданный
			{
				$keys = $this->get('keys'); // получаем ключи
				$key = $keys[$_columnnumb - 1]; // берем по номеру ключ
				$cell = $this->get('childs', $key); // получаем ячейку по ключу
			}
		}
		else // если имя столбца, а не номер
		{
			$keys = $this->get('keys'); // получаем ключи
			if(is_array($keys) && in_array($columnnumb, $keys)) // если ключ был ранее
				$cell = $this->get('childs', $columnnumb);
			else
			{
				$cell = new Div();
				$cell->addclass("Column Column{$columnnumb}");
				$this->set('keys', $columnnumb); // добавляем ключ
				parent::add($columnnumb, $cell); // добавляем столбец
				++$this->columnscount;  // увеличиваем их кол-во
			}
		}
		
		return $cell;
	}

	public function add($name, $value = null)
	{
		if((is_array($name) || (is_object($name) && ! property_exists($name, 'shineobject'))) && $value === null)
		{
			$isassarray = \LTS::isAssociativeArray((array) $name);
			foreach((array) $name as $key => $val)
			{
				$_key = $isassarray ? $key : ($key + 1);
				$cell = $this->cell($_key);
				if(is_object($val))
					$cell->add($val);
				else
					$cell->caption = $val;

				parent::add($_key, $cell);
			}

			return $this;
		}
        else
        if($value === null)
        {
            $cell = $this->cell();
            if(is_object($name))
                $cell->add($name);
            else
                $cell->caption = $name;

            parent::add($cell);
        }
        else
        {
            $cell = $this->cell($name);
            if(is_object($value))
                $cell->add($value);
            else
                $cell->caption = $value;

            parent::add($cell);
        }

		return $this;
	}
}
