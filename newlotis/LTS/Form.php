<?php
namespace LTS;

class Form extends Element
{
	public $events;

    function __construct($id = '')
    {
        parent::__construct($id);
		$this->events = new Events("{$this->id}_events");
		$this->tagname = 'form';
		if($id != '')
			$this->attr('name', $id);
    }

	public function generate($objects)
	{
		// $objects - массив или объект, состоящий из описаний полей
		// { name, caption, type, value, values, id }

		if(is_string($objects))
			$objs = (array) json_decode($objects);
		else
			$objs = (array) $objects;

		foreach($objs as $obj)
		{
			$o = (array) $obj;

			if(! array_key_exists('name', $o))
				continue;

			$name = $o['name'];
			$type = array_key_exists('type', $o) ? $o['type'] : 'text';
			$caption = array_key_exists('caption', $o) ? $o['caption'] : '';
			$values = array_key_exists('values', $o) ? $o['values'] : null;
			$id = array_key_exists('id', $o) ? $o['id'] : false;

			switch($type)
			{
				case 'select':
					$input = $this->select($name, $caption, $values);
					break;
				case 'table':
					if(array_key_exists('dbtable', $o)) {
						$dbtable = $o['dbtable'];
						$input = $this->table($name, $dbtable, $caption);
					}
					break;
				case 'textarea':
					$input = $this->textarea($name, $caption);
					break;
				case 'submit':
					$input = $this->submit($name, $caption);
					break;
				case 'reset':
					$input = $this->reset($name, $caption);
					break;
				case 'range':
					$min = array_key_exists('min', $o) ? $o['min'] : 1;
					$max = array_key_exists('max', $o) ? $o['max'] : 100;
					$input = $this->range($name, $caption, $min, $max);
					break;
				case 'button':
					$input = $this->button($name, $caption);
					break;
				default: 
					$input = $this->input($type, $name, $caption);
			}

			if($id)
				$input->id = $id;

			if(array_key_exists('value', $o))
				$input->value($o['value']);

			$fixvals = ['id', 'type', 'name', 'caption', 'values', 'value', 'min', 'max', 'class', 'dbtable'];

			foreach($o as $attr => $val)
				if(! in_array($attr, $fixvals))
					$input->attr($attr, $val);

			if(array_key_exists('class', $o))
				$input->addclass($o['class']);
		}

		return $this;
	}

	public function cells($names = '', $captionscol = 1, $inputscol = 2)
	{
		$cells = new Cells();

		// собираем все поля в массив childs
		$childs = array();
		foreach($this->get('childs') as $child)
			if($child->type == 'html')
				if($child->tagname == 'input' || $child->tagname == 'select' || $child->tagname == 'textarea' || $child->tagname == 'button')
					$childs[$child->attr('name')] = $child;
				elseif($child->hasclass('lookup-field')) 
					$childs[$child->input->attr('name')]= $child;				
			
		// выбираем только те поля из childs, которые перечислены в names
		if(empty($names)) // если не задана выборка имен
			$fields = $childs;
		else
		{
			$fields = array();
			$_names = \LTS::explodestr($names); 
			foreach($_names as $name)
			{   
				// каждое имя может быть массивом или перечислением имен через запятую
				$__names = \LTS::explodestr($name); 
				if(count($__names) == 1) // только одно имя
				{
					$_name = $__names[0];
					if(array_key_exists($_name, $childs))
						$fields[$_name] = $childs[$_name];
					else
						$fields[$_name] = null;
				}
				else
				{
				// несколько имен: тогда хотим объединить несколько полей в одну строку
					$_arr = array();
					$newname = '';
					foreach($__names as $_name)
					{
						if(array_key_exists($_name, $childs))
							$_arr[$_name] = $childs[$_name];
						else
							$_arr[$_name] = null;
						$newname = $newname . (empty($newname) ? '' : ',') . $_name;
					}
					$fields[$newname] = $_arr;
				}	
			}
		}

		// строим cells
		$k = 1;
		foreach($fields as $name => $_fields)
		{
			$inpitsnames = '';
			if(! empty($_fields))
			{
				if(! is_array($_fields))
				{
					if($captionscol != 0)
					{
						$label = $_fields->label();
						if($label !== false)
						{
							$cell = $cells->cell($k, $captionscol);
							$label->owner = $cell->id;
						}
						else
							$cells->cell($k, $captionscol);
					} 
					if($inputscol != 0)
					{
						$cell = $cells->cell($k, $inputscol);
						$_fields->owner = $cell->id;
						$inpitsnames .= '_' . $_fields->attr('name');
					}
				}
				else
					foreach($_fields as $field)
						if(! empty($field))
						{
							if($captionscol != 0)
							{
								$label = $field->label();
								if($label !== false)
								{
									$cell = $cells->cell($k, $captionscol);
									$label->owner = $cell->id;
								}
								else
									$cells->cell($k, $captionscol);
							} 
							if($inputscol != 0)
							{
								$cell = $cells->cell($k, $inputscol);
								$field->owner = $cell->id;
								$inpitsnames .= '_' . $field->attr('name');
							}
						}
			}

			if(! empty($inpitsnames))
				$cells->line($k)->addclass('Row' . $inpitsnames);

			//if($captionscol != 0 || $inputscol != 0)
			//	$cells->line($k)->attr('name', $name);
			++$k;
		}

		// Переписываем childs, добавляя в начало перед самым первым input сформированный cells
		$newchilds = array();
		$perv = true;
		foreach($this->get('childs') as $name => $child)
		{
			if($perv && $child->type == 'html' &&
				($child->tagname == 'input' || 
				$child->tagname == 'select' || 
				$child->tagname == 'textarea' || 
				$child->tagname == 'button' ||
				$child->hasclass('lookup-field')))
			{
				$newchilds[$cells->id] = $cells;
				$perv = false;
			}
			$newchilds[$name] = $child;
		}

		$this->storage['childs'] = $newchilds;

		return $cells;
	}

