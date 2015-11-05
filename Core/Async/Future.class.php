<?php
namespace Core\Async;
/**
 * Future.class.php
 * @author fang
 * @date 2015-11-5
 */
class Future implements FutureIntf {
	protected $callback;
	public function __construct($callback) {
		$this->callback = $callback;
	}
	public function run(Promise &$promise) {
		$cb = $this->callback;
		return $cb ( $promise );
	}
}