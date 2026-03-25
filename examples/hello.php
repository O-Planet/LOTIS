<?php
    define('UPPER_DIR', dirname(__DIR__) . '/newlotis/');
    include_once UPPER_DIR . 'lotis.php';
    
    $div = LTS::Div()->capt('<h2>Hello, world!</h2>');
    
    LTS::Space()->build($div);
?>