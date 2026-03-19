<?php
namespace LTS;

class Grid extends Div
{
    private $modes = [];          // [mode][device] = config
    private $currentDevice = 'desktop'; 
    private $activeMode = null;
    private $devices;
    private $priorityOrder = [];
    private $defaultmode;

    public function __construct($id = '')
    {
        parent::__construct($id);
        $this->display('grid');
        $this->devices = [
            'mobile' => '() => window.matchMedia("(max-width: 768px)").matches',
            'desktop' => '() => !window.matchMedia("(max-width: 768px)").matches'
        ];
        $this->priority('mobile, desktop');
    }

    public function deviceQuery($name, $jsFunction)
    {
        $this->devices[$name] = $jsFunction;
        return $this;
    }

    public function priority($orderString)
    {
        $names = array_map('trim', explode(',', $orderString));
        foreach ($names as $index => $name) {
            if (isset($this->devices[$name])) {
                $this->priorityOrder[$name] = $index;
            }
        }
        return $this;
    }

    public function setMode($name)
    {
        $this->activeMode = $name;
        $this->currentDevice = 'desktop';
        return $this;
    }

    public function defaultMode($mode)
    {
        $this->defaultmode = $mode;
        return $this;
    }

    public function device($device)
    {
        $this->currentDevice = $device;
        return $this;
    }

    public function areas(array $areas)
    {
        $this->modes[$this->activeMode][$this->currentDevice]['areas'] = $areas;
        return $this;
    }

    public function rows($rows)
    {
        $this->modes[$this->activeMode][$this->currentDevice]['rows'] = $rows;
        return $this;
    }

    public function columns($columns)
    {
        $this->modes[$this->activeMode][$this->currentDevice]['columns'] = $columns;
        return $this;
    }

    public function area($name)
    {
        $areaid = "{$this->id}_area_{$name}";
        $wrapper = $this->child($areaid);
        if($wrapper === false) {
            $wrapper = (new Div($areaid))
                ->addclass('GridWrapper')
                ->attr('data-grid-area', $name);
            $this->add($name, $wrapper);
        }
        return $wrapper;
    }

    public function add($name, $value = null)
    {
        if($value !== null && is_object($value) && method_exists($value, 'gridarea'))
        {
            $value->gridarea($name);
            return parent::add($value);    
        }

        return parent::add($name, $value);
    }

    public function to($name, ...$elements)
    {
        foreach ($elements as $el)
            $this->add($name, $el);

        return $this;
    }

    /**
     * Выравнивание контейнера по осям (аналог justify-items)
     * 
     * @param string $justify start | end | center | stretch
     * @return $this
     */
    public function justify($justify)
    {
        switch ($justify) {
            case 'start':
            case 'beg':
            case 'begin':
                $this->css()->add('justify-items', 'start');
                break;
            case 'end':
                $this->css()->add('justify-items', 'end');
                break;
            case 'center':
                $this->css()->add('justify-items', 'center');
                break;
            case 'stretch':
                $this->css()->add('justify-items', 'stretch');
                break;
        }
        return $this;
    }

    /**
     * Установить автоматическое размещение элементов
     * 
     * @param string $flow row | column | row dense | column dense
     * @return $this
     */
    public function flow($flow)
    {
        $this->css()->add('grid-auto-flow', $flow);
        return $this;
    }

    /**
     * Задать размеры автоматических строк
     * 
     * @param string $size Пример: '100px', 'minmax(50px, auto)'
     * @return $this
     */
    public function autoRows($size)
    {
        $this->css()->add('grid-auto-rows', $size);
        return $this;
    }

    /**
     * Задать размеры автоматических колонок
     * 
     * @param string $size Пример: '100px', 'minmax(50px, auto)'
     * @return $this
     */
    public function autoColumns($size)
    {
        $this->css()->add('grid-auto-columns', $size);
        return $this;
    }

    /**
     * Центрирование: по осям, по контейнеру или оба
     * 
     * @param string $target 'items' | 'container' | 'both'
     * @return $this
     */
    public function center($target = 'items')
    {
        if ($target === 'items' || $target === 'both') {
            $this->css()->add('justify-items', 'center');
            $this->css()->add('align-items', 'center');
        }

        if ($target === 'container' || $target === 'both') {
            $this->css()->add('justify-content', 'center');
            $this->css()->add('align-content', 'center');
        }

        return $this;
    }

    /**
     * Центрирование сетки в родительском контейнере
     * 
     * @return $this
     */
    public function centerInParent()
    {
        $this->css()->add('margin', '0 auto');
        return $this;
    }

    /**
     * Готовый шаблон: адаптивная сетка карточек
     * 
     * @param string $minWidth Минимальная ширина карточки, например '250px'
     * @return $this
     */
    public function cards($minWidth = '250px')
    {
        $this->css()->add('grid-template-columns', "repeat(auto-fit, minmax({$minWidth}, 1fr))");
        $this->css()->add('gap', '10px');
        return $this;
    }

    /**
     * Готовый шаблон: макет приложения
     * 
     * Структура:
     * header  header
     * menu    content
     * bar     bar
     * 
     * @return $this
     */
    public function layout()
    {
        return $this-setMode('default')
            ->areas([
                "header header",
                "menu   content",
                "bar    bar"
            ])
            ->rows("auto 1fr auto")
            ->columns("200px 1fr")
            ->gap("10px");
    }

    /**
     * Проверяет, заполняется ли сетка по строкам (по умолчанию)
     * @return bool
     */
    public function isrow()
    {
        $flow = $this->css()->get('grid-auto-flow');
        return $flow === null || 
            strpos($flow, 'row') !== false;
    }

    /**
     * Проверяет, заполняется ли сетка по колонкам
     * @return bool
     */
    public function iscolumn()
    {
        $flow = $this->css()->get('grid-auto-flow');
        return $flow !== null && 
            strpos($flow, 'column') !== false;
    }

    public function compile()
    {
        $id = $this->id;

        $modes = json_encode($this->modes, JSON_UNESCAPED_UNICODE);
        $priorityOrder = json_encode($this->priorityOrder);
        $devicesJs = '';
        foreach ($this->devices as $name => $funcStr) {
            $safeFuncStr = str_replace("'", "\\'", $funcStr);
            $devicesJs .= (empty($devicesJs) ? '' : ',') . "'{$name}': {$safeFuncStr}";
        }
        $devicesJs = "{{$devicesJs}}";

        $js = $this->compilemethod("ltsGrid.init('{$id}', {$modes}, {$devicesJs}, {$priorityOrder})");
        $this->compilemethod('setMode(mode)', "ltsGrid.setMode('{$id}', mode); return this", $js);
        $this->compilemethod('deviceQuery(device, func)', "ltsGrid.deviceQuery('{$id}', device, func); return this", $js);
        $this->compilemethod('mode(name)', "ltsGrid.mode('{$id}', name); return this", $js);
        $this->compilemethod('priority(order)', "ltsGrid.priority('{$id}', order); return this", $js);
        $this->compilemethod('grid', "return ltsGrid.grid('{$id}')", $js);
        $this->compilemethod('check(f)', "ltsGrid.check('{$id}', f); return this", $js);
        $this->compilemethod('before(f)', "ltsGrid.before('{$id}', f); return this", $js);
        $this->compilemethod('on(f)', "ltsGrid.on('{$id}', f); return this", $js);

        if(! empty($this->defaultmode))
            $this->js('ready')->add("LTS({$this->id}).mode('{$this->defaultmode}')");

        parent::compile();
    }
}
?>
