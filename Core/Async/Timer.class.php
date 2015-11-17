<?php
namespace Core\Async;
/**
 * 参考TSF框架，使用tick函数处理超时功能，避免使用after计时器可能导致的内存泄漏问题
 */
class Timer
{
	static protected $event = array();
	static protected $timer = null;
	const LOOPTIME = 0.5;
	
	static public function add ($sock, $timeout, $callback){
		self::init();
		
		$startTime = microtime(true);
		$event = array(
			'starttime' => microtime(true),
			'timeout' => $timeout,
			'callback' => $callback,
		);
		
		self::$event[$sock] = $event;
	}
	
	static public function del($sock){
		if(isset(self::$event[$sock]))unset(self::$event[$sock]);
	}
	
	static public function loop($t){
		if(!self::$event)return;

		$now = microtime(true);
		foreach(self::$event as $sock => $event){
			//echo "timeout ",$now - $event['starttime'] ," ", $event['timeout'],"\n";
			//超时处理
			if($now - $event['starttime'] > $event['timeout']){
				self::del($sock);
				$cb = $event['callback'];
				$cb();
			}
		}
	}
	
	static public function init(){
		if (self::$timer === null){
			self::$timer = swoole_timer_tick(1000 * self::LOOPTIME, function($tid){
				self::loop($tid);
			});
		}
	}
}