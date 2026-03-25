<?php
if(! defined('UPPER_DIR'))
    define('UPPER_DIR', dirname(__DIR__) . '/newlotis/');
include_once UPPER_DIR . 'lotis.php';

$chart = LTS::SimpleChart()
    ->labels(['Пн', 'Вт', 'Ср', 'Чт', 'Пт'])
    ->dataset([120, 180, 150, 100, 300], ['label' => 'Продажи'])
    ->dataset([100, 200, 200, 100, 280], ['label' => 'План'])
    ->dataset([10, 14, 18, 1, 25], ['label' => 'Украли'])
    ->type('pie')
    ->attr('width', '800')   
    ->attr('height', '300'); 

$buttons = LTS::Div()
    ->rowbox()
    ->add(LTS::Button()->capt('Line')->click('LTS(chart).chart.setType("line")'))
    ->add(LTS::Button()->capt('Bar')->click('LTS(chart).chart.setType("bar")'))
    ->add(LTS::Button()->capt('Pie')->click('LTS(chart).chart.setType("pie")'))
    ->add(LTS::Button()->capt('Stacked-pie')->click('LTS(chart).chart.setType("stacked-pie")'));

$content = LTS::Div()
    ->columnbox()
    ->add($chart)
    ->add($buttons);

LTS::Space()->build($content);
?>