<?php
class TravelAction extends BaseAction {
	public $contact;
	public $token;
	public $wecha_id;
	function __construct(){
		parent::__construct();
		if(!isset($_GET['token'])){
			$this->error('无法访问呢');
		}
		$this->token = $this->_get('token');
		$this->assign('token',$this->token);
		$this->wecha_id	= $this->_get('wecha_id');
		if (!$this->wecha_id){
			//$this->wecha_id='';
		}
		$this->assign('wecha_id',$this->wecha_id);
		//读取旅行社资料
		$this->contact = M('TravelContact')->where(array('token'=>$_GET['token']))->find();
		$this->assign("contact",$this->contact);
	}
	//wap首页
	function index(){
		$this->display();
	}
	//线路列表
	function routeList(){
		$map = array();
		//商家id
		$map['token'] = $_GET['token'];
		//标记查询
		if(isset($_GET['flag'])){
			$flag = addslashes($_GET['flag']);//安全转义
			$map['_string'] = "FIND_IN_SET('{$flag}',flag)";
		}
		//分类查询
		if(isset($_GET['type'])){
			$map['type'] = $_GET['type'];
		}
		//关键词筛选
		if(isset($_POST['keywords'])){
			$map['title'] = array('like',"%{$_POST['keywords']}%");
		}
		//标题处理
		$title = $_GET['title'];
		
		//查询列表
		$list = M('TravelRoute')->field('*')->where($map)->select();
		$this->assign('title',$title);
		$this->assign('list',$list);
		$this->display();
	}
	//线路详情
	function routeDetails(){
		$id = isset($_GET['id'])?$_GET['id']:0;
		$token = isset($_GET['token'])?$_GET['token']:"0";
		if(!$id || !$token){
			$this->error('非法请求');
		}
		$map = array(
			'id' => $id,
			'token' => $token,
		);
		$details = M('TravelRoute')->where($map)->find();
		if(!$details){
			$this->error('线路不存在');
		}
		
		if(!empty($details['imgurls'])){
			$details['imgurls'] = unserialize($details['imgurls']);//幻灯片反序列化
		}
		$this->assign('details',$details);
		$this->display();
	}
	//提交订单
	function postApply(){
		if(!IS_POST){
			$route_id = isset($_GET['route_id'])?$_GET['route_id']:0;
			$token = isset($_GET['token'])?$_GET['token']:"0";
			if(!$route_id || !$token){
				$this->error('非法请求');
			}
			$map = array(
				'id' => $route_id,
				'token' => $token,
			);
			$details = M('TravelRoute')->where($map)->find();
			if(!$details){
				$this->error('线路不存在');
			}
			if(!empty($details['actions'])){
				$details['actions'] = unserialize($details['actions']);//线路类型下拉
			}
			if(!empty($details['start_date'])){
				$details['start_date'] = unserialize($details['start_date']);//出发日期下拉
			}
			$this->assign('details',$details);
			$this->display();
		}else{
			$M = D('TravelRouteApply');
			$_POST['wecha_id'] = $this->wecha_id;
			if($M->create()){
				if($M->add()){
					$this->success('恭喜，你的订单已经提交，请等待客服处理，保持电话畅通。');
				}else{
					$this->error('sorry!请检查数据是否填写合法！');
				}
			}else{
				$this->error("系统发生错误，无法该请求");
			}
		}
	}
	
	//我的订单
	function myApply(){
		if(!$this->wecha_id || !$this->token){
			$this->error('非法请求');
		}
		$M = M('TravelRouteApply');
		$map = array(
			"a.wecha_id" => $this->wecha_id,
			"b.token" => $this->token,
		);
		$list = $M->alias("a")->field("a.*,b.title as route_title")->join("__TRAVEL_ROUTE__ as b on b.id=a.route_id")->where($map)->select();
		$this->assign('list',$list);
		$this->display();
	}
}