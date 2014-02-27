<?php
class HouseModel extends Model{
	protected $_validate = array(
			array('title','require','图文消息标题不能为空',1),
			array('picurl','require','图文消息封面必须填写',1),
			array('id','checkid','非法操作',2,'callback',2),
			
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