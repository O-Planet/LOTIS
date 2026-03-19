<?php
namespace LTS;

class Tabs extends Div
{
	public $vertical;
	public $ul;

    public function __construct($id = '')
    {
        parent::__construct($id);
        $this->addclass('Tabs');
		$this->ul = new Element();
		$this->ul->tag('ul');
		$this->add($this->ul);
		$this->vertical = false;
    }

	public function newtab($caption)
	{
		$tab = new Div();
		$tab->caption = $caption;
		$tab->addclass('Tab');
		$this->add($tab);
		return $tab;
	}

	public function collaps()
	{
		return $this->option('collapsible', true);
	}

	public function onmouse()
	{
		return $this->option('event', 'mouseover');
	}

	public function vertical($v = true)
	{
		$this->vertical = $v;
		return $this;
	}

	public function compile()
	{
		$tabs = $this->getchilds('html', function($child) {
			return $child->tagname === 'div' && $child->hasclass('Tab');
		});

		foreach ($tabs as $tab) {
			$li = new Html('li');
			$a = new Html('a');
			$a->attr('href', "#{$tab->id}")
				->capt($tab->caption);
			$li->add($a);
			$this->ul->add($li);
			$tab->caption = '';
		}

		$options = $this->get('options');
		$optionsstr = $options === false ? '' : JS::paramobject($options);

		$js = $this->compilemethod('vertical', 
<<<JS
			jQuery('#{$this->id}').tabs().addClass('ui-tabs-vertical ui-helper-clearfix');
			jQuery('#{$this->id} li').removeClass('ui-corner-top').addClass('ui-corner-left');
JS
			);
		$this->compilemethod('horizontal',
<<<JS
			jQuery('#{$this->id}').tabs().removeClass('ui-tabs-vertical ui-helper-clearfix');
			jQuery('#{$this->id} li').removeClass('ui-corner-left').addClass('ui-corner-top');
JS
			, $js);

		$this->js('ready')->add("jQuery('#{$this->id}').tabs({$optionsstr})");

		if($this->vertical)
			$this->js('ready')->add("LTS.get('{$this->id}').vertical()");

		parent::compile();
	}
}
?>
