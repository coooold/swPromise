<?php
namespace Core\Async;
/**
 * FutureIntf.class.php
 * @author fang
 * @date 2015-11-5
 */
interface FutureIntf {
	public function run(Promise &$promise);
}