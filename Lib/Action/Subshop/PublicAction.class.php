<?php
class PublicAction extends Action{
	public $token;
	function _initialize(){
		
	}
	function login(){
		$token = $_GET['token'];
		if(!$token){
			$this->error('请指定token');
		}
		
		if(!IS_POST){
			$accounts = M('Subshop')->where(array('token'=>$token))->select();
			$this->assign('token',$token);
			$this->assign('accounts',$accounts);
			$this->display();
		}else{
			$id = $_POST['username'];
			$passwd = $_POST['passwd'];
			$account = M('Subshop')->where(array('token'=>$token,'id'=>$id))->find();
			if(!$account){
				$this->error('门店账号不存在');
			}
			if($account['passwd'] != $passwd){
				$this->error('密码错误');
			}
			//检测商户有效期
			
			//写入门店id和门店名
			session('shopid',$account['id']);
			session('shopname',$account['shopname']);
			//商户token
			session('token',$account['token']);
			$this->redirect('Index/index');
		}
	}
	
	function logout(){
		session(null);//请勿使用session_destroy()会导致其他分组session也被清空
		$this->redirect('Home/Index/index');
	}
	
}