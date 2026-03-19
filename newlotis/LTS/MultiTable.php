<?php
namespace LTS;

class MultiTable
{
    private $header;
    private $details = [];

    public function __construct(MySqlTable $header = null)
    {
        $this->header($header);
    }

    public function header($header)
    {
        $this->header = $header;
    }

    // === Управление деталями ===

    /**
     * Добавить табличную часть
     * @param MySqlTable $detailTable Таблица детали
     * @param string|null $name Псевдоним (если null — имя таблицы)
     * @return $this
     */
    public function add(MySqlTable $detailTable, $name = null)
    {
        $headerName = $this->header->name;

        if ($name === null) {
            $name = $detailTable->name;
        }

        $linkField = "parent_{$headerName}";

        $this->details[$name] = [
            'table'      => $detailTable,
            'field'      => $linkField,
            'alias'      => $name,
            'conditions' => []
        ];

        return $this;
    }

    /**
     * Установить поле связи для табличной части.
     * Если $field = null, связь будет только по условиям (conditions).
     * @param string|MySqlTable $name Имя детали или сама таблица
     * @param string|null $field Имя поля (например, 'parent_orders')
     * @return $this
     */
    public function bindField($name, $field)
    {
        $detailName = $this->resolveDetailName($name);

        if (!isset($this->details[$detailName])) {
            throw new \InvalidArgumentException("Деталь '{$detailName}' не найдена");
        }

        $this->details[$detailName]['field'] = $field;
        return $this;
    }

    /**
     * Добавить условие отбора к табличной части
     * @param string|MySqlTable $name Имя детали или таблица
     * @param mixed $condition Условие: строка или массив
     * @return $this
     */
    public function bindCondition($name, $condition)
    {
        $detailName = $this->resolveDetailName($name);

        if (!isset($this->details[$detailName])) {
            throw new \InvalidArgumentException("Деталь '{$detailName}' не найдена");
        }

        $this->details[$detailName]['conditions'][] = $condition;
        return $this;
    }

    /**
     * Преобразует имя детали: если передана таблица — возвращает её псевдоним или имя
     * @param string|MySqlTable $name
     * @return string
     */
    private function resolveDetailName($name)
    {
        if (is_object($name)) {
            // Если это MySqlTable
            if ($name instanceof MySqlTable) {
                // Попробуем найти по имени
                foreach ($this->details as $alias => $detail) {
                    if ($detail['table'] === $name) {
                        return $alias;
                    }
                }
                // Если не нашли — используем имя таблицы
                return $name->name;
            } else {
                throw new \InvalidArgumentException("Неподдерживаемый тип объекта");
            }
        }

        return (string)$name;
    }

    // Геттеры
    public function getHeader() { return $this->header; }
    public function getDetails() { return $this->details; }
    public function getDetail($name) { return isset($this->details[$name]) ? $this->details[$name] : null; }

    // === Чтение данных ===
    /**
     * @return array|false ['header' => obj, 'details' => [name => [...]]] или false
     */

    /**
     * Получить документ по id шапки (базовая реализация)
     */
    public function get($headerId)
    {
        return $this->all($headerId);
    }

    /**
     * Получить документ с возможностью кастомизации условий
     * @param int $headerId
     * @param array $customConditions ['detailName' => ['FIELDS' => ..., 'ORDER' => ...]]
     * @return array|false
     */
    public function all($headerId, $customConditions = [])
    {
        // 1. Шапка
        $headerRecord = $this->header->get($headerId);
        if (!$headerRecord) return false;

        $result = ['header' => $headerRecord, 'details' => []];

        foreach ($this->details as $name => $detail) {
            $table = $detail['table'];

            // Создаём билдер
            $query = $table->query();

            // Применяем базовые условия
            foreach ($detail['conditions'] as $cond) {
                $query->where($cond);
            }

            // Применяем поле связи
            if ($detail['field'] !== null) {
                $query->where($detail['field'], $headerId);
            }

            // Накладываем кастомные условия (для этого вызова)
            if (isset($customConditions[$name])) {
                $custom = $customConditions[$name];
                if (is_array($custom)) {
                    foreach ($custom as $key => $value) {
                        switch (strtoupper($key)) {
                            case 'WHERE':
                                $query->where($value);
                                break;
                            case 'ORDER':
                            case 'GROUP':
                            case 'LIMIT':
                            case 'FIELDS':
                                $method = strtolower($key);
                                $query->$method($value);
                                break;
                            default:
                                $query->addCondition($key, $value, 'AND');
                        }
                    }
                } elseif (is_string($custom)) {
                    $query->where($custom);
                }
            }

            $result['details'][$name] = $query->all() ?: [];
        }

        return $result;
    }

