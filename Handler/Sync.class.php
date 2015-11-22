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
class Handler_Sync extends \Core\Handler{
	public function run($request, $response){
		$response->end(123);return;		
		$ctx = new PromiseContext ();
		$ctx->set('aaa','bbddddddddddddddddddddddddddddddddddddddddddddddddddddd
		ssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssb');
		
		Promise::create([
			new ResponseFuture ($response)
		])->start($ctx);
	}
}
