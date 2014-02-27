<?php
class MemberAction extends UserAction{
	public function index(){
		$sql=M('Member');
		$data['token']=$this->_get('token');
		$data['uid']=session('uid');
		$member=$sql->field('homepic')->where($data)->find();
		$this->assign('member',$member);
		$list=M('Userinfo')->where(array('token'=>$data['token']))->select();
		
		if(IS_POST){
			 
			$key = $this->_post('searchkey');
			if(empty($key)){
				exit("关键词不能为空.");
			}
			$map['token'] = $this->get('token'); 
			$map['tel|wechaname'] = array('like',"%$key%"); 
			$list = M('Userinfo')->where($map)->select();
			 
			 
		}
		$this->assign('list',$list);
		
		$tbsign = M('Member_card_sign')->where(array('token'=>$data['token']))->select();
		$this->assign('tbsign',$tbsign);
		//var_dump($tbsign);
		$this->display();
	}
	
	public function info(){

		$token=$data['token']=$this->_get('token');
		$data['uid']=session('uid');
		$data['id']=$this->_get('id');
        
		
		$info=M('Userinfo')->where($data)->find();
		$wecha_id = $info['wecha_id'];
		
		
		//积分兑换列表
		$integrallist=M('member_card_integral')->where(array('token'=>$token,'enddate'=>array('gt',time())))->select();
		$this->assign('integrallist',$integrallist);
        if(IS_POST){
			$token = $this->_get('token');//商户id
			
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
            if(is_numeric($score) && $score>0){   
				unset($data);
				$data['token']    = $token;                                                                   
				$data['wecha_id'] = $wecha_id;                                                                
				$data['sign_time'] = time();
				$data['score_type'] = 3;//1签到 2消费rmb 3消费积分                                                                   
				$data['is_sign'] = 0;                                                                      
				$data['expense']  = -$score;
				$data['sell_expense'] = 0; //消费金额                                               
				//var_dump($data);exit;                                                                       
				$back = M('Member_card_sign')->data($data)->add();//写入积分变化
				//更新用户积分
				$score_total = $score_total - $score;
				$score_expend = $score_expend +  $score;  
			}
			//会员卡充值
			if(is_numeric($vipaddrmb) && $vipaddrmb>0){
				unset($data);
				$data['token']    = $token;                                                                   
				$data['wecha_id'] = $wecha_id;                                                                
				$data['sign_time'] = time();
				$data['score_type'] = 2;//1签到 2消费rmb 3消费积分                                                                   
				$data['is_sign'] = 0;                                                                      
				$data['expense']  = 0;
				$data['sell_expense'] = $vipaddrmb; //金额变化，负数表示消费                                         
				//var_dump($data);exit;                                                                       
				$back = M('Member_card_sign')->data($data)->add();//写入积分变化
				//更新用户积分                                                                                      
				$rmb_total = $rmb_total + $vipaddrmb;//rmb计数器增减
				
			}
            //会员卡消费
			if(is_numeric($vipspendrmb) && $vipspendrmb>0){
				unset($data);
				$data['token']    = $token;                                                                   
				$data['wecha_id'] = $wecha_id;                                                                
				$data['sign_time'] = time();
				$data['score_type'] = 2;//1签到 2消费rmb 3消费积分                                                                   
				$data['is_sign'] = 1;                                                                      
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
		$where    = array('token'=>$data['token'],'wecha_id'=>$info['wecha_id']);  
        $record = $cardsign->where($where)->order('sign_time desc')->select();
		
	
		$this->assign('record',$record);
		//var_dump($tbsign);
		$this->display();
	}
	
	public function add(){
		$sql=M('Member');
		$data['token']=$this->_get('token');
		$data['uid']=session('uid');
		$member=$sql->field('id')->where($data)->find();
		$pic['homepic']=$this->_post('homepic');
		if($member!=false){
			$back=$sql->where($data)->save($pic);
			if($back){
				$this->success('更新成功');
			}else{
				$this->error('服务器繁忙，请稍后再试1');
			}
		}else{
			$data['homepic']=$pic['homepic'];
			$back=$sql->add($data);
			if($back){
				$this->success('更新成功');
			}else{
				$this->error('服务器繁忙，请稍后再试');
			}
		}
	
	}
	public function del(){
		$wecha_id = $this->_get('wecha_id');
		$back=M('member_card_create')->where(array('wecha_id'=>$wecha_id))->delete();
		if(!$back){
			$this->error('服务器繁忙，请稍候再试');
		} 
	
		$data['token']=$this->_get('token');
		$data['id']=$this->_get('id');
		$back=M('Userinfo')->where($data)->delete();
		if(!$back){
			$this->error('服务器繁忙，请稍候再试');
		} 
		$this->success('操作成功');
	}

	//------------------------------------------
	// 添加消费积分记录
	//------------------------------------------

	public function edit(){

		if(!IS_POST){
			$this->error('没有提交任何东西');exit;	
		}

		$token = $this->_post('token');
		$wecha_id = $this->_post('wecha_id');
		$add_expend = (int)$this->_post('add_expend');
		$add_expend_time = $this->_post('add_expend_time');
		
		if($add_expend <= 0){
			$this->error('消费金额必须大于0元');exit;	
		}
		//获取商家设置 tp_member_card_exchange
		$exchange = M('Member_card_exchange');
		$getset = $exchange->where(array('token'=>$token))->find();
		//var_dump($getset['continuation']); 
		// 积分 = 消费总金额 * $getset['continuation']
		$userinfo = M('Userinfo')->where(array('token'=>$token,'wecha_id'=>$wecha_id))->find();


		 $data['token']    = $token;
		 $data['wecha_id'] = $wecha_id;
		 $data['sign_time'] = strtotime($add_expend_time);
		 $data['score_type'] = 2;
		 $data['expense']  = ceil($add_expend * $getset['continuation']);
		 $data['sell_expense'] = $add_expend; //消费金额
		 //var_dump($data);exit;
		 $back = M('Member_card_sign')->data($data)->add();
		 
		 //总记录
		 $da['total_score']   = $userinfo['total_score'] +  $data['expense'];
         $da['expend_score']  = $userinfo['expend_score'] + $data['expense'];
         $da['add_expend']    = $add_expend;
         $da['add_expend_time']=strtotime($add_expend_time);
         $back2 = M('Userinfo')->where(array('token'=>$token,'wecha_id'=>$wecha_id))->save($da);
         $set_exchange = M('Member_card_exchange')->where(array('token'=>$token))->find();
         $get_card=M('member_card_create')->where(array('wecha_id'=>$wecha_id))->find();
                $card_type = 0;
            if($da['total_score'] >= $set_exchange["silver_card_score"])
            {   
                $card_type = 1;
            }   
            if($da['total_score'] >= $set_exchange["gold_card_score"]) 
            {   
                $card_type = 2;
            }   
            if($da['total_score'] >= $set_exchange["diamond_card_score"])
            {   
                $card_type = 3;
            }   
            if($get_card['card_type'] < $card_type)
            $mdata['card_type'] = $card_type;
            M('member_card_create')->where(array('wecha_id'=>$wecha_id))->save($mdata);
                                                                                             

        if($back && $back2){
			$this->success('操作成功');
		}else{
			$this->error('服务器繁忙，请稍候再试');
		} 
	}
}
?>
