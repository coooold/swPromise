<?php
namespace Core;
/**
 * App.class.php
 * @author fang
 * @date 2015-11-5
 */
class App{
	protected $controller = null;
	
	public function __construct(Controller $controller){
		$this->controller = $controller;
	}
	
	public function start($host, $port){
		$controller = $this->controller;
		$http = new \swoole_http_server($host, $port, SWOOLE_BASE);
		$http->on('request', function ($request, $response) use($controller){
			$controller->run($request, $response);
		});
		$http->start();
	}
}