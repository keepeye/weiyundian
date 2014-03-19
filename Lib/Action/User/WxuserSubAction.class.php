<?php
class WxuserSubAction extends UserAction{

	function index(){
		//读取子账户列表
		$list = M('WxuserSub')->where(array("token"=>$this->token))->select();
		$this->assign("list",$list);
		$this->display();
	}
	//添加用户
	function addUser(){
		if(!IS_POST){
			$this->display();
		}else{
			$username = I('username','','trim');
			$passwd = I('passwd','','trim');
			if(empty($username) || empty($passwd)){
				$this->error("用户名或密码不能为空");
			}
			if(!preg_match('/^[a-z0-9_]{1,30}$/is',$username)){
				$this->error("用户名不合法");
			}

			//检查用户是否已经存在
			if(M('WxuserSub')->where(array("token"=>$this->token,"username"=>$username))->find()){
				$this->error("用户名已存在，请更换用户名");
			}
			$passwd = md5($passwd);//密码加密
			$data=array(
				"token"=>$this->token,
				"username"=>$username,
				"passwd"=>$passwd
				);
			$re = M('WxuserSub')->add($data);
			if($re!==false){
				$this->success("添加成功",U('index'));
			}else{
				$this->error("添加失败");
			}

		}
	}

	//修改用户
	function editUser(){
		$id = I('id',0,'intval');
		$info = M('WxuserSub')->where(array("id"=>$id,"token"=>$this->token))->find();
		if(!$info){
			$this->error("非法请求");
		}
		if(!IS_POST){
			$this->assign("info",$info);
			$this->display();
		}else{
			$passwd = I('passwd','','trim');
			if(empty($passwd)){
				$this->error("密码不能为空");
			}
			$data=array(
				"id"=>$id,
				"token"=>$this->token,
				"passwd"=>md5($passwd)
				);
			if(false !== M('WxuserSub')->save($data)){
				$this->success("设置成功");
			}else{
				$this->error("设置失败,数据库错误");
			}
		}
	}
	//设置权限
	function setAccess(){
		$uid = I('id',0,'intval');
		if(!$uid){
			$this->error("请指定子账号");
		}
		$user = M('WxuserSub')->where(array("id"=>$uid,"token"=>$this->token))->find();
		if(!$user){
			$this->error("子账号不存在");
		}
		if(!IS_POST){
			
			$this->assign("user",$user);
			//读取权限设置
			$user_access = M('WxuserSubAccess')->field("access")->where(array("uid"=>$uid))->find();
			$access = unserialize($user_access['access']);//反序列化
			$this->assign("access",$access);
			$this->display();
		}else{
			$access = $_POST['access'];
			$deny = $_POST['deny'];
			$rule=array();
			foreach($access as $v){
				$rule[$v]=(array)$deny[$v];
			}
			if(empty($rule)){
				$this->error("请指定权限再保存");
			}
			M('WxuserSubAccess')->where(array("uid"=>$uid))->delete();//删除旧规则
			$data = array(
				"uid"=>$uid,
				"access"=>serialize($rule)//规则序列化
				);
			if(false !== M('WxuserSubAccess')->add($data)){
				$this->success("设置成功");
			}else{
				$this->error("设置失败，请重试");
			}
			
		}
	}

	//删除用户
	function del(){

	}

}