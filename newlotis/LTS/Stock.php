<?php
namespace LTS;
/*
Использование:
1. Определяем сток. Он фиксирует изменения по массиву передаваемых значений в заранее определенной таблице
   Старые значения будут заменены на новые. Имена полей в таблице базы данных могут не совпадать с именами полей
   в передаваемом массиве массивов, поэтому возможно указывать сопоставление полей базы данных и передаваемых

$stock = LTS::Stock($table, ['nameintable' => 'nameinvalues', ... ]);

2. Определяем коллекторы. Они меняют какое-то одно поле в выбранной таблице.
   Значение этого поля может либо увеличиваться, либо уменьшаться 
   Второй параметр - поле, в котором предполагается id изменяемого объекта, например, товара
   Третий параметр - массив соотвествий изменяемых полей в таблице базы данных по сумме значений по ключу 

$stock->collector($goods, 'goods', ['nameingoods' => 'nameinvalues', ...], true);
// например, ['kolvo' => 'quantity', 'summa' => 'total']
// если четвертый параметр false, то предполагается, что коллекторы уменьшаются, а не увеличиваются

3. Фиксируем значения. Сохраненные ранее значения по ключу будут заменены новыми, 
   все коллекторы пересчитаются с учетом новых значений.

$stock->update($keys, $values);
// Например, keys = ['parent_doc' => 3, 'type' => 'incoming'] 
*/

class Stock
{
    public $dbtable;
    public $fieldmap = [];
    public $collectors = [];

    public function __construct(MySqlTable $dbtable, $fieldmap = [])
    {
        if (is_string($fieldmap)) {
            $arr = \LTS::explodestr($fieldmap);
            $fieldmap = [];
            foreach ($arr as $name) 
                $fieldmap[$name] = $name;
        }

        $this->dbtable = $dbtable;
        $this->fieldmap = $fieldmap;
    }

    public function collector(MySqlTable $dbtable, string $selectname, $fieldmap, bool $plus = true)
    {
        if (is_string($fieldmap)) {
            $arr = \LTS::explodestr($fieldmap);
            $fieldmap = [];
            foreach ($arr as $name) 
                $fieldmap[$name] = $name;
        }

        $this->collectors[] = [
            'dbtable' => $dbtable,
            'selectname' => $selectname,
            'fieldmap' => $fieldmap,
            'plus' => $plus
        ];

        return $this;
    }

    public function update(array $keys, array $newvalues)
    {
        // Проверка входных данных
        if (!is_array($keys)) return $this;
        if (!is_array($newvalues)) return $this;

        $this->dbtable->asarray = true;
        $oldvalues = $this->dbtable->all($keys);
        if($oldvalues === false)
            $oldvalues = [];
        $mathresult = $this->matchArrays($oldvalues, $newvalues);

        // Удаление удалённых строк
        $idsToDelete = [];
        foreach($mathresult['matched'] as $pair)
            if ($pair['new'] === null) 
                $idsToDelete[] = (int) $pair['old']['id'];  
        if(!empty($idsToDelete))
            $this->dbtable->delall('id', $idsToDelete); 

        // Перезапись измененных данных
        foreach($mathresult['matched'] as $pair)
            if($pair['new']) 
                $this->updateDB($keys, $this->remapNewValuesKeys($pair['new']), $pair['old']['id']);

        // Запись новых строк
        foreach($mathresult['unmatched'] as $result)
            $this->updateDB($keys, $this->remapNewValuesKeys($result));

        // Обновление коллекторов
        foreach($this->collectors as $collector)
            $this->updateCollector($collector, $mathresult);

        return $this;
    }

