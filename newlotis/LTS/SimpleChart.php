<?php
namespace LTS;

class SimpleChart extends Element
{
    private $config = [
        'type' => 'line',
        'data' => ['labels' => [], 'datasets' => []],
        'options' => []
    ];

    public function __construct($id = '')
    {
        parent::__construct($id);
        $this->tagname = 'canvas'; 
    }

    // type: line, bar, pie, doughnut
    public function type($type) { $this->config['type'] = $type; return $this; }
    public function label($label) { $this->config['data']['labels'][] = $label; return $this; }
    public function labels($array)
    {
        $this->config['data']['labels'] = array_values($array);
        return $this;
    }

    /**
     * Генерирует числовые метки от min до max с заданным шагом.
     */
    public function autolabels($min, $max, $step = 1)
    {
       $labels = [];
        // Используем >= или <= в зависимости от направления шага
        if ($step > 0) {
            for ($i = $min; $i <= $max; $i += $step) {
                $labels[] = (string)$i;
            }
        } else {
            for ($i = $min; $i >= $max; $i += $step) {
                $labels[] = (string)$i;
            }
        }

        return $this->labels($labels);
    }

    /**
     * Добавляет набор данных.
     * Принимает:
     *   - числовой массив: [10, 20, 30]
     *   - ассоциативный массив: ['Пн' => 10, 'Вт' => 20]
     *     (в этом случае автоматически дополнит нулями по сравнению с labels)
     */
    public function dataset($data, $options = [])
    {
        // Если передан ассоциативный массив
        if (is_array($data) && array_keys($data) !== range(0, count($data) - 1)) {
            $indexedData = [];
            foreach ($this->config['data']['labels'] as $label) {
                $indexedData[] = array_key_exists($label, $data) ? $data[$label] : 0;
            }
            $values = $indexedData;
        } else {
            $values = is_array($data) ? $data : [$data];
        }

        $dataset = [
            'data' => $values,
            'borderWidth' => !empty($options['borderWidth']) ? $options['borderWidth'] : 1
        ];

        // Передаём только те опции, которые задал пользователь
        // Цвета будут сгенерированы на клиенте, если не заданы
        $this->config['data']['datasets'][] = array_merge($dataset, $options);
        return $this;
    }

    public function compile()
    {
        $id = $this->id;
        $jsonConfig = json_encode($this->config, JSON_UNESCAPED_UNICODE);

        $js = new JS('ready');
        $this->add($js);
        $js->compile = false;
        $js->add(
<<<JS
LTS.get('{$id}').chart = {
    render: () => ltsSimpleChart.create('{$id}', {$jsonConfig}),
    update: (data) => ltsSimpleChart.update('{$id}', data),
    setType: (type) => ltsSimpleChart.setType('{$id}', type),
    addDataset: (dataset) => ltsSimpleChart.addDataset('{$id}', dataset),
    destroy: () => ltsSimpleChart.destroy('{$id}')
};
LTS.get('{$id}').chart.render();
JS
        );

        parent::compile();
    }
}
?>