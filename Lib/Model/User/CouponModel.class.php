<?php
class CouponModel extends Model{

	protected $_validate = array(
			array('keyword','require','关键词不能为空',1),
			array('keyword','1,10','关键词最多10个字',1,'length'),
			array('title','require','活动标题不能为空',1),
			array('title','1,20','标题太长，最多20个汉字',1,'length'),
			array('start_time','require','开始时间不能为空',1),			
			array('end_time','require','结束时间不能为空',1),
			array('end_time', 'checkdate', '结束时间不能小于开始时间',Model::MUST_VALIDATE,'callback',3),
			array('info','require','活动说明不能为空',1),
			array('info','1,1000','介绍最多1000个字',1,'length'),
			array('num', 'checknum', '优惠券必须是大于0的数字',Model::MUST_VALIDATE,'callback',3),
	 );
	protected $_auto = array (    
		array('status','1'),  // 新增的时候把status字段设置为1   
		array('start_time','strtotime',3,'function'), 
		array('end_time','strtotime',3,'function')
	);
	
	
	function checkdate($data){	
		 if(strtotime($data)<strtotime($_POST['start_time'])){
			 return false;
		}else{
			return true;
		}
	}

	function checknum($data){
		$num = (int)$data;
		return $num>0;
	}
}

?>