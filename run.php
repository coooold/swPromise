<?php
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