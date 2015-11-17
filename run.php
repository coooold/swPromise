<?php
ini_set('display_errors',0);
ini_set('memory_limit','1024M');
error_reporting(0);
include __dir__.'/Core/autoload.php';


class MyController extends \Core\Controller{
	protected $handlerMap = array(
			'/' => 'Handler_Index',
			'/404' => 'Handler_Index',
			'/sync' => 'Handler_Sync',
	);
}

$app = new \Core\App(new MyController());
$app->start('', 9502);