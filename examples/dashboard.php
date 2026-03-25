<?php
define('UPPER_DIR', dirname(__DIR__) . '/newlotis/');
include_once UPPER_DIR . 'lotis.php';

// 1. Хранилище данных
$vars = LTS::Vars('dashboard');
if (!$vars->value('revenue')) {
    $vars->value('revenue', 1250000);
    $vars->value('tasks', 12);
    $vars->value('temp', 23);
    $vars->value('usd', 95.50);
    $vars->value('eur', 103.20);
}

// 2. События
$events = LTS::Events('dashboard');

// Событие: обновить все данные
$events->server('refreshData', function($args) {
    // Имитация получения данных
    $data = [
        'revenue' => 1250000 + rand(-50000, 50000),
        'tasks'   => rand(5, 20),
        'temp'    => 20 + rand(-5, 10),
        'usd'     => 95.50 + (rand(-100, 100) / 100),
        'eur'     => 103.20 + (rand(-100, 100) / 100)
    ];
    return $data;
});

// Клиентский обработчик: обновить отображение
$events->client('refreshData', 
<<<JS
    // Обновляем Vars
    LTS.vars("dashboard", result);
    
    // Обновляем отображение
    $(revenue).text(result.revenue.toLocaleString() + " ₽");
    $(tasks).text(result.tasks);
    $(temp).text(result.temp + "°");
    $(usd).text(result.usd.toFixed(2));
    $(eur).text(result.eur.toFixed(2));
    
    // Меняем цвета
    if (result.tasks > 15) $(tasks).css("color", "red");
    else if (result.tasks < 5) $(tasks).css("color", "orange");
    else $(tasks).css("color", "green");
JS
);

LTS::Events()->build();

// 3. Интерфейс — Grid с адаптивностью
$grid = LTS::Grid('dashboard-grid')
    ->deviceQuery('watch', '() => window.innerWidth <= 400 && window.innerHeight <= 400')
    ->priority('watch, mobile, desktop'); // Приоритет устройств

// --- Режим: default (десктоп/планшет) ---
$grid->setMode('default')
     ->device('desktop')
        ->areas([
            "revenue tasks",
            "weather currency"
        ])
        ->columns('1fr 1fr')
        ->rows('auto auto')
     ->device('mobile')
        ->areas([
            "revenue",
            "tasks",
            "weather",
            "currency"
        ])
        ->columns('1fr')
        ->rows('auto auto auto auto')
     ->device('watch')
        ->areas([
            "revenue",
            "tasks"
        ])
        ->columns('1fr')
        ->rows('1fr 1fr')
    ->gap('15px')
    ->defaultMode('default');

// Карточка: Выручка
$revenueArea = $grid->area('revenue')->addclass('card')
    ->add(LTS::Div()->capt('Выручка')->addclass('title'))
    ->add(LTS::Div('revenue')->capt($vars->value('revenue') . ' ₽')->addclass('value'));

// Карточка: Задачи
$tasksArea = $grid->area('tasks')->addclass('card')
    ->add(LTS::Div()->capt('Задачи')->addclass('title'))
    ->add(LTS::Div('tasks')->capt($vars->value('tasks'))->addclass('value'));

// Карточка: Погода
$weatherArea = $grid->area('weather')->addclass('card')
    ->add(LTS::Div()->capt('Погода')->addclass('title'))
    ->add(LTS::Div('temp')->capt($vars->value('temp') . '°')->addclass('value'));

// Карточка: Курсы валют
$currencyArea = $grid->area('currency')->addclass('card')
    ->add(LTS::Div()->capt('Курсы валют')->addclass('title'))
    ->add(LTS::Div()->add(
        LTS::Div()->capt('USD: ')->add(LTS::Span('usd')->capt($vars->value('usd')))
    ))
    ->add(LTS::Div()->add(
        LTS::Div()->capt('EUR: ')->add(LTS::Span('eur')->capt($vars->value('eur')))
    ));

// Кнопка обновления
$refreshBtn = LTS::Button()
    ->capt('🔄 Обновить данные')
    ->click('LTS(events).refreshData()');

// Собираем страницу
$page = LTS::Div('dashboard-page')
    ->addclass('container')
    ->add($vars)
    ->add($events)
    ->add(LTS::Div()->capt('Умная панель управления')->addclass('title'))
    ->add($grid)
    ->add($refreshBtn);

// Подключаем CSS:
$page->css()->add('dashboard.css');

// Автообновление каждые 30 сек
$page->js('ready')->add(
<<<JS
    setInterval(() => {
        LTS(events).refreshData();
    }, 30000);
JS
);

// Строим страницу
LTS::Space()->build($page);
?>