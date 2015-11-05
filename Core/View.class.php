<?php
/**
 * View.class.php
 * @author fang
 * @date 2015-10-27
 */
namespace Core;
class View{
	static protected $data = array();

	static public function setData($data){
		self::$data = array_merge(self::$data, $data);
	}

	static public function display($tpl, $data = array()){
		$tplName = _TEMPLATE .'/'. $tpl;
		ini_set('short_open_tag', true);
		extract(self::$data);
		include($tplName);
	}
}

function viewInclude($tpl){
	\Core\View::display($tpl);
}

function viewEcho($var, $default = ''){
	echo $var === null?$default : $var;
}

function viewCondEcho($cond, $var, $default = ''){
	if($cond)viewEcho($var, $default);
}