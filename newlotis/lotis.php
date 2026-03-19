<?php
session_start();

$__ltsinputcontent = file_get_contents("php://input");
if(! empty($__ltsinputcontent)) // для page и прочих post запросов с переменными
{
	$__ltsdata = json_decode($__ltsinputcontent, true);
	if(array_key_exists('__ltsVars', $__ltsdata))
	{
		foreach($__ltsdata['__ltsVars'] as $varsname => $vars)
			$_SESSION[$varsname] = $vars;
	}
	if(array_key_exists('__ltspageurl', $__ltsdata))
		exit('{ "url" : "' . $__ltsdata['__ltspageurl'] . '" }');
}

if(array_key_exists('__ltsVars', $_POST)) // для Events
{
    $globalvarsstr = $_POST['__ltsVars'];
    $globalvars = json_decode($globalvarsstr);

    foreach($globalvars as $varsname => $vars)
	    $_SESSION[$varsname] = $vars;

	if(array_key_exists('__ltspageurl', $_POST)) 
		exit('{ "url" : "' . $_POST['__ltspageurl'] . '" }');
}

define('_LOTIS_INTERNET', false);
define('_LOTIS_DIR', dirname(__DIR__) . '/newlotis/');
define('_LOTIS_JSDIR', '/newlotis/JS/');
define('_LOTIS_CSSDIR', '/newlotis/CSS/');

$__lts_classes = array();

spl_autoload_register(
	function ($classname) 
	{
		global $__lts_classes;

		$p = strpos($classname, '\\');
		if($p !== false)
			$__lts_classes[] = substr($classname, $p + 1);
		else
			$__lts_classes[] = $classname;	

		$fileclassname = str_replace('\\', '/', $classname) . '.php';

		if(file_exists($fileclassname))
			include $fileclassname;
		else	
			if(file_exists($classname . '.php'))
		    	include $classname . '.php';
		else
			if(file_exists(_LOTIS_DIR . $fileclassname)) 
				include _LOTIS_DIR . $fileclassname; 
		else	
			if(file_exists(_LOTIS_DIR . $classname . '.php'))
				include _LOTIS_DIR . $classname . '.php';
	}
);

// Запускаем логгер
LTS\Logger::init();

abstract class LTS
{
	static function inputrequest($reg, $filter = FILTER_SANITIZE_SPECIAL_CHARS)
	{
		if(array_key_exists($reg, $_POST))
		{
			$res = filter_input(INPUT_POST, $reg, $filter);
			if($res)
				return $res;
		}
		else
		if(array_key_exists($reg, $_GET))
		{
			$res = filter_input(INPUT_GET, $reg, $filter);
			if($res)
				return $res;
		}
	
		return false;
	}

	static function isAssociativeArray($array) 
	{
		return (array_keys($array) !== range(0, count($array) - 1));
	}

	static function explodestr($name)
	{
		if (is_array($name)) return $name;
		if (is_object($name)) return (array) $name;

		$name = trim((string) $name);
		if ($name === '') return [];

		// Приоритет: | → ; → ,
		$delimiter = '|';
		if (strpos($name, $delimiter) === false) {
			$delimiter = ';';
			if (strpos($name, $delimiter) === false) {
				$delimiter = ',';
			}
		}

		return array_map('trim', explode($delimiter, $name));
	}
	
	static function log($filename, $arr)
	{
		$ss = json_encode($arr);
		file_put_contents($filename, $ss);
	}

	static function _new($type, $id = '', $parent = null)
	{
		$_type = "LTS\\{$type}";
			
		$_id = is_string($parent) ? $parent : (is_object($id) ? '' : $id);
		$_parent = is_object($id) ? $id : $parent;

		$el = new $_type($_id);

		if($_parent !== null)
			$_parent->add($el);

		return $el;
	}

	static function scriptversion($v)
	{
		LTS\Space::$scriptversion = $v;
	}
	
	static function meta($v)
	{
		LTS\Space::$meta = $v;
	}
	
	static function keywords($v)
	{
		LTS\Space::$keywords = $v;
	}
	
	static function description($v)
	{
		LTS\Space::$description = $v;
	}
	