	public function grid($regions, $grid = null)
	{		
		if ($grid === null) 
			$grid = new Grid();

		// Проходим по областям и заполняем их полями
		foreach ($regions as $areaName => $fields) {
			$areaContainer = $grid->area($areaName);

			foreach ($fields as $name) {
				$input = $this->getchild('html', [
					'tagname' => ['input', 'textarea', 'select', 'button'],
					'name'    => $name
				]);
				if($input === false) {
					$input = $this->getchild('html', function($el) use ($name) {
						return (
							$el instanceof Div &&
							$el->hasclass('lookup-field') && 
							$el->input->attr('name') === $name
						);
					});
				}
				if ($input !== false) {
					$areaContainer->add($input);
				}
			}
		}

		// Добавляем форму в Grid
		$grid->add($this);

		// Указываем, что владелец формы — Grid
		$this->owner = $grid->id;

		return $grid;
	}

	public function field($name) {
		$childs = $this->get('childs');
		foreach($childs as $child)
			if($child->type == 'html')
				if($child->attr('name') === $name)
					return $child;
				elseif($child->hasclass('lookup-field') && $child->input->attr('name') === $name)
					return $child;
		return false;
	}

	public function input($type, $name, $caption = '')
	{
		$input = new Input();
		$input->setinput($type, $name, $caption);
		$input->form = $this;
		$this->add($input);
		return $input;
	}	

    public function button($name, $caption = '')
	{
		$input = new Input();
		$input->button($name, $caption);
		$input->form = $this;
		$this->add($input);
		return $input;
	}

	public function table($name, $dbtable, $caption = '')
	{
		// Создаем LookupField
		$lookup = new LookupField();
		$lookup->dbtable($dbtable);
		$capt = $lookup->searchfield == 'name' ? 'Наименование' : $lookup->tablefield;
		$lookup->head([$lookup->tablefield => $capt]);
		$lookup->input->attr('name', $name);

		// Добавляем label, если указан
		if ($caption !== '') 
			$lookup->label($caption);

		// Добавляем в форму
		$this->add($lookup);

		return $lookup;
	}

	public function select($name, $caption = '', $values = null)
	{		
		$input = new Input();
		$input->select($name, $caption, $values);
		$input->form = $this;
		$this->add($input);
		return $input;
	}

    public function textarea($name, $caption = '')
	{
		$input = new Input();
		$input->textarea($name, $caption);
		$input->form = $this;
		$this->add($input);
		return $input;
	}

    public function checkbox($name, $caption = '')
	{
		$input = new Input();
		$input->checkbox($name, $caption);
		$input->form = $this;
		$this->add($input);
		return $input;
	}

    public function file($name, $caption = '')
	{
		$input = new Input();
		$input->file($name, $caption);
		$input->form = $this;
		$this->add($input);
		return $input;
	}

    public function hidden($name, $caption = '')
	{
		$input = new Input();
		$input->hidden($name, $caption);
		$input->form = $this;
		$this->add($input);
		return $input;
	}

    public function image($name, $caption = '')
	{
		$input = new Input();
		$input->image($name, $caption);
		$input->form = $this;
		$this->add($input);
		return $input;
	}

    public function password($name, $caption = '')
	{
		$input = new Input();
		$input->password($name, $caption);
		$input->form = $this;
		$this->add($input);
		return $input;
	}

    public function radio($name, $caption = '', $values = null)
	{
		$input = new Input();
		$input->radio($name, $caption);
		$input->form = $this;
		$this->add($input);
		if(is_array($values))
		{
			$p = true;
			foreach($values as $val)
				if($p)
				{
					$p = false;
					$input->value($val);
				}
				else
				{
					$inp = new Input();
					$inp->radio($name)
						->value($val)
						->form = $this;
					$this->add($inp);				
				}
		}
		return $input;
	}

