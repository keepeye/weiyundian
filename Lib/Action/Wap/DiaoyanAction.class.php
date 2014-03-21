<?php
class DiaoyanAction extends BaseAction {
	public $token;
	public $wecha_id;
	public $wxsign;
	function _initialize(){
		parent::_initialize();
		$this->token = I('token',I('get.token',''),'');//获取token
		$this->wecha_id = I('wecha_id',I('get.wecha_id',session('wecha_id')));
		$this->wxsign = I('wxsign',I('get.wxsign',session('wxsign')));
	}
	//活动入口
	function index(){
		$id = I('id','0','intval');//获取活动id
		$diaoyan = M('Diaoyan')->where(array("token"=>$this->token,"id"=>$id))->find();//查询活动信息
		$this->assign("diaoyan",$diaoyan);
		$this->assign("token",$this->token);
		$this->assign("wxsign",$this->wxsign);
		$this->assign("wecha_id",$this->wecha_id);
		$this->display();
	}

	//答题开始
	function questions(){

	}

	//检测合法性
	private function _checkWxsign(){
		return !empty($this->wecha_id) && (md5($this->token.$this->wecha_id.C('safe_key')) == $this->wxsign);
	}
}