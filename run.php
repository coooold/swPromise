<?php
ini_set('display_errors',1);
error_reporting(E_ALL);
ini_set('memory_limit','1024M');

include __dir__.'/Core/autoload.php';

class App{
	protected $controller = null;
	
	public function start($host, $port){
		$controller = $this->controller;
		$http = new \swoole_http_server($host, $port, SWOOLE_BASE);
		$http->on('request', array($this, 'onRequest'));
		$http->set([
			'worker_num' => 1,
		]);
		$http->on('WorkerStart', array($this, 'onWorkerStart'));
		$http->start();
	}
	
	public function onWorkerStart(){
		//echo "worker started\n";
		unset($this->controller);
		$this->controller = new MyController();
	}
	
	public function onRequest($request, $response){
		//echo "worker onRequest\n";
		$this->controller->run($request, $response);
	}
}

$app = new App();
$app->start('', 9502);