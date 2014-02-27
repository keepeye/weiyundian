<?php
/**
* 礼品兑换处理模块
*
*/
class IntegralAction extends UserAction{
	public function _initialize() {
		parent::_initialize();
		$this->token=session('token');
		$this->assign('token',$this->token);
		//权限
		if ($this->token!=$_GET['token']){
			//exit();
		}
		$this->wxuser_db=M("Wxuser");
		//获取所在组的开卡数量
		$thisWxUser=$this->wxuser_db->where(array('token'=>$this->token))->find();
		$thisUser=M("Users")->where(array('uid'=>$thisWxUser['uid']))->find();
		$thisGroup=M("User_group")->where(array('id'=>$thisUser['gid']))->find();
		$this->wxuser_db->where(array('token'=>$this->token))->save(array('allcardnum'=>$thisGroup['create_card_num']));
		$can_cr_num = $thisWxUser['allcardnum'] - $thisWxUser['yetcardnum'];
		if($can_cr_num > 0){
			$data['cardisok'] = 1;
			$this->wxuser_db->where(array('uid'=>session('uid'),'token'=>session('token')))->save($data);
		}
		C('TOKEN_ON',false);
	}
	
	//显示申请列表
	function lists(){
		import("@.ORG.Page");
		
		$M = M('member_card_integral_exchange');//申请表模型
		$token = $this->token;
		if(!$token){
			$this->error("非法访问");
		}
		$where = array('a.token'=>$token);
		if($sn = $this->_get('sn')){
			$where['a.sn'] = array("like",$sn.'%');
		}
		$total = $M->alias('a')->where($where)->count();
		$page = new Page($total,20);
		$pagestr = $page->show();
		$list = $M->alias('a')->field('a.*,b.title')->join("__MEMBER_CARD_INTEGRAL__ AS b ON b.id = a.integral_id")->where($where)->order("donetime ASC,applytime DESC")->limit($page->firstRow,$page->listRows)->select();
		$this->assign('list',$list);
		$this->assign('pagestr',$pagestr);
		//dump($list);
		$this->display();
	}
	
	//处理订单
	function process(){
		$id = $this->_post('id');
		$token = $this->token?$this->token:$this->_post('token');
		if(!$id || !$token){
			$this->error('未指定id或token');
		}
		$M = M('member_card_integral_exchange');
		$re = $M->where(array('id'=>$id,'token'=>$token))->data(array('donetime'=>time()))->save();
		if($re === false){
			$this->error("更新订单状态失败，请联系微云店技术");
		}else{
			$this->success("ok");
		}
	}
	
}