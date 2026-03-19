<?php
namespace LTS;

class LayerSlider extends Div
{
    private $layers = [];     // Массив id потомков
    private $captions = [];   // Массив заголовков слоёв
    private $loop = true;     // Зацикливать ли слои
    private $swipeSensitivity = 50; // Минимальное смещение для свайпа
    private $displayStyle = 'block'; // Стиль отображения активного слоя
    private $activeLayer = 1; // Активный слой: объект, id или номер (1..n)

    public function __construct($id = '')
    {
        parent::__construct($id);
        $this->addclass('LayerSlider');
        $this->css()->add('position: relative; overflow: hidden;');
    }

    /**
     * Добавляет слой
     */
    public function add($name, $value = null)
    {
        $result = parent::add($name, $value);

        $child = is_string($name) ? $value : $name;
        if (
            is_object($child) && 
            property_exists($child, 'id') && 
            property_exists($child, 'type') && 
            $child->type == 'html'
        ) {
            $child->addclass('Layer');
            $this->layers[] = $child->id;
            $this->captions[] = $child->caption ?: "Слой " . count($this->layers);
        }

        return $result;
    }

    /**
     * Устанавливает изначально активный слой (объект, id или номер с 1)
     */
    public function active($child)
    {
        if (is_object($child) && property_exists($child, 'id')) {
            $this->activeLayer = $child->id;
        } else {
            $this->activeLayer = $child;
        }
         
        return $this;
    }

    /**
     * Включить/выключить зацикливание
     */
    public function loop($enable = true)
    {
        $this->loop = $enable;
        return $this;
    }

    /**
     * Установить чувствительность свайпа
     */
    public function sensitivity($px)
    {
        $this->swipeSensitivity = $px;
        return $this;
    }

    /**
     * Установить стиль отображения активного слоя
     */
    public function setdisplay($style = 'block')
    {
        $this->displayStyle = in_array($style, ['block', 'flex', 'inline-block', 'grid']) ? $style : 'block';
        return $this;
    }

