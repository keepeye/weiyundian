<?php
class WxuserSubAction extends UserAction{

	function index(){
		//读取子账户列表
		$list = M('WxuserSub')->where(array("token"=>$this->token))->select();
		dump($list);
		//$this->display();
	}
}