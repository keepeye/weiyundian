<?php
class GiftAction extends UserAction
{
	function __construct(){
		parent::__construct();
		if(!$this->token){
			$this->error('非法访问');
		}
		$this->assign("token",$this->token);
	}

	//默认首页
	function index()
	{
		$this->display();
	}

}