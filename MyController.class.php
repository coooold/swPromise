<?php
/**
 * MyController.class.php
 * @author fang
 * @date 2016-01-06
 */
class MyController extends Core\Controller{
	protected $handlerMap = array(
            '/async' => 'Handler_Index',
            '/sync' => 'Handler_Sync',
        );
}