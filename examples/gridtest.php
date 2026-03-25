<?php
// Подключаем фреймворк
if (!defined('UPPER_DIR')) {
    define('UPPER_DIR', dirname(__DIR__) . '/newlotis/');
}
include_once UPPER_DIR . 'lotis.php';

// Создаем основной контейнер
$page = LTS::Div('grid-demo')
    ->addclass('container');

// === Сам Grid ===
$grid = LTS::Grid('main-grid');

// --- Определяем дополнительное устройство ---
$grid->deviceQuery('watch', '() => window.innerWidth <= 600 && window.innerHeight <= 600');

// Режим: dashboard
$grid->setMode('dashboard')
     ->device('desktop')
        ->areas(["header header", "menu content", "bar bar"])
        ->rows("60px 1fr auto")
        ->columns("200px 1fr")
     ->device('mobile')
        ->areas(["header", "content", "bar"])
        ->rows("60px 1fr auto")
        ->columns("1fr")
     ->device('watch')
        ->areas(["header", "content"])
        ->rows("50px 1fr")
        ->columns("1fr");

// Режим: editor
$grid->setMode('editor')
     ->device('desktop')
        ->areas(["header header", "form form", "actions actions"])
        ->rows("60px 1fr auto")
        ->columns("1fr")
     ->device('mobile')
        ->areas(["header", "form", "actions"])
        ->rows("60px 1fr auto")
        ->columns("1fr")
     ->device('watch')
        ->areas(["header", "form"])
        ->rows("50px 1fr")
        ->columns("1fr");

// --- Приоритеты режимов и режим по умолчанию ---   
$grid->defaultmode('dashboard')
    ->priority('watch,mobile,desktop');

// --- Врапперы для областей ---
$header = $grid->area('header')->capt('🌐 Демо Grid')->addclass('header');
$menu = $grid->area('menu')
    ->add(LTS::Div()->addclass('menu-item')->capt('🏠 Главная'))
    ->add(LTS::Div()->addclass('menu-item')->capt('📊 Отчеты'))
    ->add(LTS::Div()->addclass('menu-item')->capt('⚙️ Настройки'));
$content = $grid->area('content')->capt('Контент загружается...')->addclass('content');
$bar = $grid->area('bar')->capt('Готов к работе')->addclass('status-bar');
$form = $grid->area('form')->capt('Форма редактирования')->addclass('form'); 
$actions = $grid->area('actions')->capt('Кнопки действий')->addclass('actions'); 

// --- Кнопки управления ---
$btnDashboard = LTS::Button()
    ->capt('📊 Панель')
    ->click("LTS(grid).mode('dashboard')");

$btnEditor = LTS::Button()
    ->capt('✏️ Редактор')
    ->click("LTS(grid).mode('editor')");

$btnRefresh = LTS::Button()
    ->capt('🔄 Обновить')
    ->click("$(content).text('✅ Контент обновлен: ' + new Date().toLocaleTimeString());");

$buttons = LTS::Div()
    ->addmany($btnDashboard, $btnEditor, $btnRefresh)
    ->flex()
    ->gap('10px');

// --- Сборка интерфейса ---
$page->addmany(
    LTS::Div()->capt('Тест Grid')->addclass('title'),
    $grid,
    $buttons
);

// --- CSS стили ---
$page->css()->add('test-grid.css');

// --- Сборка страницы ---
LTS::Space()->build($page);
?>