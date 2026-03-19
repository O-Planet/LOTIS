<?php
namespace LTS;

class Dialog extends Div
{
    public function __construct($id = '')
    {
        parent::__construct($id);
        $this->addclass('Dialog');
    }

	public function capt($str) {
		$this->attr('title', $str);
		return $this;
	}

	public function autoopen($open = true)
	{
		return $this->option('autoOpen', $open);
	}

	public function show($effect = 'blind', $duration = 1000)
	{
		return $this->option('show', array('effect' => $effect, 'duration' => $duration));
	}

	public function hide($effect = 'explode', $duration = 1000)
	{
		return $this->option('hide', array('effect' => $effect, 'duration' => $duration));
	}

	public function button($caption, $onClick = '')
	{
		$this->set('buttons', $caption, $onClick);
		return $this;
	}
	
	public function modal($val = true)
	{
		return $this->option('modal', $val);
	}
	
	public function left($val)
	{
		return $this->option('left', $val);
	}

	public function top($val)
	{
		return $this->option('top', $val);
	}

	public function width($val)
	{
		return $this->option('width', $val);
	}

	public function height($val)
	{
		return $this->option('height', $val);
	}

	public function compile()
	{
		$buttons = $this->get('buttons');
		$buttonsclicks = '';
		if($buttons !== false)
		{
			$buttonnumb = 0;
			$buttonsarr = [];
			foreach($buttons as $buttoncapt => $buttonfunc)
			{
				++$buttonnumb;
				$buttonsclicks .= "
LTS({$this->id}).button{$buttonnumb} = function () { {$buttonfunc} };";
				$buttonsarr[$buttoncapt] = "function () { LTS({$this->id}).button{$buttonnumb}(); return this; }";
			}
			$this->option('buttons', $buttonsarr);
		}

		$options = $this->get('options');
		$optionsstr = $options === false ? '{}' : JS::paramobject($options);

		$this->js('ready')->add(
<<<JS
	{$buttonsclicks}
	$({$this->id}).dialog({$optionsstr})
JS
);

		$js = $this->compilemethod('open', "jQuery('#' + '{$this->id}').dialog('open'); return this;");
		$this->compilemethod('close', "jQuery('#' + '{$this->id}').dialog('close'); return this;", $js);

		parent::compile();
	}
}
?>
