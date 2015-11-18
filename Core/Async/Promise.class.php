<?php
namespace Core\Async;
/**
 * Promise.class.php
 * @author fang
 * @date 2015-11-5
 */
class Promise {
	public $context = null;		//PromiseContext
	protected $future;				//FutureIntf
	protected $lastPromise = null;	//Promise
	protected $nextPromise = null;	//Promise
	protected function __construct($future) {
		$this->future = $future;
	}
	static public function create($sth) {
		if (is_callable ( $sth )) {
			$future = new Future ( $sth );
			return new self ( $future );
		} elseif ($sth instanceof FutureIntf) {
			return new self ( $sth );
		} elseif ($sth instanceof Promise) {
			return $sth;
		} elseif (is_array($sth)) {
			return PromiseGroup::create($sth);
		} else {
			throw new Exception ( 'error sth type' );
		}
	}

	public function then($sth) {
		if (is_callable ( $sth )) {
			$future = new Future ( $sth );
			$nextPromise = new self ( $future );
			$this->nextPromise = $nextPromise;
			$nextPromise->lastPromise = $this;
			return $nextPromise;
		} elseif ($sth instanceof FutureIntf) {
			$nextPromise = new self ( $sth );
			$this->nextPromise = $nextPromise;
			$nextPromise->lastPromise = $this;
			return $nextPromise;
		} elseif ($sth instanceof Promise) {
			// 拿到的sth一定是尾promise，把头promise挂上主promise
			$headPromise = $sth->getHeadPromise ();
			$this->nextPromise = $headPromise;
			$headPromise->lastPromise = $this;
			return $sth;
		} elseif (is_array($sth)){
			$nextPromise = PromiseGroup::create($sth);
			$this->nextPromise = $nextPromise;
			$nextPromise->lastPromise = $this;
			return $nextPromise;
		}else {
			throw new Exception ( 'error sth type' );
		}
	}

	// 找到第一个promise然后执行
	public function start($context) {
		$headPromise = $this->getHeadPromise ();
		$headPromise->run ( $context );
		unset($headPromise);
	}

	protected $accepted = false;
	// 成功后执行
	public function accept($ret = null) {
		if($this->accepted)return;	//仅执行一次
		$this->accepted = true;
		
		if ($this->nextPromise !== null) {
			if(is_array($ret)){
				$this->context->merge($ret);
			}
			$this->nextPromise->run ( $this->context );
			unset($this->nextPromise);
		}
	}

	//设置上下文数据
	public function set($key, $val){
		return $this->context->set($key, $val);
	}

	//获取上下文数据
	public function get($key){
		return $this->context->get($key);
	}

	//获取全部上下文数据
	public function getData(){
		return $this->context->getAll();
	}

	// 失败后执行
	public function reject() {
	}

	// /////////////////////////////////////////////

	// 取得第一个promise
	protected function getHeadPromise() {
		for($i = $this; $i->lastPromise != null; $i = $i->lastPromise)
			;
		return $i;
	}
	protected function run(PromiseContext $context) {
		$this->context = $context;
		$ret = $this->future->run ( $this, $context );
		unset($this->future);

		// 如果返回值是个promise，那么把后续的promise链条挂载到这个promise后面，然后继续执行
		if ($ret instanceof Promise) {
			$ret->nextPromise = $this->nextPromise;
			if ($this->nextPromise) {
				$this->nextPromise->lastPromise = $ret;
			}
			$ret->start ( $context );
		}
	}
}