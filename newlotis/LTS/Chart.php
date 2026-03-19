<?php
namespace LTS;

class Chart extends Element
{
    private $config = [
        'type' => 'line',
        'data' => ['labels' => [], 'datasets' => []],
        'options' => [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'scales' => [
                'xAxes' => [['ticks' => ['autoSkip' => true]]],
                'yAxes' => [['ticks' => ['beginAtZero' => true]]]
            ]
        ]
    ];

    public function __construct($id = '')
    {
        parent::__construct($id);
        $this->tagname = 'canvas';
    }

    /**
     * Устанавливает тип графика
     */
    public function type($type)
    {
        $this->config['type'] = $type;
        return $this;
    }

    /**
     * Добавляет метку для оси X
     */
    public function label($label)
    {
        $this->config['data']['labels'][] = $label;
        return $this;
    }

    public function labels($array)
    {
        foreach($array as $l)
            $this->label($l);
        return $this;
    }


    /**
     * Генерирует числовые метки от min до max с заданным шагом.
     */
    public function autolabels($min, $max, $step = 1)
    {
        // Используем >= или <= в зависимости от направления шага
        if ($step > 0) {
            for ($i = $min; $i <= $max; $i += $step) {
                $this->label((string)$i);
            }
        } else {
            for ($i = $min; $i >= $max; $i += $step) {
                $this->label((string)$i);
            }
        }

        return $this;
    }

    private function generateColor($index)
    {
        $hue = ($index * 137.508) % 360; // Используем "золотой угол" для максимальной разницы
        $saturation = 70 + ($index % 4) * 5; // Варьируем насыщенность
        $lightness = 55 + (($index / 3) % 3) * 10; // Варьируем светлоту

        return $this->hslToRgba($hue, $saturation, $lightness);
    }

    // Конвертация HSL в RGBA строку
    private function hslToRgba($h, $s, $l, $a = 0.7)
    {
        $h /= 360;
        $s /= 100;
        $l /= 100;

        $r = $l;
        $g = $l;
        $b = $l;
        if ($s != 0) {
            $q = $l < 0.5 ? $l * (1 + $s) : $l + $s - $l * $s;
            $p = 2 * $l - $q;
            $r = $this->hue2rgb($p, $q, $h + 1/3);
            $g = $this->hue2rgb($p, $q, $h);
            $b = $this->hue2rgb($p, $q, $h - 1/3);
        }

        return "rgba(" . round($r*255) . "," . round($g*255) . "," . round($b*255) . ",{$a})";
    }

    private function hue2rgb($p, $q, $t)
    {
        if ($t < 0) $t += 1;
        if ($t > 1) $t -= 1;
        if ($t < 1/6) return $p + ($q - $p) * 6 * $t;
        if ($t < 1/2) return $q;
        if ($t < 2/3) return $p + ($q - $p) * (2/3 - $t) * 6;
        return $p;
    }

    /**
     * Добавляет набор данных
     */
    public function dataset($data, $options = [])
    {
        $backgroundColor = $options['backgroundColor'] ?? $this->generateColor(count($this->config['data']['datasets']));
        $borderColor = $options['borderColor'] ?? str_replace('0.7', '1', $backgroundColor);

        $dataset = [
            'data' => is_array($data) ? $data : [$data],
            'backgroundColor' => $backgroundColor,
            'borderColor' => $borderColor,
            'borderWidth' => $options['borderWidth'] ?? 1
        ];

        $this->config['data']['datasets'][] = array_merge($dataset, $options);
        return $this;
    }

    /**
     * Устанавливает опции графика
     */
    public function options($options)
    {
        $this->config['options'] = array_replace_recursive($this->config['options'], $options);
        return $this;
    }

    /**
     * Включает/отключает сетку
     */
    public function grid($show = true)
    {
        $this->config['options']['scales']['yAxes'][0]['gridLines']['display'] = (bool)$show;
        return $this;
    }

    /**
     * Включает/отключает легенду
     */
    public function legend($position = 'top')
    {
        $this->config['options']['legend'] = [
            'display' => true,
            'position' => $position
        ];
        return $this;
    }

    /**
     * Устанавливает заголовок графика
     */
    public function title($text, $size = 16)
    {
        $this->config['options']['title'] = [
            'display' => true,
            'text' => $text,
            'fontSize' => $size
        ];
        return $this;
    }

    /**
     * Настраивает масштабирование оси Y
     */
    public function yscale($min = null, $max = null)
    {
        $ticks = [];
        if ($min !== null) $ticks['suggestedMin'] = $min;
        if ($max !== null) $ticks['suggestedMax'] = $max;
        
        $this->config['options']['scales']['yAxes'][0]['ticks'] = array_merge(
            $this->config['options']['scales']['yAxes'][0]['ticks'] ?? [],
            $ticks
        );
        return $this;
    }

    /**
     * Включает анимацию
     */
    public function animate($duration = 1000)
    {
        $this->config['options']['animation'] = ['duration' => $duration];
        return $this;
    }


    /**
     * Компилирует объект
     */
    public function compile()
    {
        static $scriptChartAdded = false;
        if (!$scriptChartAdded) {
            // Подключаем Chart.js
            $this->space->metadata[] = [
                'type' => 'script',
                'src' => 'https://cdn.jsdelivr.net/npm/chart.js'
            ];
            $scriptChartAdded = true;
        }

        $id = $this->id;
        $jsonConfig = json_encode($this->config, JSON_UNESCAPED_UNICODE);

        $js = new JS();
        $js->area = 'ready';
        $js->compile = false;
        $js->add(
<<<JS
_obj = LTS.get('{$id}');
// Создаём экземпляр ltsChart с ID этого элемента
_obj.chart = new ltsChart('{$id}');

// Дублируем методы ltsChart в _obj, чтобы можно было вызывать LTS(id).method()
_obj.type = function(type) { this.chart.type(type); return this; };
_obj.label = function(label) { this.chart.label(label); return this; };
_obj.dataset = function(data, options) { this.chart.dataset(data, options); return this; };
_obj.options = function(opts) { this.chart.options(opts); return this; };
_obj.grid = function(show = true) { this.chart.grid(show); return this; };
_obj.title = function(text, size = 16) { this.chart.title(text, size); return this; };
_obj.yscale = function(min = null, max = null) { this.chart.yscale(min, max); return this; };
_obj.updateData = function(data) { this.chart.updateData(data); return this; };
_obj.render = function() { this.chart.render(); return this; };

// Передаём конфигурацию и отрисовываем график
_obj.chart.type('{$this->config['type']}');
JS
        );

        // Добавляем все метки
        foreach ($this->config['data']['labels'] as $label) {
            $js->add("_obj.chart.label('{$label}');");
        }

        // Добавляем все наборы данных
        foreach ($this->config['data']['datasets'] as $dataset) {
            $jsDataset = json_encode($dataset['data']);
            $jsOptions = json_encode(array_diff_key($dataset, ['data' => null]));
            $js->add("_obj.chart.dataset({$jsDataset}, {$jsOptions});");
        }

        // Применяем опции
        $js->add("_obj.chart.options(" . json_encode($this->config['options'], JSON_UNESCAPED_UNICODE) . ");");

        // Отрисовываем
        $js->add("_obj.chart.render();");

        $this->add($js);
        parent::compile();
    }
}
?>