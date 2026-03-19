<?php
namespace LTS;

class Div extends Element
{
    private $_reverse;
    private $_wrapreverse;

    function __construct($id = '')
    {
        parent::__construct($id);
        $this->_reverse = false;
        $this->_wrapreverse = false;
    }

    public function block() 
    {
        return $this->display('block');
    }

    public function none() 
    {
        return $this->display('none');
    }

    public function inline() 
    {
        return $this->display('inline');
    }

    // FLEX
    public function flex() 
    {
        return $this->display('flex');
    }
  
    public function rowbox() 
    {
        return $this->flex()
            ->row()
            ->wrap()
            ->content('center')
            ->align('center');
    }

    public function columnbox() 
    {
        return $this->flex()
            ->column()
            ->wrap(false)
            ->content('center')
            ->align('center');
    }

    public function primaryaxis($axis)
    {
        if($axis == 'row')
            return $this->row();
        if($axis == 'column')
            return $this->column();

        return $this;
    }
    
    // start, end, center, between, around
    public function primarycontent($cont)
    {
        return $this->content($cont);
    }

    // start, end, baseline, stretch, center
    public function secondalign($align)
    {
        return $this->align($align);
    }

    // start, end, center, strech, between, around
    public function secondcontent($cont)
    {
        return $this->aligncontent($cont);
    }

    // Определение главной оси
    // в строку
    public function row()
    {
        $this->css()->add('flex-direction', $this->_reverse ? 'row-reverse' :'row');
        return $this;
    }

    // в колонку
    public function column()
    {
        $this->css()->add('flex-direction', $this->_reverse ? 'column-reverse' :'column');
        return $this;
    }

    // Расположение содержимого по главной оси
    // justify: start, end, center, between, around
    public function content($justify)
    {
        switch($justify)
        {
            case 'start':
            case 'beg':
            case 'begin':
                $this->css()->add('justify-content', 'flex-start');
                break;
            case 'end':
                $this->css()->add('justify-content', 'flex-end');
                break;
            case 'center':
                $this->css()->add('justify-content', 'center');
                break;
            case 'between':
                $this->css()->add('justify-content', 'space-between');
                break;
            case 'around':
                $this->css()->add('justify-content', 'space-around');
                break;
            case 'evenly': 
                $this->css()->add('justify-content', 'space-evenly');
                break;
        }
        
        return $this;
    }

    // Расположение контента по поперечной оси
    // $align: start, end, baseline, stretch, center
    public function align($align)
    {
        switch ($align)
        {
            case 'start':
            case 'beg':
            case 'begin':
                $this->css()->add('align-items', 'flex-start');
                break;
            case 'end':
                $this->css()->add('align-items', 'flex-end');
                break;
            case 'center':
                $this->css()->add('align-items', 'center');
                break;
            case 'stretch':
                $this->css()->add('align-items', 'stretch');
                break;
            case 'baseline':
                $this->css()->add('align-items', 'baseline');
                break;
        }

        return $this;
    }
    
    // В одну строку/колонку со сжатием или в несколько по поперечной оси
    public function wrap($w = true)
    {
        $this->css()->add('flex-wrap', $w ? ($this->_wrapreverse ? 'wrap-reverse' : 'wrap') : 'nowrap');
        return $this;
    }

    // в обратном порядке по главной оси
    public function reverse($r = true)
    {
        $this->_reverse = $r;

        $_d = $this->css()->add('flex-direction');

        switch($_d)
        {
            case 'row':
            case 'row-reverse':
                return $this->row();
            case 'column':
            case 'column-reverse':
                return $this->column();
        }

        return $this;
    }
    
    // в обратном порядке при разбитии по строкам по поперечной оси
    public function wrapreverse($r = true)
    {
        $this->_wrapreverse = $r;
        
        if($this->css()->add('flex-wrap') !== false)
            return $this->wrap();

        return $this;    
    }

    // расположение строк по поперечной оси при разбиении контента по строкам
    // $cont: start, end, center, strech, between, around
    public function aligncontent($cont)
    {
        switch($cont)
        {
            case 'start':
            case 'beg':
            case 'begin':
                $this->css()->add('align-content', 'flex-start');
                break;
            case 'end':
                $this->css()->add('align-content', 'flex-end');
                break;
            case 'center':
                $this->css()->add('align-content', 'center');
                break;
            case 'stretch':
                $this->css()->add('align-content', 'stretch');
                break;
            case 'between':
                $this->css()->add('align-content', 'space-between');
                break;
            case 'around':
                $this->css()->add('align-content', 'space-around');
                break;
            }

        return $this;
    }

    public function contentalign($cont)
    {
        return $this->aligncontent($cont);
    }

    public function isrow()
    {
        $_d = $this->css()->get('flex-direction');
        return $_d == 'row' || $_d == 'row_reverse';
    }

    public function iscolumn()
    {
        $_d = $this->css()->get('flex-direction');
        return $_d == 'column' || $_d == 'column_reverse';
    }

    /**
     * Устанавливает отступ между дочерними элементами (аналог gap в grid)
     * Работает как в строках, так и в колонках.
     * 
     * @param string $value Пример: '10px', '1em 20px'
     * @return $this
     */
    public function gap($value)
    {
        $this->css()->add('gap', $value);
        return $this;
    }

    /**
     * Устанавливает вертикальный отступ между строками
     * 
     * @param string $value Пример: '10px'
     * @return $this
     */
    public function rowGap($value)
    {
        $this->css()->add('row-gap', $value);
        return $this;
    }

    /**
     * Устанавливает горизонтальный отступ между колонками
     * 
     * @param string $value Пример: '10px'
     * @return $this
     */
    public function columnGap($value)
    {
        $this->css()->add('column-gap', $value);
        return $this;
    }

        /**
     * Устанавливает отступ только между элементами, центрирует контейнер
     * 
     * @return $this
     */
    public function gapCentered()
    {
        return $this
            ->content('center')
            ->align('center')
            ->gap('10px');
    }

    /**
     * Вертикальный контейнер с равными отступами между элементами
     * 
     * @return $this
     */
    public function gapStack()
    {
        return $this
            ->column()
            ->gap('10px');
    }

    /**
     * Горизонтальный контейнер с отступами между элементами
     * 
     * @return $this
     */
    public function gapInline()
    {
        return $this
            ->row()
            ->gap('10px');
    }
}
?>
