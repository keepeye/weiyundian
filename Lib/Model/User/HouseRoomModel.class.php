<?php
class HouseRoomModel extends Model{
	protected $_validate = array(
			array('name','require','户型名称不能为空',1),
			array('floor','require','楼层必须填写',1),
			array('area','require','建筑面积不能为空',1),
			array('order','require','显示顺序不能为空',1),
			array('id','checkid','非法操作',2,'callback',2),
			array('category_id','/^[1-9]/','请选择一个子楼盘'),
			
	);
	
	protected $_auto = array (
			array('token','getToken',Model::MODEL_BOTH,'callback'),
			array('create_time','time',Model::MODEL_INSERT,'function'),
			array('update_time','time',Model::MODEL_BOTH,'function'),
	);
	
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