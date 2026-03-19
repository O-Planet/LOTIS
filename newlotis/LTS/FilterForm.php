<?php
namespace LTS;

class FilterForm extends Form
{
    public $datatable;

    function __construct($id = '')
    {
        parent::__construct($id);
        $this->addclass('filterform');
    }

    public function assign($datatable, $objects, $auto = true)
    {
        $this->datatable = $datatable;

		if(is_string($objects))
			$objs = (array) json_decode($objects);
		else
			$objs = (array) $objects;

        if($auto)
        {
            foreach($objs as &$ob)
            {
                $ob['data-table'] = $datatable->id;
                if(isset($ob['class']))
                    $ob['class'] .= ' filterfield';
                else
                    $ob['class'] = 'filterfield';
            }
            unset($ob);
        }
          
        $cells = $this->generate($objs)
            ->cells(); 

        $k = 0;
        foreach($objs as $ob)
        {
            ++$k;
            if(isset($ob['addlist']))
            {
                $listbutton = new Button();
                $this->add($listbutton);
                $listbutton
                    ->capt('🔻')
                    ->width('60px')
                    ->attr('data-field', $ob['name'])
                    ->attr('data-form', $this->id)
                    ->attr('data-table', $datatable->id)
                    ->addclass('filterlistbutton')
                    ->owner = $cells->cell($k, 2)->id;
            }
            if(! array_key_exists('type', $ob) || $ob['type'] != 'button')
            {
                $delbutton = new Button();
                $this->add($delbutton);
                $delbutton
                    ->capt('❌')
                    ->width('60px')
                    ->attr('data-field', $ob['name'])
                    ->attr('data-form', $this->id)
                    ->attr('data-table', $datatable->id)
                    ->addclass('filterdelbutton')
                    ->owner = $cells->cell($k, 2)->id;
            }
        }
        
        return $cells;
    }

    public function compile()
    {
        $this->compilemethod('filter(values)',
<<<JS
                const isEmpty = Object.values(values).every(val => 
                    val === null || 
                    val === undefined || 
                    (typeof val === 'string' && val.trim() === '')
                );
                if (isEmpty) 
                    ltsDataTable.clearFilter('{$this->datatable->id}'); 
                else {
                    var filtered = ltsDataTable.filter('{$this->datatable->id}', values);
                    ltsDataTable.applyFilter('{$this->datatable->id}', filtered);
                }
JS
        );
        parent::compile();
    }
}
?>