	public function reset($name, $caption = '')
	{
		$input = new Input();
		$input->reset($name, $caption);
		$input->form = $this;
		$this->add($input);
		return $input;
	}

	public function submit($name, $caption = '')
	{
		$input = new Input();
		$input->submit($name, $caption);
		$input->form = $this;
		$this->add($input);
		return $input;
	}


	public function text($name, $caption = '')
	{
		$input = new Input();
		$input->text($name, $caption);
		$input->form = $this;
		$this->add($input);
		return $input;
	}

	// HTML 5

	public function email($name, $caption = '')
	{
		$input = new Input();
		$input->email($name, $caption);
		$input->form = $this;
		$this->add($input);
		return $input;
	}

	public function tel($name, $caption = '')
	{
		$input = new Input();
		$input->tel($name, $caption);
		$input->form = $this;
		$this->add($input);
		return $input;
	}

	public function url($name, $caption = '')
	{
		$input = new Input();
		$input->url($name, $caption);
		$input->form = $this;
		$this->add($input);
		return $input;
	}

	public function date($name, $caption = '')
	{
		$input = new Input();
		$input->date($name, $caption);
		$input->form = $this;
		$this->add($input);
		return $input;
	}

	public function color($name, $caption = '')
	{
		$input = new Input();
		$input->color($name, $caption);
		$input->form = $this;
		$this->add($input);
		return $input;
	}

	public function time($name, $caption = '')
	{
		$input = new Input();
		$input->time($name, $caption);
		$input->form = $this;
		$this->add($input);
		return $input;
	}

	public function number($name, $caption = '')
	{
		$input = new Input();
		$input->number($name, $caption);
		$input->form = $this;
		$this->add($input);
		return $input;
	}

	public function range($name, $caption = '', $min = 1, $max = 100)
	{
		$input = new Input();
		$input->range($name, $caption, $min, $max);
		$input->form = $this;
		$this->add($input);
		return $input;
	}

	public function getEvent() {
		return $this->events;
	}

	public function eventname($button)
	{
		$buttonid = is_string($button) ? $button : $button->id;
		return $buttonid;
	}

	public function beforeevent($button, $func) {
		$eventname = $this->eventname($button);
		if(is_string($func))
			$this->events->js()->add("before{$eventname}", $func);
		else
			$this->events->add("before{$eventname}", $func);
		return $this;
	}

	public function checkevent($button, $func) {
		$eventname = $this->eventname($button);
		if(is_string($func))
			$this->events->js()->add("check{$eventname}", $func);
		else
			$this->events->add("check{$eventname}", $func);
		return $this;
	}

	public function onevent($button, $func) {
		$eventname = $this->eventname($button);
		if(is_string($func))
			$this->events->js()->add("on{$eventname}", $func);
		else
			$this->events->add("on{$eventname}", $func);
		return $this;
	}

	public function event($button, $func)
	{
		$buttonid = $this->eventname($button);
		if($func === false || $func === null) {
			$this->set('clicks', $buttonid, false);
			$this->events->add($buttonid, false);
			$this->events->js()->add("{$buttonid}(values)", false);
		}
		else
		if(is_string($func))
		{
			$this->events->js()->add("{$buttonid}(values)", $func);
			$this->set('clicks', $buttonid, true);
		}
		else
		if(is_callable($func))
			$this->events->add($buttonid, $func);

		return $this;
	}

	public function error($button, $body)
	{
		$buttonid = is_string($button) ? $button : $button->id;
		$this->events->error($buttonid, $body);
		return $this;		
	}

	public function compile()
	{
		$eventschilds = $this->events->get('childs');
		if(count($eventschilds) > 0)
			$this->add($this->events);

		$js = $this->compilemethod('value(name, val)', "return ltsForm.value('{$this->id}', name, val)");
		$this->compilemethod('values(vals)', "return ltsForm.values('{$this->id}', vals)", $js);
		$this->compilemethod('data()', "return ltsForm.data('{$this->id}')", $js);
		$this->compilemethod('clear()', "return ltsForm.clear('{$this->id}')", $js);

		$clicks = $this->get('clicks');
		if($clicks !== false)
		{
			foreach($clicks as $buttonid => $buttonenable)
				if($buttonenable)
				{
					$button = $this->child($buttonid);
					if($button === false)
						$button = $this->child("{$this->id}_{$buttonid}");
					if($button === false) {
						$childs = $this->getchilds('html', ['tagname' => 'button', 'name' => $buttonid]); 
						if(count($childs) > 0) 
							$button = $childs[0]; 
					}
					if($button !== false)
					{
						$name = $button->attr('name');
						if($name !== false && $name != $button->id)
							$this->compilemethod($name, "LTS.get('{$button->id}').click()", $js);
						$button->click("let values = LTS.get('{$this->id}').data(); LTS.get('{$this->events->id}').{$buttonid}(values);");
					}
				}
		}

		parent::compile();
	}
}
?>
