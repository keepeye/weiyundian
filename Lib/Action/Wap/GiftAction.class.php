<?php
class DiaoyanAction extends WapAction {
	public $token;
	public $wecha_id;
	function _initialize(){
		parent::_initialize();
		
		if( ! $this->checkWxsign())
		{
			$this->redirect("Home/Adma/index?token=".$this->token);
		}

		$this->assign("token",$this->token);
		$this->assign("wecha_id",$this->wecha_id);
	}

	

	//活动入口
	function index(){
		$list = M('Gift')->select();
		dump($list);
		//$this->display();
	}

	
}