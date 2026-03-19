<?php
namespace LTS;

Interface Ether
{
    public function __construct($id = '');
    // Предварительная обработка
    public function compile();
    // Создание элемента и его потомков и добавление во вселенную
    public function create($space, $parent = null);
    // Создание элемента
    public function shine($parent = null);
    // Добавление элемента во вселенную
    public function addinspace();
    // Добавление потомков элемента потомков элемента во вселенную
    public function childs();
}
?>