    // === Удаление ===

    public function del($headerId)
    {
        $success = true;

        foreach ($this->details as $name => $detail) {
            $query = $detail['table']->query();
            $this->applyDetailConditions($query, $detail, $headerId);
            $deleted = $query->delall();
            $success = $success && $deleted;
        }

        $deletedHeader = $this->header->del($headerId);
        return $success && $deletedHeader;
    }

    private function applyDetailConditions($query, $detail, $headerId)
    {
        foreach ($detail['conditions'] as $cond) {
            $query->where($cond);
        }
        if ($detail['field'] !== null) {
            $query->where($detail['field'], $headerId);
        }
    }

    // === Сохранение ===

    /**
     * Сохранить документ и вернуть обогащённую структуру с id
     * @param array $data ['header' => [...], 'details' => [name => [...]]]
     * @param string $saveMode 'replace' | 'merge'
     * @return array|false ['header' => [...], 'details' => [...]] или false
     */
    public function save($data, $saveMode = 'replace')
    {
        if (!isset($data['header']) && !isset($data['headerid'])) return false;

        $detailsData = isset($data['details']) ? $data['details'] : [];

        // === 1. Сохраняем шапку ===
        if(isset($data['header'])) {
            $headerData = $data['header'];

            $this->header->freevalues();
            $headerId = null;

            foreach ($headerData as $key => $val) 
                if ($key === 'id') 
                    $headerId = $val;
                else 
                    $this->header->value($key, $val);

            if (empty($headerId)) {
                $savedId = $this->header->insert();
                if (!$savedId) return false;
                $headerId = $savedId;
            } 
            else 
                if (!$this->header->set((int)$headerId)) 
                    return false;
        }
        else
            $headerId = $data['headerid'];

        // Получаем актуальную запись с id
        $savedHeader = $this->header->get($headerId);
        if (!$savedHeader) return false;

        $result = ['header' => $savedHeader, 'details' => []];

        // === 2. Сохраняем детали ===
        foreach ($this->details as $name => $detail) {
            $table = $detail['table'];
            $linkField = $detail['field'];
            $rows = isset($detailsData[$name]) ? $detailsData[$name] : [];
            $savedRows = [];

            $passedIds = array_filter(array_column($rows, 'id'), 'is_numeric');

            // --- Удаление по стратегии ---
            if ($saveMode === 'replace' && $linkField !== null) {
                $condition = "`{$linkField}` = {$headerId}";
                if (!empty($passedIds)) {
                    $inClause = implode(',', $passedIds);
                    $condition .= " AND `id` NOT IN ({$inClause})";
                }
                $table->delall($condition);
            }

            // --- Сохранение строк ---
            foreach ($rows as $row) {
                if (isset($row['ltsDELETED']) && !empty($row['ltsDELETED'])) {
                    continue;
                }

                $table->freevalues();
                $rowId = !empty($row['id']) ? $row['id'] : null;

                foreach ($row as $key => $val) {
                    if ($key !== 'id') {
                        $table->value($key, $val);
                    }
                }

                if ($linkField !== null) {
                    $table->value($linkField, $headerId);
                }

                if (empty($rowId)) {
                    $newId = $table->insert();
                    if ($newId) {
                        $row['id'] = $newId;
                        $savedRows[] = $row;
                    }
                } else {
                    if ($table->set((int)$rowId)) {
                        $savedRows[] = $row;
                    }
                }
            }

            $result['details'][$name] = $savedRows;
        }

        return $result;
    }

    public function hasDetail($name)
    {
        $detailName = $this->resolveDetailName($name);
        return isset($this->details[$detailName]);
    }

    public function removeDetail($name)
    {
        $detailName = $this->resolveDetailName($name);
        unset($this->details[$detailName]);
        return $this;
    }

    /**
     * Получить QueryBuilder для табличной части, уже с условиями связи
     * @param string|MySqlTable $name
     * @return QueryBuilder
     */
    public function query($name)
    {
        $detailName = $this->resolveDetailName($name);
        $detail = $this->getDetail($detailName);

        if (!$detail) {
            throw new \InvalidArgumentException("Деталь '{$detailName}' не найдена");
        }

        $query = $detail['table']->query();

        // Применяем базовые условия
        foreach ($detail['conditions'] as $cond) {
            $query->where($cond);
        }

        // Поле связи пока не добавляем — пусть пользователь сам решит
        return $query;
    }
}
