<?php
namespace LTS;

class Html extends Element
{
    public $filename;

    function __construct($id = '')
    {
        parent::__construct();
        if(strpos($id, '.') !== false)
        {
            $this->type = 'file';
            $this->filename = $id;
        }
        else
            $this->tagname = $id != '' ? $id : 'div';
    }

    public function shine($parent = null)
    {
        parent::shine($parent);
        if($this->type == 'file')
            $this->set('element', 'filename', $this->filename);
    }
}
?>
