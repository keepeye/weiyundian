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
		$m = M('Gift');
		$list = $m->select();

		$this->assign("list",$list);
		$this->display();
	}

}