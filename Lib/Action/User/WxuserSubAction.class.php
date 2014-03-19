<?php
class WxuserSubAction extends UserAction{

	function index(){
		//读取子账户列表
		$list = M('WxuserSub')->where(array("token"=>$this->token))->select();
		$this->assign("list",$list);
		$this->display();
	}
	//添加用户
	function addUser(){
		if(!IS_POST){
			$this->display();
		}else{
			
		}
	}

}