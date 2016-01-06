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
			'worker_num' => 2,
		]);
        $this->controller = new MyController();
		$http->start();
	}

	public function onRequest($request, $response){
		//echo "worker onRequest\n";
		$this->controller->run($request, $response);
        
        static $i=0;
        
        if($i++ >= 1000){
            echo "----->Mem: ", memory_get_usage(), "b\n";
            $i = 0;
        }
	}
}

$app = new App();
$app->start('', 9502);