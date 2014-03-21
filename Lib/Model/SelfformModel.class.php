<?php
class SelfformModel extends Model{
	protected $_validate = array(
	array('name','require','名称不能为空',1)
	);
	protected $_auto = array (
	array('token','gettoken',1,'callback'),
	array('endtime','getTime',3,'callback'),
	array('time','time',1,'function')
	);
	function gettoken(){
		return session('token');
	}
	function getTime(){
		$date=$_POST['enddate'];
		
		$time = strtotime($date);
		exit($time);
		return $time;
	}
}

?>