<?php
namespace LTS;

class Accordion extends Div
{
    public $vertical;
    public $ul;

    public function __construct($id = '')
    {
        parent::__construct($id);
        $this->addclass('Accordion');
        $this->ul = new Element();
        $this->add($this->ul);
    }

    public function newsection($caption)
    {
        $section = new Div();
        $section->caption = $caption;
        $section->addclass('AccordionSection');
        $this->add($section);
        return $section;
    }

    public function collaps()
    {
        return $this->option('collapsible', true);
    }

    public function active($numb = 1)
    {
        return $this->option('active', $numb - 1);
    }

    public function heightstyle($style = 'auto')
    {
        // 'auto', 'fill', 'content'
        return $this->option('heightStyle', $style);
    }

    public function compile()
    {
        $sections = $this->getchild('html', function($child) {
            return $child->tagname === 'div' && $child->hasclass('AccordionSection');
        });

        foreach ($sections as $section) {
            $h3 = new Element()
                ->tag('h3')
                ->capt($section->caption);
            $section->caption = '';

            $this->ul->add($h3)->add($section);
        }

        $options = $this->get('options');
        $optionsstr = $options === false ? '{}' : json_encode($options, JSON_UNESCAPED_UNICODE);

        $js = $this->compilemethod('open(numb)', "jQuery('#{$this->id}').accordion('option', 'active', numb - 1)");
        $this->compilemethod('disable', "jQuery('#{$this->id}').accordion('disable')", $js);
        $this->compilemethod('enable', "jQuery('#{$this->id}').accordion('enable')", $js);

        $this->js('ready')->add("jQuery('#{$this->id}').accordion({$optionsstr})");

        parent::compile();
    }
}
?>
