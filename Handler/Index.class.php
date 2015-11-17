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
class Handler_Index extends \Core\Handler{
	public function run($request, $response){
		Promise::create ( Model::getUserInfo ( 'user1', 'haha' ) )
			->then (Model::getUserInfo ( 'user2', 'haha2' ))
			->then ( new ResponseFuture ($response) )
			->start ( new PromiseContext () );
	}
}


//////////////////////////////////////////辅助类 Model Service

class Service {
	static public function get($api, $params = array()) {
		$url = $api . '?' . http_build_query($params);
		$proxy=null;
		return Promise::create ( new HttpClientFuture ( $url, null, $proxy ) );
	}
}
class Model {
	static public function getUrl($ret, $url, $params = array()) {
		return Service::get ( $url,$params )->then ( function ($promise) use($ret) {
			$data = $promise->get ( 'http_data' );
			$promise->accept ([$ret=>$data]);
		} );
	}
	
	static public function getUserInfo($ret, $usr){
		$params = array();
		$url = 'http://127.0.0.1:9932/fang/swAsync/1.json';
		
		return Service::get ( $url,$params )->then ( function ($promise) use($ret) {
			$data = $promise->get ( 'http_data' );
			$promise->accept ([$ret=>$data]);
		} );
	}
}