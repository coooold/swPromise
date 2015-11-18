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

	protected $phase = 0;	//执行阶段 0:map 1:reduce
	protected $runCount = 0;
	protected function run(PromiseContext $context) {
		if($this->phase == 0){
			$this->context = $context;
			$this->phase = 1;
			$this->runCount = count($this->subPromiseArray);
			foreach($this->subPromiseArray as $subPromise){
				$subPromise->start ( $this->context );
				unset($subPromise);
			}
			unset($this->subPromiseArray);
		}else{
			$this->context->merge($context);
			$this->runCount --;
			if($this->runCount == 0){//都执行完了
				$this->accept();
			}
		}
	}
}