<?php
//访问、转发统计查看
class TjAction extends TongjiBaseAction{
	private $_token;
	private $_model;
	function _initialize(){
		parent::_initialize();
		$this->_token = session('token');//获取商户token
		$this->_model = D('tongji');
	}
	//概况，只显示总的概况
	function index(){
		$globalCount = $this->_model->getGlobalCount();//获取全局统计
		$this->assign("globalCount",$globalCount);
		$this->display();
	}

	//详细统计列出每个文档的概况
	function details(){

	}
	
	//文档具体统计,显示指定文档的每天统计数据
	function detail(){

	}

	private function _globalMonth(){
		$map = array();
		$this->_setField();//设置字段
		return M('tongji')->where($map)->find();
	}

	private function _setField(){
		M('tongji')->field("SUM('shares') as shares");
	}

}