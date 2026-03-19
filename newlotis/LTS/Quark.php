<?php
namespace LTS;

use Exception;

class Quark extends Construct
{
    public $tagname = '';
    public $classname = '';
    public $caption = '';
    // Архив всех подчиненных объектов
    public $storage = array();
    public $jscreate = null;

    public function __construct($id = '')
    {
        parent::__construct($id);
        $this->storage['childs'] = array();  // потомки
        $this->storage['element'] = array(); // элемент, создаваемый методом shine
    }

    public function setid($id)
    {
        $this->id = $id;
        return $this;
    }

    // Добавить объект в архив
    // Объекты распределены по секциям
    public function set($section, $name, $value = null)
    {
        if(! array_key_exists($section, $this->storage))
            $this->storage[$section] = array();

        if($name !== null)
            if($value === null)
                $this->storage[$section][] = $name;
            else 
                $this->storage[$section][$name] = $value; 

        return $this;
    }

    // Получить объект из архива
    // Если name не укказано, то возвращает секцию
    public function get($section, $name = null)
    {
        if($name === null)
            return array_key_exists($section, $this->storage) ? $this->storage[$section] : false;
        else
            if(array_key_exists($section, $this->storage) && array_key_exists($name, $this->storage[$section]))
                return $this->storage[$section][$name];

        return false;
    }

    // Добавить в секцию childs потомка
    public function add($name, $value = null)
    {
        $this->set('childs', $name, $value);
        return $this;
    }

    // Добавить несколько потомков
    public function addmany(...$values)
    {
        foreach($values as $v)
            $this->add($v);

        return $this;
    }

    // Удалить потомка
    public function del($child)
    {
        $childs = $this->get('childs');
        $obj = is_string($child) ? $this->child($child) : $child;
        if($obj !== false)
        {
            $key = array_search($obj, $childs);
            if($key !== false)
                unset($this->storage['childs'][$key]);           
        }

        return $this;
    }

    // Добавляем новый объект в конкретную позиция: перед объектом или по номеру, начиная с 1
    public function addin($pos, $name, $value = null)
    {
        $childs = $this->get('childs');
        if ($childs === false) 
            $childs = [];

        // Определяем позицию вставки
        $insertIndex = count($childs); // по умолчанию — в конец

        if (is_int($pos) && $pos >= 1) {
            // Число: вставляем на позицию (1-based)
            $insertIndex = $pos - 1;
        } elseif (is_object($pos) && ! isset($pos->id)) {
            // Объект: ищем его в массиве
            $index = array_search($pos, $childs, true);
            if ($index !== false) {
                $insertIndex = $index;
            }
        }
        else
        {
            if(is_object($pos))
                $id = $pos->id;
            else
                $id = $pos;
            // Строка: ищем объект с таким id
            foreach ($childs as $index => $child) {
                if (is_object($child) && $child->id === $id) {
                    $insertIndex = $index;
                    break;
                }
            }
        } 
        
        // Если позиция выходит за пределы — в конец
        if ($insertIndex < 0) 
            $insertIndex = 0;
        elseif ($insertIndex > count($childs)) 
            $insertIndex = count($childs);

        // Создаем временный массив
        $newChilds = [];

        // Копируем элементы до позиции
        for ($i = 0; $i < $insertIndex; $i++) 
            $newChilds[] = $childs[$i];

        // Добавляем новый объект, используя логику add()
        $this->add($name, $value);

        // Получаем только что добавленный объект
        $addedObjects = array_slice($this->get('childs'), count($childs));
        foreach ($addedObjects as $obj) 
            $newChilds[] = $obj;

        // Копируем оставшиеся элементы
        for ($i = $insertIndex; $i < count($childs); $i++) 
            $newChilds[] = $childs[$i];

        // Обновляем storage
        $this->storage['childs'] = $newChilds;

        return $this;
    }

    public function child($id)
    {
        $childs = $this->get('childs');
        foreach($childs as $child)
            if(is_object($child) && $child->id == $id)
                return $child;

        return false;
    }

    public function getchild($type, $par = null)
    {
        $ret = $this->getchilds($type, $par, 1);
        if($ret !== false && count($ret) > 0)
            return $ret[0];

        return false;
    }

    public function getchilds($type, $par = null, $kol = 0)
    {
        $childs = $this->get('childs');
        if ($childs === false) return false;

        $ret = array();
        $_kol = 0;

        foreach ($childs as $child) {
            if (!is_object($child)) continue;

            // Проверяем тип
            if (!property_exists($child, 'type')) continue;

            $types = is_array($type) ? $type : [$type];
            if (!in_array($child->type, $types)) continue;

            // Если нет условия 
            if ($par === null) {
                $ret[] = $child;
                ++$_kol;
                if($kol != 0 && $_kol == $kol)
                    break;
                else
                    continue;
            }

            // Если условие — массив свойств
            if (is_array($par)) {
                $match = true;
                foreach ($par as $name => $val) {
                    if($child->attr($name) === $val)
                        continue;
                    if (!property_exists($child, $name) || $child->{$name} != $val) {
                        $match = false;
                        break;
                    }
                }
                if ($match) {
                    $ret[] = $child;
                    ++$_kol;
                    if($kol != 0 && $_kol == $kol)
                        break;
                    else
                        continue;
                }
            }

            // Если условие — функция
            if (is_callable($par)) 
                if ($par($child) === true) 
                {
                    $ret[] = $child;
                    ++$_kol;
                    if($kol != 0 && $_kol == $kol)
                        break;
                }
        }

        return $ret;
    }

    public function move($newparent, $obj)
    {
        $id = is_string($obj) ? $obj : $obj->id;
        $childs = $this->get('childs');
        
        foreach ($childs as $i => $o) {
            if (isset($o->id) && $o->id == $id) {
                // Добавляем в нового родителя
                $newparent->add($id, $o);
                // Удаляем из storage текущего родителя
                $this->del($o);
                return $this;
            }
        }

        // Если объект не был найден среди потомков, но передан как объект
        if (is_object($obj)) {
            $newparent->add($id, $obj);
        }

        return $this;
    }

    // JS
    public function js($area = 'script')
    {
        $childs = $this->get('childs');
        if($childs !== false) 
            foreach($childs as $child)
                if(is_object($child) && 
                    property_exists($child, 'type') && 
                    $child->type == 'JS' && 
                    $child->area == $area)
                    return $child;

        if($area == 'create')
        {
            if($this->jscreate === null)
            {
                $this->jscreate = new JS();
                $this->jscreate->area = 'create';
            }
            return $this->jscreate;
        }

        $js = new JS();
        $js->area = $area;
        $this->add($js); 

        return $js;
    }

    // Прототипы функций, отвечающих за создание объекта в документе 
    public function childs()
    {
        $childs = $this->get('childs');
        foreach($childs as $child)
            if ($child instanceof Construct && $child->shineobject) 
                $child->create($this->space, $this->id);
    }

    public function compile()
    {
        $childs = $this->get('childs');
        foreach($childs as $child)
            if(is_object($child) && method_exists($child, 'compile'))
                $child->compile();
        if($this->jscreate !== null)
        {
            $this->add($this->jscreate);
            $this->jscreate->area = 'ready';
            $this->jscreate->compile();
        }
    }

    public function addinspace()
    {
        $el = $this->get('element');
        if(count($el) > 0)
            $this->space->set('elements', $this->id, $el);
    }
}
?>
