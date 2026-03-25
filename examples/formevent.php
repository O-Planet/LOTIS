<?php 
ini_set('display_errors',1);
error_reporting(E_ALL);
define('UPPER_DIR', dirname(__DIR__) . '/newlotis/');
include_once UPPER_DIR . 'lotis.php';

// Login Form
$form = LTS::Form();

$form->text('login')->attr('placeholder', 'Логин')->attr('required', '1');
$form->password('password')->attr('placeholder', 'Пароль')->attr('required', '1');
$loginbutton = $form->button('start', 'Авторизация');

$form->event($loginbutton, 
<<<JS
    if(typeof result == 'string')
        alert(result);
    else
        alert('Hello!');
JS
)
    ->event($loginbutton, function ($args) { 
        $login = $args['login'];
        $password = $args['password'];

        if($login == 'BRED PITT' && $password == '123')
            return true;

    return 'Введены неверный логин и пароль!';
});

$form->checkevent($loginbutton,
<<<JS
    function (args) {
        if(args.get('login') == '' || args.get('password') == '') {
                alert('Логин и пароль не должны быть пустыми!');
            return false;
        }
        return true;
    };
JS
);

$form->beforeevent($loginbutton, function($args) {
    // Модификация данных перед отправкой
    $args['login'] = strtoupper($args['login']);
    return $args;
});

$form->onevent($loginbutton,
<<<JS
function (result) {
    if(typeof result != 'string') {
        LTS(myform).signal('DataSaved');
    }
    return result;
}
JS
);

LTS::Space()->build($form);
?>
