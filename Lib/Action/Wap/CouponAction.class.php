<?php
class CouponAction extends BaseAction{
	private $token;
	private $wecha_id;
	function _initialize(){
		parent::_initialize();
		$this->token		= I('request.token');
		$this->wecha_id	= I('request.wecha_id') || cookie('wecha_id');
		if($this->wecha_id == ""){
			$this->redirect("Home/Adma/index?token=".$token);
		}
		$this->assign("token",$this->token);
		$this->assign("wecha_id",$this->wecha_id);
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
	
	//中奖后填写信息
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

		//判断活动是否关闭
		if($coupon['status'] != 1){
			$this->error("活动已被关闭");
		}
		
		$record = $CouponRecordM->where(array("wecha_id"=>$this->wecha_id))->find();
		if($record){
			$this->error("你已经领取过优惠券");
		}

		//生成优惠券
		$data['sn'] = uniqid();
		$data['pid'] = $id;
		$data['wecha_id'] = $this->wecha_id;
		$data['time'] = time();
		$data['contact_name'] = I("post.name");
		$data['contact_phone'] = I("post.phone");
		$data['contact_weixin'] = I("post.weixin");
		if($CouponRecordM->add($data)){
			$this->ajaxReturn(array("status"=>"1","sn"=>$sn));
		}else{
			$this->error("优惠码已被领取完了。。".$CouponRecordM->getDbError());
		}

	}
}
