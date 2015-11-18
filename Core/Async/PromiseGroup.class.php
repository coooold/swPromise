<?php
namespace Core\Async;
/**
 * PromiseGroup.class.php
 * 用于并行执行Promise，自己发起，自己接收，承担了map + reduce两个工作
 * @author fang
 * @date 2015-11-5
 */
class PromiseGroup extends Promise {
	protected $subPromiseArray = array();
	protected function __construct() {}
	
	static public function create($sthGroup) {
		if(!is_array($sthGroup)){
			throw new \Exception('asset is_array($sthGroup)');
		}
		
		$promiseGroup = new self();
		
		foreach($sthGroup as $sth){
			$promise = Promise::create($sth);
			$promise->nextPromise = $promiseGroup;
			$promiseGroup->subPromiseArray[] = $promise;
		}
		
		return $promiseGroup;
	}

	// /////////////////////////////////////////////

	protected function run(PromiseContext $context) {
		$this->context = $context;
		
		$subPromise = \array_pop($this->subPromiseArray);	//一个一个执行下去

		if($subPromise){
			$subPromise->start ( $this->context );
			unset($subPromise);
		}else{	//都执行完了
			$this->accept();
		}
	}
}