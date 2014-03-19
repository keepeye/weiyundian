<?php
class PublicAction extends Action{
	//子账号登录
	function subLogin(){
		$token = I('token','');
		if(!$token){
			$this->error("请从正确的入口登陆");
		}
		if(!IS_POST){
			$this->assign("token",$token);
			$this->display();
		}else{
			$username = I('username','','trim');//用户提交的用户名
			$passwd = I('passwd','','trim');//用户提交的密码
			$map = array(
				"token"=>$token,
				"username"=>$username,
				"passwd"=>md5($passwd)
				);
			$SubUser = M('WxuserSub')->where($map)->find();
			if(!$SubUser){
				$this->error("用户名或密码错误");
			}
			//读取wxuser信息
			$Wxuser = M('Wxuser')->field('id,uid')->where(array("token"=>$token))->find();
			if(!$Wxuser){
				$this->error("token不存在");
			}
			//读取user信息
			$user = M('Users')->where(array("id"=>$Wxuser['uid']))->find();
			if(!$user){
				$this->error("商户账号不存在");
			}

			//开始写入session
			session('uid',$user['id']);
			session('gid',$user['gid']);
			session('uname',$user['username']);
			$group=M('user_group')->find($user['gid']);//查询用户组信息
			session('diynum',$user['diynum']);
			session('connectnum',$user['connectnum']);
			session('activitynum',$user['activitynum']);
			session('viptime',$user['viptime']);
			session('gname',$group['name']);
			//写入子账户标识
			session("is_wxsub",1);
			session("sub_username",$SubUser['username']);
			session("sub_uid",$SubUser['id']);
			//跳转到微信首页
			$this->redirect("User/Tongji/index",array("id"=>$Wxuser['id'],"token"=>$token));//http://weiyundian.xici.net/index.php?g=User&m=Tongji&a=index&id=49&token=xici2013
 		}
	}
}