    private function updateCollector(array $collector, array $mathresult) {
        $db = $collector['dbtable'];
        $selectname = $collector['selectname'];
        $map = $collector['fieldmap'];
        $plus = $collector['plus'];
        $objs = [];
        $oldselectname = array_search($selectname, $this->fieldmap);
        $oldselectname = $oldselectname ? $oldselectname : $selectname;
        foreach($mathresult['matched'] as $values) { 
            $oldvalues = $values['old'];  
            $newvalues = $values['new'];         
            $oldid = isset($oldvalues[$oldselectname]) ? (int) $oldvalues[$oldselectname] : null;
            if($oldid) {
                $mapvalues = isset($objs[$oldid]) ? $objs[$oldid] : [];
                foreach($map as $dbkey => $valkey) {
                    $oldvalkey = array_search($valkey, $this->fieldmap);
                    $oldvalkey = $oldvalkey ? $oldvalkey : $valkey;
                    if(isset($oldvalues[$oldvalkey])) {
                        if(! isset($mapvalues[$dbkey]))
                            $mapvalues[$dbkey] = 0;
                        $mapvalues[$dbkey] -= $plus ? $oldvalues[$oldvalkey] : -$oldvalues[$oldvalkey];
                    }
                }
                $objs[$oldid] = $mapvalues;
            }
            if($newvalues) {
                $newid = isset($newvalues[$selectname]) ? (int) $newvalues[$selectname] : null;
                $mapvalues = isset($objs[$newid]) ? $objs[$newid] : [];
                foreach($map as $dbkey => $valkey) {
                    if(isset($newvalues[$valkey])) {
                        if(! isset($mapvalues[$dbkey]))
                            $mapvalues[$dbkey] = 0;
                        $mapvalues[$dbkey] += $plus ? $newvalues[$valkey] : -$newvalues[$valkey];
                    }
                }
                $objs[$newid] = $mapvalues;
            }
        }
        foreach($mathresult['unmatched'] as $newvalues) { 
            $newid = isset($newvalues[$selectname]) ? (int) $newvalues[$selectname] : null;
            $mapvalues = isset($objs[$newid]) ? $objs[$newid] : [];
            foreach($map as $dbkey => $valkey) {
                if(isset($newvalues[$valkey])) {
                    if(! isset($mapvalues[$dbkey]))
                        $mapvalues[$dbkey] = 0;
                    $mapvalues[$dbkey] += $plus ? $newvalues[$valkey] : -$newvalues[$valkey];
                }
            }
            $objs[$newid] = $mapvalues;
        }    
        foreach($objs as $id => $mapvalues)
        {
            $obj = $db->get($id);
            if($obj) {
                $isMatch = false;
                foreach($mapvalues as $dbkey => $val)
                    if(abs($val) >= 1e-9 && $db->field($dbkey) !== null) {
                        $isMatch = true;
                        $oldval = $obj->{$dbkey};
                        if($oldval === null) $oldval = 0;
                        $db->value($dbkey, $oldval + $val);
                    }
                if($isMatch)
                    $db->set($id);
            }
        }
    }

    private function remapNewValuesKeys(array $newItem)
    {
        $result = [];
        foreach ($newItem as $key => $value) {
            $oldKey = array_search($key, $this->fieldmap, true);
            $result[$oldKey !== false ? $oldKey : $key] = $value;
        }
        return $result;
    }

    private function matchArrays(array $oldvalues, array $newvalues)
    {
        $matched = [];
        $remainingNew = $newvalues;

        foreach ($oldvalues as $oldItem) {
            $found = false;
            foreach ($remainingNew as $i => $newItem) {
                $isMatch = true;
                foreach ($this->fieldmap as $dbField => $valueField) {
                    $oldHas = isset($oldItem[$dbField]);
                    $newHas = isset($newItem[$valueField]);
                    $equal = $oldHas && $newHas && $oldItem[$dbField] == $newItem[$valueField];

                    if (!$equal) {
                        $isMatch = false;
                        break;
                    }
                }

                if ($isMatch) {
                    $matched[] = ['old' => $oldItem, 'new' => $newItem];
                    unset($remainingNew[$i]);
                    $found = true;
                    break;
                }
            }

            if (!$found) 
                $matched[] = ['old' => $oldItem, 'new' => null];
        }

        return [
            'matched' => $matched,
            'unmatched' => array_values($remainingNew)
        ];
    }

    private function updateDB(array $keys, array $newItem, $id = null)
    {
        if(empty($newItem))
            return;
        foreach($newItem as $name => $val)
            if($this->dbtable->field($name) !== null)
                $this->dbtable->value($name, $val);
        foreach($keys as $name => $val)
            if($this->dbtable->field($name) !== null)
                $this->dbtable->value($name, $val);
        $this->dbtable->set($id);
    }
}
?>