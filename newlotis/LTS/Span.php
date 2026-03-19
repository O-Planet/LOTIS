<?php
namespace LTS;

class Span extends Element
{
    function __construct($id = '')
    {
        parent::__construct($id);
        $this->tagname = 'span';
    }
}
?>
