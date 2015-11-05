<?php
/**
 * Controller.class.php
 * @author fang
 * @date 2015-10-27
 */
namespace Core;
class Controller{
	public function run($request, $response){
		$route = $request->server['request_uri'];
		$handler = $this->getHandler($route);
		$handler->run($request, $response);
	}
	
	//////////////////////////////////////////////////////////////////
	
	protected $handlerMap = array();
	protected $handlerCache = array();	//将handler实例缓存住
	
	/**
	 * @param String $route
	 * @throws \Exception
	 * @return \Core\Handler
	 */
	protected function getHandler($route){
		if(!array_key_exists($route, $this->handlerCache)){
			if(!array_key_exists($route, $this->handlerMap)){
				$route = '/404';
			}
			$handlerName = $this->handlerMap[$route];
			$this->handlerCache[$route] = new $handlerName();
		}
		return $this->handlerCache[$route];
	}
}