    /**
     * Создать индикатор слоёв
     */
    public function createIndicator($css = null)
    {
        $indicatorId = $this->id . '_indicator'; // Уникальный ID
        $indicator = LTS::Div($indicatorId); // Передаём id в конструктор
        $indicator->addclass('layer-indicator');
        $indicator->css()->add($css ?: 'display: flex; justify-content: center; gap: 8px; margin: 10px 0;');

        foreach ($this->captions as $index => $caption) {
            $dot = LTS::Div();
            $dot->addclass('layer-dot');
            $dot->css()->add("
                width: 10px;
                height: 10px;
                border-radius: 50%;
                background: #ccc;
                cursor: pointer;
                transition: background 0.3s;
            ");
            $dot->attr('data-index', $index);
            $dot->click("LTS('{$this->id}').goto({$index});");
            $indicator->add($dot);
        }

        // Генерация JS: обновляем только точки внутри #{$indicatorId}
        $this->js('ready')->add(
<<<JS
    _obj = LTS.get('{$this->id}');
    _obj.updateIndicator = function() {
        const _indicator = jQuery('#{$indicatorId}');
        if (_indicator.length) {
            _indicator.find('.layer-dot').css('background', '#ccc');
            _indicator.find('.layer-dot').eq(_obj.currentIndex).css('background', '#333');
        }
    };
    if (_obj.updateIndicator) _obj.updateIndicator();
JS
        );

        return $indicator;
    }

    public function compile()
    {
        // Определяем индекс активного слоя
        $activeIndex = 0;

        if (is_numeric($this->activeLayer)) {
            // Номер слоя (начинается с 1)
            $index = (int)$this->activeLayer - 1;
            if ($index >= 0 && $index < count($this->layers)) {
                $activeIndex = $index;
            }
        } elseif (is_string($this->activeLayer)) {
            // ID слоя
            $index = array_search($this->activeLayer, $this->layers);
            if ($index !== false) {
                $activeIndex = $index;
            }
        }

        // Настраиваем CSS для всех слоёв
        foreach ($this->layers as $i => $layerId) {
            $child = $this->child($layerId);
            if ($child !== false) {
                if ($i === $activeIndex) {
                    $child->css()->add("display: {$this->displayStyle}; position: absolute; top: 0; left: 0; width: 100%;");
                } else {
                    $child->css()->add('display: none; position: absolute; top: 0; left: 0; width: 100%;');
                }
            }
        }

        $loop = $this->loop ? 'true' : 'false';

        $jsready = new JS('ready');
        $jaready->compile = false;
        $this->add($jsready);

        // Генерация JavaScript
        $jsready->add(
" (function () {
        let _obj = LTS.get('{$this->id}');
        _obj.layers = [" . implode(', ', array_map(fn($id) => "'{$id}'", $this->layers)) . "];
        _obj.captions = [" . implode(', ', array_map(fn($cap) => "'" . addslashes($cap) . "'", $this->captions)) . "];
        _obj.currentIndex = {$activeIndex};
        _obj.loop = {$loop};
        _obj.displayStyle = '{$this->displayStyle}';
        _obj.swipeSensitivity = {$this->swipeSensitivity};
        _obj.swipeStartX = 0;
        _obj.swipeStartY = 0;
        _obj.isSwiping = false;
    })();");

// Методы
    $js = $this->compilemethod('next',
<<<JS
    const prevIndex = this.currentIndex;
    this.currentIndex = this.loop 
        ? (this.currentIndex + 1) % this.layers.length 
        : Math.min(this.currentIndex + 1, this.layers.length - 1);
    if (prevIndex !== this.currentIndex) {
        this._fadeTransition(prevIndex, this.currentIndex);
        if (this.updateIndicator) this.updateIndicator();
    }
JS
        );
    $this->compilemethod('prev',
<<<JS
    const prevIndex = this.currentIndex;
    this.currentIndex = this.loop 
        ? (this.currentIndex - 1 + this.layers.length) % this.layers.length 
        : Math.max(this.currentIndex - 1, 0);
    if (prevIndex !== this.currentIndex) {
        this._fadeTransition(prevIndex, this.currentIndex);
        if (this.updateIndicator) this.updateIndicator();
    }
JS
        , $js);
    $this->compilemethod('goto(index)',
<<<JS
    if (index >= 0 && index < this.layers.length && index !== this.currentIndex) {
        const prevIndex = this.currentIndex;
        this.currentIndex = index;
        this._fadeTransition(prevIndex, this.currentIndex);
        if (this.updateIndicator) this.updateIndicator();
    }
JS
        , $js);
    $this->compilemethod('_fadeTransition(fromIndex, toIndex)',
<<<JS
    const _from = jQuery('#' + this.layers[fromIndex]);
    const _to = jQuery('#' + this.layers[toIndex]);
    _to.css('display', this.displayStyle).css('opacity', 0);
    _from.animate({ opacity: 0 }, 300, function() { jQuery(this).css('display', 'none'); });
    _to.animate({ opacity: 1 }, 300);
JS
        , $js);

    $jsready->add(
<<<JS
        jQuery('#{$this->id}').on('mousedown touchstart', function(e) {
            const event = e.originalEvent;
            _obj.swipeStartX = event.touches ? event.touches[0].clientX : e.clientX;
            _obj.swipeStartY = event.touches ? event.touches[0].clientY : e.clientY;
            _obj.isSwiping = true;
        });

        jQuery('#{$this->id}').on('mouseup touchend', function(e) {
            if (!_obj.isSwiping) return;
            const event = e.originalEvent;
            const endX = event.changedTouches ? event.changedTouches[0].clientX : e.clientX;
            const endY = event.changedTouches ? event.changedTouches[0].clientY : e.clientY;
            const deltaX = _obj.swipeStartX - endX;
            const deltaY = Math.abs(_obj.swipeStartY - endY);

            if (Math.abs(deltaX) > _obj.swipeSensitivity && deltaY < 50) {
                if (deltaX > 0) {
                    _obj.next();
                } else {
                    _obj.prev();
                }
            }
            _obj.isSwiping = false;
        });

        jQuery('#{$this->id}').on('selectstart', false);
JS
            );

        parent::compile();
    }
}