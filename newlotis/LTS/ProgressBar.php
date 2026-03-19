<?php
namespace LTS;

class ProgressBar extends Div
{
    public function __construct($id = '')
    {
        parent::__construct($id);
        $this->addclass('ProgressBar');
    }

    public function value($val)
    {
        return $this->option('value', $val);
    }

    public function min($val)
    {
        return $this->option('min', $val);
    }

    public function max($val)
    {
        return $this->option('max', $val);
    }

    public function text($val = '')
    {
        $this->option('value', 0); // чтобы инициализировать options
        $this->set('text', $val);
        return $this;
    }

    public function compile()
    {
        $options = $this->get('options') ?: [];
        $optionsstr = json_encode($options, JSON_UNESCAPED_UNICODE);

        $customText = $this->get('text');
        $textCode = $customText ? "
jQuery('#{$this->id}').on('progresschange', function(event, ui) {
    jQuery(this).find('.progress-label').text('{$customText}: ' + ui.value + '%');
});" : "
jQuery('#{$this->id}').on('progresschange', function(event, ui) {
    jQuery(this).find('.progress-label').text(ui.value + '%');
});";

        $js = $this->compilemethod('value(val)', "jQuery('#{$this->id}').progressbar('option', 'value', val)"); 
        $this->compilemethod('disable', "jQuery('#{$this->id}').progressbar('disable')", $js); 
        $this->compilemethod('enable', "jQuery('#{$this->id}').progressbar('enable')", $js); 

        $jsready = new JS('ready');
        $jsready->compile = false;
        $this->add($jsready);
        $jsready->add(
<<<JS
jQuery('#{$this->id}').progressbar({$optionsstr});
jQuery('<div class="progress-label">').css({
    position: 'absolute',
    top: '50%',
    left: '50%',
    transform: 'translate(-50%, -50%)',
    font: 'bold 12px Arial',
    color: '#555'
}).appendTo('#{$this->id}');
{$textCode}
JS
            );

        parent::compile();
    }
}