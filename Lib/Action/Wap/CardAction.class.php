<?php
class CardAction extends BaseAction{
	public function index(){
		$agent = $_SERVER['HTTP_USER_AGENT']; 
		if(!strpos($agent,"MicroMessenger")) {
			//echo '此功能只能在微信浏览器中使用';exit;
		}
		$token=$this->_get('token');
		if($token!=false){
			$data=M('member_card_set')->where(array('token'=>$token))->find();
			$this->assign('card',$data);
		}else{
			$this->error('无此信息');
		}
		$this->display();	
    }
    
	public function request(){
		$agent = $_SERVER['HTTP_USER_AGENT']; 
		if(!strpos($agent,"MicroMessenger")) {
	//		echo '此功能只能在微信浏览器中使用';exit;
		}
		$token=$this->_get('token');
		if($token!=false){
			//会员卡信息
			$data=M('member_card_set')->where(array('token'=>$token))->find();
			//商家信息
			$info=M('member_card_info')->where(array('token'=>$token))->find();
			//卡号
			$card=M('member_card_create')->where(array('token'=>$token))->order('id asc')->find();
			//联系方式
			$contact=M('member_card_contact')->where(array('token'=>$token))->order('sort desc')->find();
			$this->assign('card',$data);
			$this->assign('card_info',$card);
			$this->assign('contact',$contact);
			$this->assign('info',$info);			
		}else{
			$this->error('无此信息');
		}
		$this->display();	
    }

	
	
	
	public function get_card(){
		$agent = $_SERVER['HTTP_USER_AGENT']; 
		if(!strpos($agent,"MicroMessenger")) {
		//	echo '此功能只能在微信浏览器中使用';exit;
		}
		$token=$this->_get('token');
		$wecha_id	= $this->_get('wecha_id') || cookie('openid');
		if($wecha_id == ""){
			$this->redirect("Home/Adma/index?token=".$token);
		}
		$get_card=M('member_card_create')->where(array('wecha_id'=>$wecha_id))->find();

		if($get_card!=false){
			Header("Location: ".C('site_url').'/'.U('Wap/Card/vip',array('token'=>$this->_get('token'),'wecha_id'=>$this->_get('wecha_id')))); 
		}
		if($token!=false){
			//会员卡信息
			$data=M('member_card_set')->where(array('token'=>$token))->find();
			//商家信息
			$info=M('member_card_info')->where(array('token'=>$token))->find();
			//联系方式
			$contact=M('member_card_contact')->where(array('token'=>$token))->order('sort desc')->find();
			$this->assign('card',$data);
			$this->assign('card_info',$card);
			$this->assign('contact',$contact);
			$this->assign('info',$info);
		}else{
			$this->error('无此信息');
		}
		$this->display();	
    }
	
	
	public function info(){
		$agent = $_SERVER['HTTP_USER_AGENT']; 
		if(!strpos($agent,"MicroMessenger")) {
			//echo '此功能只能在微信浏览器中使用';exit;
		}
		$token=$this->_get('token');
		if($token!=false){
			//会员卡信息
			$data=M('member_card_set')->where(array('token'=>$token))->find();
			//商家信息
			$info=M('member_card_info')->where(array('token'=>$token))->find();
			//联系方式
			$contact=M('member_card_contact')->where(array('token'=>$token))->order('sort desc')->find();
			//我的卡号
			$mycard=M('member_card_create')->where(array('token'=>$this->_get('token'),'wecha_id'=>$this->_get('wecha_id')))->find();
			//会员卡积分策略和使用说明
			$exchange=M('MemberCardExchange')->where(array('token'=>$token))->find();
			
			$this->assign('mycard',$mycard);
			$this->assign('card',$data);
			$this->assign('card_info',$card);
			$this->assign('contact',$contact);
			$this->assign('info',$info);
			$this->assign('exchange',$exchange);
			
		}else{
			$this->error('无此信息');
		}
		$this->display();	
    }
	
