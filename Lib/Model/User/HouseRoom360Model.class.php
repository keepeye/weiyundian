<?php
class HouseRoom360Model extends Model{
	protected $_validate = array(
			array('room_id','checkRoomid','roomid必须填写',1,'callback',3),
			array('title','require','名称不能为空',1),
			array('sort','require','显示顺序必须填写',1),
			array('url','require','图片不能为空',1),
			array('id','checkid','非法操作',2,'callback',2),
			
	);
	
	protected $_auto = array (
			array('token','getToken',Model::MODEL_BOTH,'callback'),
			array('create_time','time',Model::MODEL_INSERT,'function'),
			array('update_time','time',Model::MODEL_BOTH,'function'),
	);
	
	function checkRoomid(){
		$dataid=D("HouseRoom")->field('id')->where(array('id'=>$_POST['room_id'],'token'=>session('token')))->find();
		if($dataid==false){
			return false;
		}else{
			return true;
		}
	}
	
	function checkid(){
		$dataid=$this->field('id')->where(array('id'=>$_POST['id'],'token'=>session('token')))->find();
		if($dataid==false){
			return false;
		}else{
			return true;
		}
	}
	
	function getToken(){	
		return session('token');
	}
}

?>