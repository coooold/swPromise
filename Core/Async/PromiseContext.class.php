<?php
namespace Core\Async;
/**
 * PromiseContext.class.php
 * @author fang
 * @date 2015-11-5
 */
class PromiseContext {
	protected $data = array ();
	public function set($k, $v) {
		$this->data [$k] = $v;
	}
	public function merge($data){
		if(is_array($data)){
			$this->data = array_merge($this->data, $data);
		}
	}
	public function get($k) {
		return $this->data [$k];
	}
	public function getAll() {
		return $this->data;
	}
}