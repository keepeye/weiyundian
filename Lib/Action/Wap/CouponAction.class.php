<?php
class CouponAction extends BaseAction{
	private $token;
	private $wecha_id;
	function _initialize(){
		parent::_initialize();
		$this->token		= $this->_get('token');
		$this->wecha_id	= $this->_get('wecha_id') || cookie('wecha_id');
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
		 if($_POST['action'] ==  'add' ){
			$lid 				= $this->_post('lid');
			$wechaid 			= $this->_post('wechaid');
			$data['sn']			= $this->_post('sncode');
			$data['phone'] 		= $this->_post('tel');
			$data['prize']		= $this->_post('winprize');
			$data['wecha_name'] = $this->_post('wxname');
			$data['time']		= time(); 
			$data['islottery']	= 1;
			$data['usenums']	= 1;			

			$rollback = M('Lottery_record')->where(array('lid'=> $lid,
				'wecha_id'=>$wechaid))->save($data);
			
			echo'{"success":1,"msg":"恭喜！尊敬的<font color=red>'.$data['wecha_name'].'</font>请您保持手机通畅！你的领奖序号:<font color=red>'.$data['sn'].'</font>"}';
			exit;	
		}
	}
}
	
?>
