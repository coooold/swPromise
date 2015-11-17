<?php
use \Core\Async\Promise;
use \Core\Async\PromiseContext;
use \Core\Async\ResponseFuture;
use \Core\Async\HttpClientFuture;
/**
 * Index.class.php
 * @author fang
 * @date 2015-11-5
 */
class Handler_Info extends \Core\Handler{
	public function run($request, $response){
		
		$cli = new \swoole_client ( SWOOLE_TCP, SWOOLE_SOCK_ASYNC );
		$cli->on ( "connect",function($cli){echo 1;});
		$cli->on ( "error",function($cli){echo 2;});
		$cli->on ( "close",function($cli){echo 3;});
		$cli->on ( "receive",function($cli, $data){echo 4;});
		$ret = $cli->connect ( '192.168.0.20', 80, 1000, 0);
		
		$response->end("Hello, world!");
		
	}
}
