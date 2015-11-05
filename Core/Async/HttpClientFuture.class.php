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

	public $rspHeaders = array();		//返回头信息
	public $body = '';
	
	protected $buffer = '';
	protected $isFinish = false;
	protected $trunk_length = 0;
	
	protected $proxy = false;
	
	public function __construct($url, $post = array(), $proxy = array()) {
		$this->url = $url;
		$this->post = $post;
		if($proxy){
			$this->proxy = $proxy;
		}
	}
	public function run(Promise &$promise) {
		$cli = new \swoole_client ( SWOOLE_TCP, SWOOLE_SOCK_ASYNC );
		$urlInfo = parse_url ( $this->url );
		if(!isset($urlInfo ['port']))$urlInfo ['port'] = 80;
		$cli->on ( "connect", function ($cli)use($urlInfo){
			
			$host = $urlInfo['host'];
			if($urlInfo['port'])$host .= ':'.$urlInfo['port'];
			
			$req = array();
			$req[] = "GET {$this->url} HTTP/1.1\r\n";
			$req[] = "User-Agent: PHP swAsync\r\n";
			$req[] = "Host:{$host}\r\n";
			$req[] = "Connection:close\r\n";
			$req[] = "\r\n";
			$req = implode('', $req);
			
			$cli->send ( $req );
		} );
		$cli->on ( "receive", function ($cli, $data = "") use(&$promise) {
			//这个地方可能调用多次，使用packRsp组装
			call_user_func_array(array($this, 'packRsp'), array('key' => $cli, 'data' => $data, 'promise' => &$promise));
		} );
		$cli->on ( "error", function ($cli) use(&$promise) {
			$promise->reject ();
		} );
		$cli->on ( "close", function ($cli) {
		} );
		
		
		if($this->proxy){
			$cli->connect ( $this->proxy['host'], $this->proxy ['port'], 1 );
		}else{
			$cli->connect ( $urlInfo ['host'], $urlInfo ['port'], 1 );
		}
	}
	
	
	/**
	 * [packRsp 组包合包，函数回调]
	 * @param  [type] $cli  [description]
	 * @param  [type] $data [description]
	 * @return [type]       [description]
	 */
	private function packRsp($cli, $data, Promise &$promise)
	{
		$this->buffer .= $data;
	
		if ($this->trunk_length > 0 and strlen($this->buffer) < $this->trunk_length) {
			return;
		}
	
		if (empty($this->rspHeaders)) {
			$ret = $this->parseHeader($this->buffer);
	
			if ($ret === false) {
				return;
			} else {
				//header + CRLF + body
				if (strlen($this->buffer) > 0) {
					$parsebody = $this->parseBody();
				}
			}
		} else {
			$parsebody = $this->parseBody();
		}
	
		if ($this->isFinish) {
			$cli->close();
			$data = array('head' => $this->rspHeaders, 'body' => $this->body);
			$promise->accept(['http_data'=>$data]);
		}	
	}
	
	
	/**
	 * 解析头信息
	 * @param  [type] $headerBuf [description]
	 * @return [type]            [description]
	 */
	private function parseHeader($data)
	{
		$parts = explode("\r\n\r\n", $data, 2);
	
		$headParts = explode("\r\n", $parts[0]);
		if (is_string($headParts)) {
			$headParts = explode("\r\n", $headParts);
		}
	
		if (!is_array($headParts) || !count($headParts)) {
			//TODO header buffer valid
			return false;
		}
	
		list($this->rspHeaders['protocol'], $this->rspHeaders['status'], $this->rspHeaders['msg']) = explode(' ', $headParts[0], 3);
		unset($headParts[0]);
	
		foreach ($headParts as $header) {
	
			$header = trim($header);
			if (empty($header)) {
				continue;
			}
	
			$h = explode(':', $header, 2);
			$key = trim($h[0]);
			$value = trim($h[1]);
			$this->rspHeaders[strtolower($key)] = $value;
		}
	
		if (isset($parts[1])) {
			$this->buffer = $parts[1];
		}
	
		return true;
	}
	
	/**
	 * @desc 数据解析
	 * @return boolean
	 */
	public function parseBody()
	{
		//解析trunk
		if (isset($this->rspHeaders['transfer-encoding']) and $this->rspHeaders['transfer-encoding'] == 'chunked') {
			while (1) {
				if ($this->trunk_length == 0) {
					$_len = strstr($this->buffer, "\r\n", true);
					if ($_len === false) {
						return false;
					}
					$length = hexdec($_len);
					if ($length == 0) {
						$this->isFinish = true;
						return true;
					}
					$this->trunk_length = $length;
					$this->buffer = substr($this->buffer, strlen($_len) + 2);
				} else {
					//数据量不足，需要等待数据
					if (strlen($this->buffer) < $this->trunk_length) {
						return false;
					}
					$this->body .= substr($this->buffer, 0, $this->trunk_length);
					$this->buffer = substr($this->buffer, $this->trunk_length + 2);
					$this->trunk_length = 0;
				}
			}
			return false;
		} else {//普通的
			if(!isset($this->rspHeaders['content-length'])){	//头信息里面没有content-length的直接结束
				print_r($this->rspHeaders);
				echo "missing content-length";
				
				$this->body = '';
				$this->isFinish = true;
				return true;
			}

			if (strlen($this->buffer) < $this->rspHeaders['content-length']) {
				return false;
			} else {
				$this->body = $this->buffer;
				$this->isFinish = true;
				return true;
			}
		}
	}
}