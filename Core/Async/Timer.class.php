<?php
namespace Core\Async;
/**
 * 参考TSF框架，使用tick函数处理超时功能，避免使用after计时器可能导致的内存泄漏问题
 */
class Timer
{
	static protected $eventSlots = array();
	static protected $sockSlotIndex = array();
	static protected $tick = 0;
	static protected $timer = null;
	const LOOP_TIME = 0.05;
	const SLOT_SIZE = 200;
	
	static public function add ($sock, $timeout, $callback){
		self::init();
		$tick = (self::$tick + intval($timeout/self::LOOP_TIME)) % self::SLOT_SIZE;
		self::$eventSlots[$tick][$sock] = $callback;
		self::$sockSlotIndex[$sock] = $tick;
	}
	
	static public function del($sock){
		if(isset(self::$sockSlotIndex[$sock])){
			$tick = self::$sockSlotIndex[$sock];
			unset(self::$eventSlots[$tick][$sock]);
			unset(self::$sockSlotIndex[$sock]);
		}
	}
	
	static public function loop(){
		self::$tick++;
		if(self::$tick == self::SLOT_SIZE)self::$tick = 0;
		if(!self::$eventSlots[self::$tick])return;

		foreach(self::$eventSlots[self::$tick] as $sock => $callback){
			$callback();
			unset(self::$sockSlotIndex[$sock]);
			unset(self::$eventSlots[self::$tick][$sock]);
		}
		
		self::$eventSlots[self::$tick] = array();
	}
	
	static public function init(){
		if (self::$timer === null){
			for($i=0; $i<self::SLOT_SIZE; $i++){
				self::$eventSlots[$i] = array();
			}
			self::$timer = swoole_timer_tick(1000 * self::LOOP_TIME, function($tid){
				self::loop($tid);
			});
		}
	}
}