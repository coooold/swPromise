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
		$url1 = 'http://192.168.6.20/fang/swAsync/1.json';
		$url2 = 'http://192.168.6.20/fang/swAsync/2.json';
		$data = httpGet($url1) . httpGet($url2);
		$response->end($data);
	}
}

function httpGet($url){
	return file_get_contents($url);
	
	
// 	// curl获取接口内容
// 	$ch = curl_init();
// 	curl_setopt($ch, CURLOPT_URL, $url);
// 	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
// 	//设定超时时间 支持毫秒级超时 fang 2015年5月5日19:05:24
// 	$timeout = 1;
// 	$data = curl_exec($ch);

// 	curl_close($ch);;
// 	return $data;
}





