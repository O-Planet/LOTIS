<?php
namespace LTS;

class Construct implements Ether
{
    use TypeChecker;

    // Ссылка на вселенную, в которой создается объект
    public $space; 
    // Идентификатор, используется в css
    public $id;
    // Вид объекта
    public $type;
    // Надо ли выводить, как отдельный объект
    public $shineobject;
    // Идентификатор переопределенного владельца
    public $owner;

    // Переопределяемые события. Все без параметров

    // Разрешено ли создание элемента. Возвращает true или false
    public $check;
    // Выполняется перед созданием элемента
    public $before;
    // Выполняется после создания элемента
    public $on;
    // Разрешено ли создание потомков элемента. Возвращает true или false
    public $checkchilds;
    // Выполняется перед созданием потомков элемента
    public $beforechilds;
    // Выполняется после добавления потомков элемента во вселенную
    public $onchilds;   

    // Глобальный нумератор объектов
    static $spaceid = 0; 

    public function __construct($id = '')
    {
        $this->shineobject = true;
        $this->id = $id == '' ? 'O' . ++Construct::$spaceid : $id;
        $this->type = 'none';
        $this->owner = null;
    }

    // ----------
    public function shine($parent = null) {}
    
    public function childs() {}

    public function compile() {}

    public function addinspace() {}

    public function create($space, $parent = null) // parent - id владельца
    {
        $this->space = $space;

        if($this->type == 'none' || (isset($this->check) && is_callable($this->check) && ! $this->check()))
            return $this;
    
        if(isset($this->before) && is_callable($this->before))
            $this->before();
        $this->shine($this->owner !== null ? $this->owner : $parent);
        if(isset($this->on) && is_callable($this->on))
            $this->on();
        
        $this->addinspace();

        if(isset($this->checkchilds) && is_callable($this->checkchilds) && ! $this->checkchilds())
            return $this;

        if(isset($this->beforechilds) && is_callable($this->beforechilds))
            $this->beforechilds(); 
        $this->childs(); 
        if(isset($this->onchilds) && is_callable($this->onchilds))
            $this->onchilds();

        return $this;
    }
}