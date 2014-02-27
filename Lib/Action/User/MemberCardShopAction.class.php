<?php
//会员卡商家管理---商场店铺子账号；开启子帐号后，订单消费需要登录子帐号。
//session：membercard_shopadmin 商铺账号管理员  membercard_shopid 商铺id   membercard_shopname 商铺名
class MemberCardShopAction extends UserAction{
	public $card_info;
	public function _initialize() {
		parent::_initialize();
		$this->token=session('token');
		if(!$this->token){
			$this->error("请先登陆");
		}
		$this->assign('token',$this->token);
		
		//读取开关状态
		$card_info = M('member_card_info')->field('*')->where(array('token'=>$this->token))->find();
		if(!$card_info){
			$this->error('请先到商家设置里设置基本信息');
		}
		$this->card_info = $card_info;//用类成员保存会员卡基本信息
		$this->assign('card_info',$card_info);
		C('TOKEN_ON',false);
	}
	
	function index(){
		import('@.ORG.Page');
		$where = array('token'=>$this->token);
		if(!empty($_GET['searchkey'])){
			$where['shopname'] = array('like',"%{$_GET['searchkey']}%");
		}
		$total = M('member_card_shop')->where($where)->count();
		$Page = new Page($total,30);
		$pagestr = $Page->show();// 分页显示输出
		$list = M('member_card_shop')->where($where)->limit($Page->firstRow.','.$Page->listRows)->select();
		$this->assign('list',$list);
		$this->assign('pagestr',$pagestr);
		$this->display();
	}
	
	
	
	
	//商铺用户登录
	function shopLogin(){
	
	}
	
	//商铺用户注销
	function shopLogout(){
		session('membercard_shopid',null);
		session('membercard_shopname',null);
	}
	
	//添加、修改商铺账号
	function setShop(){
		if(!IS_POST){
			if(isset($_GET['id']) && !empty($_GET['id'])){
				$shop_info = M('member_card_shop')->where(array('token'=>$this->token,'id'=>(int)$_GET['id']))->find();
				if(!empty($shop_info)){
					$this->assign('shop_info',$shop_info);
				}
			}
			$this->display();
		}else{
			if($this->token != $_POST['token']){
				$this->error('非法提交');
			}
			$isUpdate = !empty($_POST['id'])?true:false;//判断是添加还是更新
			$M = D('MemberCardShop');//实例化高级模型，使用自动验证以及自动完成
			if($M->create()){
				if($isUpdate){
					$re = $M->save();
					$errText = '数据保存失败';
				}else{
					$re = $M->add();
					$errText = '数据添加失败';
				}
				if($re !== false){
					$this->success('ok',U('index'));
				}else{
					$this->error($errText);
				}
			}else{
				$this->error($M->getError());
			}
		}
	}
	
	//删除商铺账号
	function delShop(){
		if(isset($_GET['id']) && !empty($_GET['id'])){
			M('member_card_shop')->where(array('token'=>$this->token,'id'=>(int)$_GET['id']))->delete();
		}
		$this->success('删除成功');
	}
	
	
}
