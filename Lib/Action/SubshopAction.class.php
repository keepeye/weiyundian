<?php
class SubshopAction extends BaseAction{
	public $token;//商户token
	public $shopid;//门店账户id
	public $shopname;//门店名
	function _initialize(){
		//读取会话信息
		$this->token = session('token');
		$this->shopid = session('shopid');
		$this->shopname = session('shopname');
		//检测登录状态
		if(!$this->checklogin()){
			$this->error('您尚未登录，请从后台门店管理地址进行登录');
			exit;
		}
		//设置商户token和门店账户id的模板变量
		$this->assign('token',$this->token);
		$this->assign('shopid',$this->shopid);
		$this->assign('shopname',$this->shopname);
	}
	
	protected function checklogin(){
		if(!$this->token || !$this->shopid){
			return false;
		}
		return true;
	}
	
	//获取商户详细信息
	protected function getCustom(){
		
	}
	
	//获取门店账户详细信息
	protected function getSubshop(){
	
	}
}