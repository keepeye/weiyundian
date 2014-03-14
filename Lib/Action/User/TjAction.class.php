<?php
//访问、转发统计查看
class TjAction extends UserAction{
	private $_token;
	private $_model;
	private $_types = array("img"=>"图文","dazhuanpan"=>"大转盘","selfform"=>"报名","guaguaka"=>"刮奖");
	function _initialize(){
		parent::_initialize();
		$this->_token = session('token');//获取商户token
		$this->_model = D('tongji');
		$this->assign("types",$this->_types);
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

	//访问统计
	function clicks(){

	}

	//转发统计
	function shares(){
		//今日分享最多的十条图文
		$today_list = $this->_model->where(array("token"=>$this->_token,"day"=>date("j",time())))->order("shares desc")->limit("0,10")->select();
		//本月分享最多的十条图文
		$month_list = $this->_model->field("title,type,SUM(shares) as shares")->group('pid')->where(array("token"=>$this->_token,"month"=>date("n",time())))->order("shares desc")->limit("0,10")->select();
		$this->assign("today_list",$today_list);
		$this->assign("month_list",$month_list);

		$this->display();
	}
}