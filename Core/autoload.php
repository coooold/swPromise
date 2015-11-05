<?php
define('_ROOT', realpath(__DIR__.'/..'));
define('_TEMPLATE', _ROOT.'/html');

spl_autoload_register(function ($class) {
	if ($class) {
		$file = str_replace(array('\\','_'), '/', $class).'.class.php';
		include __DIR__.'/../'.$file;
	}
});
