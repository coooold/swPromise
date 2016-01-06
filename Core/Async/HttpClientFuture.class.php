<?php
namespace Core\Async;
/**
 * HttpClientFuture.class.php
 * @author fang
 * @date 2015-11-5
 */
class HttpClientFuture implements FutureIntf {
	protected $url = null;
	protected $post = null;
	protected $timer = null;
	protected $proxy = false;
	protected $timeout = 0.5;
	
	public function __construct($url, $post = array(), $proxy = array(), $timeout = 0.5) {
		$this->url = $url;
		$this->post = $post;
		if($proxy){
			$this->proxy = $proxy;
		}
		$this->timeout = $timeout;
	}
	

	
	public function run(Promise &$promise) {
		$urlInfo = parse_url ( $this->url );
		$timeout = $this->timeout;
		if(!isset($urlInfo ['port']))$urlInfo ['port'] = 80;
        
        $cli = new \swoole_http_client($urlInfo['host'], $urlInfo ['port']);
		$cli->set(array(
            'timeout' => $timeout,
            'keepalive' => 0,
        ));
        $cli->on ( "error", function($cli)use(&$promise){
            Timer::del($cli->sock);
			$promise->accept(['http_data'=>null, 'http_error'=>'Connect error']);
        } );
        $cli->on ( "close", function ($cli)use(&$promise) {
		} );
        $cli->execute( $this->url, function ($cli)use(&$promise) {
            Timer::del($cli->sock);
            $cli->isDone = true;
            $promise->accept(['http_data'=>$cli->body]);
        } );

		$cli->isConnected = false;

		if(!$cli->errCode){
			Timer::add($cli->sock, $this->timeout, function()use($cli, &$promise){
				@$cli->close();
				if($cli->isConnected){
					$promise->accept(['http_data'=>null, 'http_error'=>'Http client read timeout']);
				}else{
					$promise->accept(['http_data'=>null, 'http_error'=>'Http client connect timeout']);
				}
			});
		}
	}
}
