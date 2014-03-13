<?php
//访问、转发统计查看
class TjAction extends UserAction{
	private $_token;
	private $_model;
	function _initialize(){
		parent::_initialize();
		$this->_token = session('token');//获取商户token
		$this->_model = D('tongji');
	}
	
	//默认统计
	function index(){
		$list = $this->_model->field("concat(year,'/',month,'/',day) as day,SUM(shares) as shares,SUM(clicks) as clicks")->where(array("token"=>$this->_token))->group("year,month,day")->limit(0,10)->order("id ASC")->select();//10天内的数据
		$data['day'] = array_column($list,"day");
		$data['shares'] = array_column($list,"shares");
		//$data['clicks'] = array_column($list,"clicks");
		
		 $this->assign("data",$data);
		 $this->display();
	}
}