<?php
class WechaScoreAction extends UserAction
{
	function __construct(){
		parent::__construct();
		if(!$this->token){
			$this->error('非法访问');
		}
		$this->assign("token",$this->token);
	}

	function index()
	{
		echo "推广设置，积分兑换";
	}

	
	
}