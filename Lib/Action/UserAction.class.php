<?php
class UserAction extends BaseAction{
	protected $trade;//行业
	protected $token;//微信token
	protected function _initialize(){
		parent::_initialize();
		//判断用户是否登录
		if(session('uid')==false){
			$this->redirect('Home/Index/login');
		}
		$userinfo=M('User_group')->where(array('id'=>session('gid')))->find();
		$this->assign('userinfo',$userinfo);
		$users=M('Users')->where(array('id'=>$_SESSION['uid']))->find();
		$this->assign('thisUser',$users);
		//dump($users);
		$this->assign('viptime',$users['viptime']);
		if(session('uid')){
			if($users['viptime']<time()){
				session(null);
				session_destroy();
				unset($_SESSION);
				$this->error('您的帐号已经到期，请充值后再使用');
			}
		}
		//尝试从session中获取token，并查询wxuser详细信息
		$this->token = session('token');
		if($this->token){
			$wecha=M('Wxuser')->field('wxname,wxid,headerpic,weixin')->where(array('token'=>$this->token,'uid'=>session('uid')))->find();
			$this->assign('wecha',$wecha);
			$this->assign('token',$this->token);
			//判断是否子账号，并进行权限判定，主意排除Tongji模块，避免无法访问首页
			if(session('is_wxsub') && !(GROUP_NAME=="User" && MODULE_NAME=="Tongji")){
				$sub_access = M('wxuser_sub_access')->where(array("uid"=>session('sub_uid')))->find();//读取权限信息
				$rules = unserialize($sub_access['access']);//反序列化权限数组并做小写转换
				if(GROUP_NAME != "User" || !isset($rules[MODULE_NAME])){
					$this->assign("error","你没有权限查看该功能");
					$this->display("Public/deny");
					exit;
				}
				//检查当前操作是否被禁止
				$denyrules = $rules[MODULE_NAME];
				if(in_array(strtolower(ACTION_NAME),array_map("strtolower",$denyrules))){
					$this->assign("error","你没有该操作权限");
					$this->display("Public/deny");
					exit;
				}
			}
		}
		

		//所属行业
		$this->trade = $users['trade'];
		$this->assign('trade',$this->trade);


	}
}
