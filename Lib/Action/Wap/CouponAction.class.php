<?php
class CouponAction extends BaseAction{
	private $token;
	private $wecha_id;
	private $wxsign;
	function _initialize(){
		parent::_initialize();
		//基础信息
		$this->token		= $this->_get('token');
		$this->wecha_id	= I('request.wecha_id');//获取wecha_id
		$this->wxsign = I('wxsign',I('get.wxsign'));//获取加密字符串
		if($this->wecha_id == "" || md5($this->token.$this->wecha_id.C('safe_key'))!=$this->wxsign){
			$this->redirect("Home/Adma/index?token=".$token);
		}

		$this->assign("token",$this->token);
		$this->assign("wecha_id",$this->wecha_id);
		$this->assign("wxsign",$this->wxsign);
		//$this->error('该功能重构中...');
	}

	//优惠券首页
	public function index(){
		$id = I('get.id',0,'intval');
		if($id <= 0){
			$this->error("非法请求");
		}
		//获取活动信息
		$CouponM = M('Coupon');
		$coupon = $CouponM->where(array("id"=>$id,"token"=>$this->token))->find();
		if(!$coupon){
			$this->error("活动已被删除");
		}
		//判断活动是否开启
		if($coupon['status'] != 1){
			$this->error("活动已被关闭");
		}
		$nowtime = time();
		if($nowtime < $coupon['start_time']){
			$this->error("活动尚未开始");
		}
		if($nowtime > $coupon['end_time']){
			$this->error("活动已结束");
		}
		$this->assign("coupon",$coupon);
		$this->display();
	}
	
	//提交联系人信息
	public function add(){
		$return = array();
		$CouponRecordM = M('CouponRecord');
		$id = I("post.id");//活动id		
		$map = array(
				"id"=>$id,
				"token"=>$this->token
			);
		$coupon = M('Coupon')->where($map)->find();
		if(!$coupon){
			$return = array(
					"status"=>"0",
					"info"=>"活动已被删除"
				);
			$this->ajaxReturn($return);
		}

		//优惠券已领完
		if($coupon['given_num'] >= $coupon['num']){
			$return = array(
					"status"=>"0",
					"info"=>"啊，来迟一步，优惠券已被领完，请关注下次活动"
				);
			$this->ajaxReturn($return);
		}

		//判断活动是否关闭
		if($coupon['status'] != 1){
			$this->error("活动已被关闭");
		}
		
		$record = $CouponRecordM->where(array("wecha_id"=>$this->wecha_id))->find();
		if($record){
			$this->error("你已经领取过优惠券");
		}

		//生成优惠券
		$sn = $data['sn'] = uniqid();
		$data['pid'] = $id;
		$data['wecha_id'] = $this->wecha_id;
		$data['time'] = time();
		$data['contact_name'] = I("post.name");
		$data['contact_phone'] = I("post.phone");
		$data['contact_weixin'] = I("post.weixin");
		if($CouponRecordM->add($data)){
			$this->ajaxReturn(array("status"=>"1","sn"=>$sn));
		}else{
			$this->error("优惠码已被领取完了。。");
		}

	}
}
