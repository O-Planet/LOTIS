<?php
/*
    Пересчет суммы по курсу валют центробанка
*/

define('UPPER_DIR', dirname(__DIR__) . '/newlotis/');
include_once UPPER_DIR . 'lotis.php';

function getCbrRates() {
    $url = 'https://www.cbr-xml-daily.ru/daily_json.js';
    $response = file_get_contents($url);
    return json_decode($response, true);
}

// Дополнительные классы
class Option extends LTS\Element
{
    public function __construct($id = '')
    {
        parent::__construct($id);
        $this->tag('option');
    }

    public function inic($val, $caption)
    {
        $this->attr('value', $val)
            ->capt($caption);
        return $this;
    }
}

class Select extends LTS\Element
{
    public function __construct($id = '')
    {
        parent::__construct($id);
        $this->tag('select');
    }

    public function option($val, $caption = null)
    {
        $option = new Option();
        $option->inic($val, $caption ?? $val);
        $this->add($val, $option);
        return $this;
    }
}

// ========================
// Читаем курсы валют
$res = getCbrRates();

// Окно программы
$maindiv = LTS::Div('converter')
    ->columnbox()
    ->gap('10px');

// Два списка для пересчета валют
$selectfrom = new Select();
$selectto = new Select();
$rates = [];

// Заполняем списки валютами
foreach($res['Valute'] as $name => $val)
{
    $caption = $val['Name'];
    $curs = $val['Value'];
    $selectfrom->option($name, $caption);
    $selectto->option($name, $caption);
    $rates[$name] = $curs;
}

// Поле для ввода суммы
$input = LTS::Input()->number('total')->attr('placeholder', 'Введите сумму');

// Поле для вывода результата
$result = LTS::Element()->capt('0');

// Собираем все в окнопрограммы
$maindiv->add($selectfrom)
    ->add($input)
    ->add($selectto)
    ->add($result)
        ->css()->add("converter.css");

// Функция пересчетиа        
$maindiv->js()
    ->add('const rates = ' . json_encode($rates) . ';')
    ->add('recount()', 
<<<JS
    // Получаем выбранные валюты
    const val1 = $(selectfrom).val();
    const val2 = $(selectto).val();
    // Получаем курсы
    const curs1 = rates[val1];
    const curs2 = rates[val2];
    // Пересчитываем сумму
    const summa = $(input).val();
    let newsumma = (curs2 == 0 ? 0 : summa * curs1 / curs2).toFixed(4);
    // Выводим результат
    $(result).text(newsumma);
JS
);

// Назначаем обработчики пересчета при изменении выбранной валюты или введенной суммы
$selectfrom->on('change', 'recount()');
$selectto->on('change', 'recount()');
$input->on('change', 'recount()');

// Строим программу
LTS::Space()->build($maindiv);
?>