	public function vip(){
	   $agent = $_SERVER['HTTP_USER_AGENT']; 
		if(!strpos($agent,"MicroMessenger")) {
			//echo '此功能只能在微信浏览器中使用';exit;
		}
		$token=$this->_get('token');
		$wecha_id	= $this->_get('wecha_id') || cookie('openid');
		if($wecha_id == ""){
			$this->redirect("Home/Adma/index?token=".$token);
		}
		 
		if($token!=false){
			//会员卡信息
			$data=M('member_card_set')->where(array('token'=>$token))->find();
			//商家信息
			$info=M('member_card_info')->where(array('token'=>$token))->find();
			//卡号
			$card=M('member_card_create')->where(array('token'=>$token,'wecha_id'=>$this->_get('wecha_id')))->find();
			//var_dump($card);exit;
			//dump(array('token'=>$token,'wecha_id'=>$this->get('wecha_id')));
			//联系方式
			$contact=M('member_card_contact')->where(array('token'=>$token))->order('sort desc')->find();
			$this->assign('card',$data);
			$this->assign('card_info',$card);
            
            $type_arr=array("普通卡","银卡","金卡","钻石卡");
			$this->assign('member_level',$type_arr[$card['card_type']]);
			$this->assign('contact',$contact);
			$this->assign('info',$info);			
			$data=M('member_card_set')->where(array('token'=>$token))->find();
			//dump($data);
			$this->assign('card',$data);
			//用户信息
			$userinfo = M('Userinfo')->where(array('token'=>$this->_get('token'),'wecha_id'=>$this->_get('wecha_id')))->find();
			$this->assign('userinfo',$userinfo);
			
		}else{
			$this->error('无此信息');
		}
	
		$this->display();
	
	}
	