	static function Accordion($id = '', $parent = null)
	{
		return LTS::_new('Accordion', $id, $parent);
	}

	static function Button($id = '', $parent = null)
	{
		return LTS::_new('Button', $id, $parent);
	}

	static function Cells($id = '', $parent = null)
	{
		return LTS::_new('Cells', $id, $parent);
	}
	
	static function Columns($id = '', $parent = null)
	{
		return LTS::_new('Columns', $id, $parent);
	}
	
	static function Construct($id = '', $parent = null)
	{
		return LTS::_new('Construct', $id, $parent);
	}
	
	static function CSS($id = '', $parent = null)
	{
		return LTS::_new('CSS', $id, $parent);
	}
	
	static function DataTable($id = '', $parent = null)
	{
		return LTS::_new('DataTable', $id, $parent);
	}

	static function DataSync($id = '', $parent = null)
	{
		return LTS::_new('DataSync', $id, $parent);
	}

	static function DataView($id = '', $parent = null)
	{
		return LTS::_new('DataView', $id, $parent);
	}

	static function Dialog($id = '', $parent = null)
	{
		return LTS::_new('Dialog', $id, $parent);
	}

	static function Div($id = '', $parent = null)
	{
		return LTS::_new('Div', $id, $parent);
	}

	static function Element($id = '', $parent = null)
	{
		return LTS::_new('Element', $id, $parent);
	}

	static function Events($id = '', $parent = null)
	{
		return LTS::_new('Events', $id, $parent);
	}

	static function FilterForm($id = '', $parent = null)
	{
		return LTS::_new('FilterForm', $id, $parent);
	}

	static function Form($id = '', $parent = null)
	{
		return LTS::_new('Form', $id, $parent);
	}

	static function Grid($id = '', $parent = null)
	{
		return LTS::_new('Grid', $id, $parent);
	}

	static function Html($id = '', $parent = null)
	{
		return LTS::_new('Html', $id, $parent);
	}

	static function Input($id = '', $parent = null)
	{
		return LTS::_new('Input', $id, $parent);
	}

	static function JS($id = '', $parent = null)
	{
		return LTS::_new('JS', $id, $parent);
	}
	
	static function LayerSlider($id = '', $parent = null)
	{
		return LTS::_new('LayerSlider', $id, $parent);
	}
	
	static function Lang($id = '', $parent = null)
	{
		return LTS::_new('Lang', $id, $parent);
	}
	
	static function LookupField($id = '', $parent = null)
	{
		return LTS::_new('LookupField', $id, $parent);
	}
	
	static function MySql($name, $server, $user, $password)
	{
		return new LTS\MySql($name, $server, $user, $password);
	}
	
	static function MySqlField($name, $type, $param = null)
	{
		return new LTS\MySqlField($name, $type, $param);
	}
	
	static function MySqlTable($name)
	{
		return new LTS\MySqlTable($name);
	}
	
	static function MultiTable(LTS\MySqlTable $header)
	{
		return new LTS\MultiTable($header);
	}
	
	static function ProgressBar($id = '', $parent = null)
	{
		return LTS::_new('ProgressBar', $id, $parent);
	}
	
	static function SimpleChart($id = '', $parent = null)
	{
		return LTS::_new('SimpleChart', $id, $parent);
	}
	
	static function Space($id = '', $parent = null)
	{
		return LTS::_new('Space', $id, $parent);
	}
	
	static function Span($id = '', $parent = null)
	{
		return LTS::_new('Span', $id, $parent);
	}
	
	static function Stock(LTS\MySqlTable $dbtable, $fieldmap = [])
	{
		return new LTS\Stock($dbtable, $fieldmap);
	}
	
	static function Tabs($id = '', $parent = null)
	{
		return LTS::_new('Tabs', $id, $parent);
	}
	
	static function Quark($id = '', $parent = null)
	{
		return LTS::_new('Quark', $id, $parent);
	}

	static function QueryBuilder()
	{
		return new LTS\QueryBuilder;
	}

	static function Vars($id = '', $parent = null)
	{
		return LTS::_new('Vars', $id, $parent);
	}

	static function Video($id = '', $parent = null)
	{
		return LTS::_new('Video', $id, $parent);
	}
}
?>
