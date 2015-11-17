<?php
namespace Core\Async;
class Timer
{
	static protected $timers = array();
	static public function add($sock, $timeout, $cb){
		self::$timers[$sock] = swoole_timer_after($timeout, function()use($cb, $sock){
			unset(self::$timers[$sock]);
			$cb();
		});
	}

	static public function del($sock){
		if(isset(self::$timers[$sock])){
			swoole_timer_clear(self::$timers[$sock]);
			unset(self::$timers[$sock]);
		}
	}
}