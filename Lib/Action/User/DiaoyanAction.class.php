<?php
/*
by 先兆
 */
class DiaoyanAction extends UserAction {
	private $_token;
	function _initialize(){
		parent::_initialize();
		$this->_token = $this->token?$this->token:session('token');//获得token
	}
	//活动列表
	function index(){
		$list = M('Diaoyan')->where(array("token"=>$this->_token))->select();//读取活动列表
		$this->assign("list",$list);
		$this->display();
	}
	//设置调研
	function setDiaoyan(){

	}
	//题库
	function questionList(){

	}
	//设置题目
	function setQuestion(){

	}

	//报名记录
	function recordList(){

	}
}