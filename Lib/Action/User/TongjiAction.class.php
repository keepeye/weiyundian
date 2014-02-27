<?php
class TongjiAction extends UserAction{
	function index(){
		$id=$this->_get('id','intval');
		$token=$this->_get('token','trim');
		$info=M('Wxuser')->find($id);
		if($info==false||$info['token']!==$token){
			$this->error('非法操作',U('User/Index/index'));
		}
		session('token',$token);
		session('wxid',$info['id']);
		$this->assign('token',session('token'));
		$this->display();
	}
}

?>
