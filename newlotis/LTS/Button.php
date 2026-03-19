<?php
namespace LTS;

class Button extends Element
{
    function __construct($id = '')
    {
        parent::__construct($id);
        $this->tagname = 'button';
        $this->attr('type', 'button');
    }

    public function ui()
    {
        $this->addclass('ui-button ui-corner-all ui-widget');
        return $this;
    }
}
?>
