<?php
//会员卡模块，店铺前台页面
class MemberCardShopUserAction extends Action{
	public $card_info;
	public $token;
	public $shop_id;
	public function _initialize() {
		C('TOKEN_ON',false);
		//检测登录
		if(!session('MemberCardShopLogin') && (ACTION_NAME!='login') && (ACTION_NAME!='logout')){
			$this->redirect('login',array('token'=>$this->_get('token')));
			exit;
		}
		$this->token = session('token');
		$this->shop_id = session('MemberCardShopId');
	}
	
	//用户首页
	public function index(){
		$list=M('Userinfo')->where(array('token'=>$this->token))->select();
		
		if(IS_POST){
			 
			$key = $this->_post('searchkey');
			if(empty($key)){
				exit("关键词不能为空.");
			}
			$map['token'] = $this->token; 
			$map['tel|wechaname'] = array('like',"%$key%"); 
			$list = M('Userinfo')->where($map)->select();
			 
			 
		}
		$this->assign('list',$list);
		
		
		$this->display();
	}
	
	public function info(){

		$token=$data['token']=$this->token;
		$data['id']=$this->_get('id');
		$info=M('Userinfo')->where($data)->find();
		$wecha_id = $info['wecha_id'];
		
        if(IS_POST){
            $score = $this->_post('score');//消费的积分
			$vipaddrmb = $this->_post('vipaddrmb');//会员卡充值数量
			$vipspendrmb = $this->_post('vipspendrmb');//会员卡余额消费
			$spendrmb = $this->_post('spendrmb');//现金消费
            //获取商家设置 tp_member_card_exchange
			$exchange = M('Member_card_exchange');
			$getset = $exchange->where(array('token'=>$token))->find();
			//积分初始化
			$rmb_total = $info['rmb_total'];
			$score_total = $info['total_score'];
			$score_expend = $info['expend_score'];
			$rmb_expend = $info['rmb_expend'];
            
			
            //会员卡消费
			if(is_numeric($vipspendrmb) && $vipspendrmb>0){
				unset($data);
				$data['token']    = $token;                                                                   
				$data['wecha_id'] = $wecha_id;                                                                
				$data['sign_time'] = time();
				$data['score_type'] = 2;//1签到 2消费rmb 3消费积分                                                                   
				$data['is_sign'] = 1;    
				$data['shop_id'] = $this->shop_id;//店铺id
				$data['expense']  = ceil($vipspendrmb*$getset['reward']);//积分奖励
				$data['sell_expense'] = -$vipspendrmb; //金额变化，负数表示消费                                         
				//var_dump($data);exit;                                                                       
				$back = M('Member_card_sign')->data($data)->add();//写入积分变化
				//更新用户积分                                                                                      
				$rmb_total = $rmb_total - $vipspendrmb;//rmb计数器增减
				$score_total = $score_total+ceil($vipspendrmb*$getset['reward']);//总积分变化
				$rmb_expend = $rmb_expend + $vipspendrmb;
			}
			
			//现金消费
			if(is_numeric($spendrmb) && $spendrmb>0){
				unset($data);
				$data['token']    = $token;                                                                   
				$data['wecha_id'] = $wecha_id;                                                                
				$data['sign_time'] = time();
				$data['score_type'] = 2;//1签到 2消费rmb 3消费积分                                                                   
				$data['is_sign'] = 1;
				$data['shop_id'] = $this->shop_id;//店铺id
				$data['expense']  = ceil($spendrmb*$getset['reward']);//积分奖励
				$data['sell_expense'] = -$spendrmb; //金额变化，负数表示消费                                         
				//var_dump($data);exit;                                                                       
				$back = M('Member_card_sign')->data($data)->add();//写入积分变化
				//更新用户积分                                                                                      
				
				$score_total = $score_total+ceil($spendrmb*$getset['reward']);//总积分变化
				$rmb_expend = $rmb_expend + $spendrmb;                                                                                 
			}
			$da['rmb_total'] = $rmb_total;//以计数器为准
			$da['total_score'] = $score_total;//新的总积分
			$da['expend_score'] = $score_expend;
			$da['rmb_expend'] = $rmb_expend;
			$back2 = M('Userinfo')->where(array('token'=>$token,'wecha_id'=>$wecha_id))->save($da);
			member_upviplevel($token,$wecha_id);//更新用户会员卡等级
			$this->success("ok");
			exit;
        }   
		
		$this->assign('info',$info);
		
		$cardsign   = M('Member_card_sign');  //签到表 
		$where    = array('token'=>$data['token'],'wecha_id'=>$info['wecha_id'],'shop_id'=>$this->shop_id);  
        $record = $cardsign->where($where)->order('sign_time desc')->select();
		
	
		$this->assign('record',$record);
		//var_dump($tbsign);
		$this->display();
	}
	
	function login(){
		if(!IS_POST){
			$this->display();
		}else{
			$M = M('member_card_shop');
			$token = $this->_post('token');
			$username = $this->_post('username');
			$password = $this->_post('password','md5');
			if(empty($token) || empty($username) || empty($password)){
				$this->error('登陆失败：token、用户名、密码不能为空');
			}
			$user = $M->where(array('token'=>$token,'username'=>$username,'password'=>$password))->find();
			if(empty($user)){
				$this->error('登录失败：用户名或密码错误');
			}else{
				session('MemberCardShopLogin',1);
				session('token',$token);
				session('MemberCardShopId',$user['id']);
				session('MemberCardShopInfo',$user);
				$this->success('登录成功',U('index'));
			}
		}
		
	}
	
	function logout(){
		session('MemberCardShopLogin',null);
		//session('token',null);
		session('MemberCardShopId',null);
		session('MemberCardShopInfo',null);
		$this->success('注销成功',U('login'));
	}
}
