<?php
class IndexAction extends BaseAction{
	//关注回复
	public function index(){
		
		$this->display();
	}
	public function resetpwd(){
		$uid=$this->_get('uid','intval');
		$code=$this->_get('code','trim');
		$rtime=$this->_get('resettime','intval');
		$info=M('Users')->find($uid);
		if( (md5($info['uid'].$info['password'].$info['email'])!==$code) || ($rtime<time()) ){
			$this->error('非法操作',U('Index/index'));
		}
		$this->assign('uid',$uid);
		$this->display();
	}
	//代理申请
	function merchants(){
		if(!IS_POST){
			$this->display();
		}else{
			$M = M('merchants');
			$_auto = array(
				array('adddate','getDateTime',1,'function')
			);
			$M->setProperty("_auto",$_auto);//设置自动完成
			if($M->create()){
				if($M->add()){
					$this->success('提交成功',U('Index/index'));
				}else{
					$this->error('提交失败');
				}
			}else{
				$this->error('提交失败');
			}
		}
	}
	//意见反馈
	function suggest(){
		if(!IS_POST){
			$this->display();
		}else{
			$M = M('suggest');
			$_auto = array(
				array('adddate','getDateTime',1,'function')
			);
			$M->setProperty("_auto",$_auto);//设置自动完成
			if($M->create()){
				if($M->add()){
					$this->success('提交成功',U('Index/index'));
				}else{
					$this->error('提交失败');
				}
			}else{
				$this->error('提交失败');
			}
		}
	}
	//资费说明
	function price(){
		$this->success("敬请期待");
	}
}