	//特权信息
	function viewGroup(){
		$agent = $_SERVER['HTTP_USER_AGENT']; 
		if(!strpos($agent,"MicroMessenger")) {
			//echo '此功能只能在微信浏览器中使用';exit;
		}
		$token=$this->_get('token');
		$wecha_id=$this->_get('wecha_id');
		if($token!=false){
			$member=M('Member')->where(array('token'=>$token))->find();
			if($memboer['hoempic']!=false){
				$img=$member['homepic'];			
			}else{
				$img='tpl/Wap/default/common/images/userinfo/fans.jpg';
			}
			$this->assign('homepic',$img);
			//卡号
			$card=M('member_card_create')->where(array('token'=>$token,'wecha_id'=>$this->_get('wecha_id')))->find();
			//特权服务
            $group_number = $card['card_type'] + 1;
			$viplist=M('member_card_vip')->where(array('token'=>$token,'group'=>array(array('eq',$group_number),array('eq','0'),'or')))->order('id desc')->select();
			
			$this->assign('viplist',$viplist);
			
		}else{
			$this->error('无此信息');
		}
	
		$this->display();
	}
	//优惠券
	function viewCoupon(){
		$agent = $_SERVER['HTTP_USER_AGENT']; 
		if(!strpos($agent,"MicroMessenger")) {
			//echo '此功能只能在微信浏览器中使用';exit;
		}
		$token=$this->_get('token');
		$wecha_id=$this->_get('wecha_id');
		if($token!=false){
			$member=M('Member')->where(array('token'=>$token))->find();
			if($memboer['hoempic']!=false){
				$img=$member['homepic'];			
			}else{
				$img='tpl/Wap/default/common/images/userinfo/fans.jpg';
			}
			$this->assign('homepic',$img);
			//卡号
			$card=M('member_card_create')->where(array('token'=>$token,'wecha_id'=>$this->_get('wecha_id')))->find();
			
			
            $group_number = $card['card_type'] + 1;
			
			//优惠卷
			$couponlist=M('member_card_coupon')->where(array('token'=>$token,'group'=>array(array('eq',$group_number),array('eq','0'),'or')))->select();
			$this->assign('couponlist',$couponlist);
			
		}else{
			$this->error('无此信息');
		}
	
		$this->display();
	}
	//礼品卡
	function viewIntegral(){
		$agent = $_SERVER['HTTP_USER_AGENT']; 
		if(!strpos($agent,"MicroMessenger")) {
			//echo '此功能只能在微信浏览器中使用';exit;
		}
		$token=$this->_get('token');
		$wecha_id=$this->_get('wecha_id');
		if($token!=false){
			$member=M('Member')->where(array('token'=>$token))->find();
			if($memboer['hoempic']!=false){
				$img=$member['homepic'];			
			}else{
				$img='tpl/Wap/default/common/images/userinfo/fans.jpg';
			}
			$this->assign('homepic',$img);
			//卡号
			$card=M('member_card_create')->where(array('token'=>$token,'wecha_id'=>$this->_get('wecha_id')))->find();
            $group_number = $card['card_type'] + 1;
			
			//兑换
			$integrallist=M('member_card_integral')->where(array('token'=>$token,'enddate'=>array('gt',time())))->select();
			
			$this->assign('integrallist',$integrallist);
		}else{
			$this->error('无此信息');
		}
	
		$this->display();
	}
	//礼品兑换
	function exchange(){
		$token=$this->_get('token');//商户token
		$wecha_id=$this->_get('wecha_id');//用户id
		if(!$wecha_id){
			$this->error("用户不存在");
		}
		$integral_id = $this->_get('integral_id');//礼品id
		$lipin = M('member_card_integral')->field('id,title,info,integral')->where(array('id'=>$integral_id,'token'=>$token,'enddate'>time()))->find();//读取礼品信息
		if(!$lipin){
			$this->error('礼品已下架或不存在');
		}
		
		
		if(!IS_AJAX){
			$this->assign('lipin',$lipin);
			$this->display();
		}else{
			if(!$this->_get('contact_name') || !$this->_get('contact_tel')){
				$this->error("请填写联系人和联系电话");
			}
			$userinfo = M('userinfo')->field("total_score")->where(array('wecha_id'=>$wecha_id))->find();//读取用户信息
			if(($userinfo['total_score'] - $lipin['integral']) < 0){//用户积分不足，提示错误
				$this->error('您的积分不足，无法兑换该礼品，该礼品需要积分: '.$lipin['integral'].' 您的积分：'.$userinfo['total_score']);
			}
			
			//记录兑换记录，生成sn号码
			$data = array();
			$data['integral_id'] = $integral_id;
			$data['token'] = $token;
			$data['wecha_id'] = $wecha_id;
			$data['sn'] = $sn = uniqid();//使用php生成唯一的id字符串
			$data['applytime'] = time();//申请时间自动生成
			$data['donetime'] = 0;//处理时间，默认为0，表示待处理
			$data['contact_name'] = $this->_get('contact_name');
			$data['contact_tel'] = $this->_get('contact_tel');
			if(M('member_card_integral_exchange')->data($data)->add()){
				//扣除积分
				M('userinfo')->where(array('wecha_id'=>$wecha_id))->setDec('total_score',$lipin['integral']);
				$this->success("兑换成功，请记下sn号码: ".$sn);
			}else{
				$this->error("兑换失败，请联系客服");
			}
		}
	}
	
	public function addr(){
	$agent = $_SERVER['HTTP_USER_AGENT']; 
		if(!strpos($agent,"MicroMessenger")) {
			//echo '此功能只能在微信浏览器中使用';exit;
		}
	
		$token=$this->_get('token');
		if($token!=false){
			//会员卡信息
			$data=M('member_card_set')->where(array('token'=>$token))->find();
			//商家信息
			//$addr=M('member_card_contact')->where(array('token'=>$token))->select();
			//if (!$addr){
			$addr=M('Company')->where(array('token'=>$token))->order('isbranch ASC')->select();
			if ($addr){
				$i=0;
				foreach ($addr as $a){
					$addr[$i]['info']=$a['address'];
					$addr[$i]['tel']=$a['tel'];
					$i++;
				}
			}
			//}
			//联系方式
			$contact=M('member_card_contact')->where(array('token'=>$token))->order('sort desc')->find();
			//我的卡号
			$mycard=M('member_card_create')->where(array('token'=>$this->_get('token'),'wecha_id'=>$this->_get('wecha_id')))->find();
			$this->assign('mycard',$mycard);
			$this->assign('card',$data);
			$this->assign('card_info',$card);
			$this->assign('contact',$contact);
			$this->assign('addr',$addr);
		}else{
			$this->error('无此信息');
		}
		$this->display();
	
	}
}
?>
