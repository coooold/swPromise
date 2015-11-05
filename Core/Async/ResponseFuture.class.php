<?php
namespace Core\Async;
/**
 * ResponseFuture.class.php
 * @author fang
 * @date 2015-11-5
 */
class ResponseFuture implements FutureIntf {
	protected $response;
	
	public function __construct($response){
		$this->response = $response;
	}
	
	public function run(Promise &$promise) {
		$data = json_encode($promise->getData());
		$this->response->end($data);
		$promise->accept ();
	}
}