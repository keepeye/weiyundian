<?php
class LotteryAction extends WapAction{
	public function index(){
		$token = $this->token;
		$wxsign = $this->wxsign;
		$wecha_id = $this->wecha_id;
		$id = I('request.id');//活动id
		
		$Lottery = M('Lottery')->where(array('id'=>$id,'token'=>$token,'type'=>1,'status'=>1))->find();//为了处理推广信息，提前查询
		//检测当前访问的合法性
		if( ! $this->wecha_id){
			// $wxuser = M('Wxuser')->field('has_oauth')->where(array('token'=>$token))->find();
			// if($wxuser && $wxuser['has_oauth']=="1"){
			// 	redirect(U("Wap/Oauth/getCode",array("token"=>$this->token,"referer"=>rawurlencode('http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING']))));
			// }else{
				// if(!empty($Lottery['redirect'])){
				// 	redirect($Lottery['redirect']);
				// }else{
				// 	$this->redirect("Home/Adma/index?token=".$token);
				// }
			//}
			//$this->redirect("Home/Adma/index?token=".$token);
			if(!empty($Lottery['redirect'])){
				redirect($Lottery['redirect']);
			}else{
				$this->redirect("Home/Adma/index?token=".$token);
			}
		}
		
		dump($this->fromuser);
		//推广处理
		if($this->fromuser && $Lottery['spread'] == "1"){
			//推广者的抽奖记录
			$lt_re=M('Lottery_record')->field('spread_times_count,usenums')->where(array("lid"=>$id,"token"=>$this->token,"wecha_id"=>$this->fromuser))->find();
			//推广奖励
			if($lt_re){
				//抽奖次数奖励
				if($Lottery['spread_times_limit']==0 || $lt_re['spread_times'] < $Lottery['spread_times_limit']){
					$spread_data = array(
						"spread_times"=>$lt_re['spread_times']+1,
						"usenums"=>$lt_re['usenums']+1
					);
				}
				//积分奖励
				if($Lottery['spread_score']>0 && ($Lottery['spread_score_limit']==0 || $lt_re['spread_score'] < $Lottery['spread_score_limit'])){
					$spread_data['spread_score'] = $lt_re['spread_score'] + $Lottery['spread_score'];
					//增加用户积分
					if(D('WechaUser')->changeScore($this->token,$this->fromuser,$Lottery['spread_score'])){
		                D('WechaLog')->addLog($this->token,$this->fromuser,"积分","推广{$Lottery['title']}增加积分{$Lottery['spread_score']}");
		            }
				}
				//更新抽奖记录
				if(!empty($spread_data)){
					M('Lottery_record')
						->where(array("lid"=>$id,"token"=>$this->token,"wecha_id"=>$this->fromuser))
						->data($spread_data)
						->save();
				}				
			}
		}
		
		$this->assign("token",$this->token);
		$this->assign("wecha_id",$this->wecha_id);
		$this->assign("wxsign",$this->wxsign);

		//检测活动状态
		$data['token'] = $token;
		$data = array_merge($data,$Lottery);//模板数据

		//1.活动已关闭
		if (empty($Lottery)) {
			 $data['end'] = 1;
			 $data['endinfo'] = "活动已结束";
			 $this->assign('Dazpan',$data);
			 $this->display();
			 exit();
		}

		//活动尚未开始
		if($Lottery['statdate'] > time()){
			$data['end'] = 1;
			$data['endinfo'] = "活动将在 ".date("Y-m-d H:i:s",$Lottery['statdate'])." 开始";
			$this->assign('Dazpan',$data);
			$this->display();
			exit();
		}
		//活动过期
		//4.显示奖项,说明,时间
		if ($Lottery['enddate'] < time()) {
			 $data['end'] = 1;
			 $data['endinfo'] = $Lottery['endinfo'];
			 $this->assign('Dazpan',$data);
			 $this->display();
			 exit();
		}


		//获取用户抽奖记录
		$redata		= M('Lottery_record');
		$where 		= array('token'=>$token,'wecha_id'=>$wecha_id,'lid'=>$id);//构建查询条件
		$record 	= $redata->where($where)->find();//查询记录
		//初始化记录
		if(empty($record)){
			$data1 = $where;
			$data1['usenums'] = $Lottery['canrqnums'];//从用户端计数，先将抽奖次数一次性赋予用户。
			$redata->add($data1);
			unset($data1);
			$record = $redata->where($where)->find();//用户抽奖记录
			//浏览次数+1
			//M('Lottery')->where(array('id'=>$id,'token'=>$token,'type'=>1,'status'=>1))->setInc('click',1);
		}
		//检测抽奖时间和抽奖次数，是否将抽奖次数归零
		// if($Lottery['interval'] > 0 && (time()-$record['time']) > $Lottery['interval']){
		// 	$redata->data(array('usenums'=>'0'))->where($where)->save();//距离上次抽奖时间已超过时间限制，次数归零
		// 	$record['usenums'] = 0;//次数归零，下面用于判断
		// }
		// 按自然天计算
		if($Lottery['interval'] > 0 && time()>$record['time'] && date("d",time())!=date("d",$record['time'])){
			$record['usenums'] = $record['usenums']+$Lottery['canrqnums'];//次数重置，用于接下来的流程
			$redata->where($where)->setInc("usenums",$Lottery['canrqnums']);//距离上次抽奖时间已超过时间限制，抽奖计数自动补满或重置，每日抽奖次数不积累
		}
		$data['wecha_id']	= $record['wecha_id'];		
		$data['lid']		= $record['lid'];
		$data['rid']		= $record['id'];
		$data['usenums'] 	= $record['usenums'];
		// 1. 中过奖金	
		if ($record['islottery'] == 1) {				
			$data['end'] = 5;
			$data['sn']	 	 = $record['sn'];
			$data['uname']	 = $record['myname'];
			$data['prize']	 = $record['prize'];
			$data['tel'] 	 = $record['phone'];	
		}
		
		$data['On'] 		= 1;
		//formset
		if(!empty($data['formset'])){
			$data['formset'] = json_decode($data['formset'],true);
		}
		$this->assign('Dazpan',$data);
		//var_dump($data);exit();
		
		$this->display();
	}
	
	/**
	 * Enter description here...
	 *
	 * @param unknown_type $proArr
	 * @param unknown_type $total 预计参与人数
	 * @return unknown
	 */
	protected function get_rand($proArr,$total) { 
		    $result = 7; 
		    $randNum = mt_rand(1, $total); 
		    foreach ($proArr as $v) {
		    	
		    	if ($v['v']>0){//奖项存在或者奖项之外
		    		if ($randNum>$v['start']&&$randNum<=$v['end']){
		    			$result=$v['id'];
		    			break;
		    		}
		    	}
		    }
		    return $result; 
	}  
	
	protected function get_prize($id){

		$Lottery 	= M('Lottery')->where(array('id'=>$id))->find();
		//
		$firstNum=intval($Lottery['fistnums']);
		$secondNum=intval($Lottery['secondnums']);
		$thirdNum=intval($Lottery['thirdnums']);
		$fourthNum=intval($Lottery['fournums']);
		$fifthNum=intval($Lottery['fivenums']);
		$sixthNum=intval($Lottery['sixnums']);
		$multi=intval($Lottery['canrqnums']);//最多抽奖次数
		$total = intval($Lottery['allpeople'])*$multi;
		$prize_arr = array(
			'0' => array('id'=>1,'prize'=>'一等奖','v'=>$firstNum,'start'=>0,'end'=>$firstNum), 
			'1' => array('id'=>2,'prize'=>'二等奖','v'=>$secondNum,'start'=>$firstNum,'end'=>$firstNum+$secondNum), 
			'2' => array('id'=>3,'prize'=>'三等奖','v'=>$thirdNum,'start'=>$firstNum+$secondNum,'end'=>$firstNum+$secondNum+$thirdNum),
			'3' => array('id'=>4,'prize'=>'四等奖','v'=>$fourthNum,'start'=>$firstNum+$secondNum+$thirdNum,'end'=>$firstNum+$secondNum+$thirdNum+$fourthNum),
			'4' => array('id'=>5,'prize'=>'五等奖','v'=>$fifthNum,'start'=>$firstNum+$secondNum+$thirdNum+$fourthNum,'end'=>$firstNum+$secondNum+$thirdNum+$fourthNum+$fifthNum),
			'5' => array('id'=>6,'prize'=>'六等奖','v'=>$sixthNum,'start'=>$firstNum+$secondNum+$thirdNum+$fourthNum+$fifthNum,'end'=>$firstNum+$secondNum+$thirdNum+$fourthNum+$fifthNum+$sixthNum),
			'6' => array('id'=>7,'prize'=>'谢谢参与','v'=>(intval($Lottery['allpeople']))*$multi-($firstNum+$secondNum+$thirdNum+$fourthNum+$fifthNum+$sixthNum),'start'=>$firstNum+$secondNum+$thirdNum+$fourthNum+$fifthNum+$sixthNum,'end'=>$total)
		);
		//
		
		//-------------------------------	 
		//随机抽奖[如果预计活动的人数为1为各个奖项100%中奖]
		//-------------------------------	 
		if ($Lottery['allpeople'] == 1) {
	 
			if ($Lottery['fistlucknums'] <= $Lottery['fistnums']) {
				$prizetype = 1;	
			}else{
				$prizetype = 7;	
			}			
			
		}else{

			$prizetype = $this->get_rand($prize_arr,$total); 

		}
		 
		//$winprize = $prize_arr[$rid-1]['prize'];

		switch($prizetype){
			case 1:
					 
				if ($Lottery['fistlucknums'] >= $Lottery['fistnums']) {
					 $prizetype = ''; 
					 //$winprize = '谢谢参与'; 
				}else{
					if(empty($Lottery['fist']) || empty($Lottery['fistnums'])){
						$prizetype = '';
					}else{
						$prizetype = 1; 					
				    	M('Lottery')->where(array('id'=>$id))->setInc('fistlucknums');
					}
					
				}
				break;
				
			case 2:
				if ($Lottery['secondlucknums'] >= $Lottery['secondnums']) {
						$prizetype = ''; 
						//$winprize = '谢谢参与';
				}else{
					//判断是否设置了2等奖&&数量
					if(empty($Lottery['second']) || empty($Lottery['secondnums'])){
						$prizetype = ''; 
						//$winprize = '谢谢参与';
					}else{ //输出中了二等奖
						$prizetype = 2; 					
						M('Lottery')->where(array('id'=>$id))->setInc('secondlucknums');
					}	 
					
				}
				break;
							
			case 3:
				if ($Lottery['thirdlucknums'] >= $Lottery['thirdnums']) {
					 $prizetype = ''; 
					// $winprize = '谢谢参与';
				}else{
					if(empty($Lottery['third']) || empty($Lottery['thirdnums'])){
						 $prizetype = ''; 
						// $winprize = '谢谢参与';
					}else{
						$prizetype = 3; 					
						M('Lottery')->where(array('id'=>$id))->setInc('thirdlucknums');
					} 
					
				}
				break;
						
			case 4:
				if ($Lottery['fourlucknums'] >= $Lottery['fournums']) {
					  $prizetype =  ''; 
					// $winprize = '谢谢参与';
				}else{
					 if(empty($Lottery['four']) || empty($Lottery['fournums'])){
					   	$prizetype =  ''; 
					 	//$winprize = '谢谢参与';
					 }else{
					 	$prizetype = 4; 					
						M('Lottery')->where(array('id'=>$id))->setInc('fourlucknums');
					 }					
				}
			break;
			
			case 5:
				if ($Lottery['fivelucknums'] >= $Lottery['fivenums']) {
					 $prizetype =  ''; 
					 //$winprize = '谢谢参与';
				}else{
					if(empty($Lottery['five']) || empty($Lottery['fivenums'])){
						$prizetype =  ''; 
					 	//$winprize = '谢谢参与';
					}else{
						$prizetype = 5; 					
						M('Lottery')->where(array('id'=>$id))->setInc('fivelucknums');
					} 
				}
			break;
			
			case 6:
				if ($Lottery['sixlucknums'] >= $Lottery['sixnums']) {
					 $prizetype =  ''; 
					// $winprize = '谢谢参与';
				}else{
					 if(empty($Lottery['six']) || empty($Lottery['sixnums'])){
					 	$prizetype =  ''; 
					 	//$winprize = '谢谢参与';
					 }else{
					 	$prizetype = 6; 					
						M('Lottery')->where(array('id'=>$id))->setInc('sixlucknums');
					 }
					
				}
			break;
							
			default:
					$prizetype =  ''; 
					//$winprize = '谢谢参与';
					
					break;
		}
		
		return $prizetype;
	}
	
	public function getajax(){	
		
		$token 		=	I('token');
		$wecha_id	=	I('oneid');
		$wxsign = I('wxsign');
		//验证合法性
		if($wecha_id == "" || md5($token.$wecha_id.C('safe_key'))!=$wxsign){
			echo '{"norun":1,"msg":"非法请求，请重新点击图文消息进入"}';
			exit;
		}

		$id 		=	I('id');//lid
		$rid 		= 	I('rid');//id	
		$redata 	=	M('Lottery_record');
		$where 		= 	array('id'=>$rid,'token'=>$token,'wecha_id'=>$wecha_id,'lid'=>$id);
		$record 	=	$redata->where($where)->find();	//用户抽奖记录
		//判断用户是否存在记录表里
		if(!$record){
			echo '{"norun":1,"msg":"非法提交表单，请重新进入抽奖首页"}';
			exit;
		}
		// 1. 中过奖金	
		if ($record['islottery'] == 1) {				
			//$norun = 1;
			$sn	 	 = $record['sn'];
			$uname	 = $record['myname'];
			$prize	 = $record['prize'];
			$tel 	 = $record['phone'];
			$msg = "尊敬的:<font color='red'>$uname</font>,您已经中过<font color='red'> $prize</font> 了,您的领奖序列号:<font color='red'> $sn </font>请您牢记及尽快与我们联系.";
			echo '{"norun":1,"msg":"'.$msg.'"}';
			exit;		
		}
		// 2. 抽奖次数是否达到			
		//$Lottery 	= M('Lottery')->where(array('id'=>$id,'token'=>$token,'type'=>1,'status'=>1))->find();
		if ($record['usenums'] <= 0 ) {
			$norun 	 =  2;
			$usenums =  $record['usenums'];	
			$canrqnums=	$Lottery['canrqnums'];
			echo '{ 				
				"norun":'.$norun.',
				"usenums":"'.$usenums.'",
				"canrqnums":"'.$canrqnums.'",
				"id":"'.$id.'",
				"token":"'.$token.'",
				"type":"'.$type.'",
				"status":"'.$status.'"
			}';
			exit;	
		}else{ 
			//统计参与人数,只有初次抽奖才算
			if($record['time'] == 0){
				M('Lottery')->where(array('id'=>$id,'token'=>$token,'type'=>1,'status'=>1))->setInc('joinnum',1);//参与人数+1
			}
			$newusenums = max($record['usenums']-1,0);
			$newcounts = $record['counts']+1;
			//每次请求先减少 使用次数 usenums 
			$newdata = array(
				'time'=>time(),
				'usenums'=>$newusenums,
				'counts'=>$newcounts
			);
			$map = array('id'=>$rid,'wecha_id'=>$wecha_id,'lid'=>$id);
			
			//$record = M('Lottery_record')->where(array('id'=>$rid))->find();
			$prizetype	=	$this->get_prize($id);	
			//这里不应当先生成sn号码，应该用户提交联系人信息后生成并返回给用户
			if ($prizetype >= 1 && $prizetype <= 6) {				 
				//$sn 	= uniqid();
				//$prize = str_replace(array(1,2,3,4,5,6),array('一','二','三','四','五','六'),$prizetype)."等奖";
				$prize = str_replace(array(1,2,3,4,5,6),array('fist','second','third','four','five','six'),$prizetype);//直接写奖品名称
				//$newdata['sn'] = $sn;//写入sn号码
				$lottery = M('Lottery')->field("fist,second,third,four,five,six")->where(array('token'=>$token,"id"=>$record['lid']))->find();//查询活动信息的中奖设置
				$newdata['prize'] = $lottery[$prize];//奖品名称
				$newdata['islottery'] = 1;
				//echo '{"success":1,"sn":"'.$sn.'","prizetype":"'.$prizetype.'","usenums":"'.($record['usenums']+1).'"}';
				$ajaxData = array(
						"success"=>1,
						"prizetype"=>$prizetype,
						"usenums"=>$newusenums
					);
				
			}else{
				//echo '{"success":0,"prizetype":"","usenums":"'.($record['usenums']+1).'"}';
				$ajaxData = array(
						"success"=>"0",
						"prizetype"=>"",
						"usenums"=>$newusenums
					);
			}
			M('Lottery_record')->where($map)->data($newdata)->save();//更新抽奖记录、若中奖则写入sn号码		
			$this->ajaxReturn($ajaxData);
			exit;
		} 
	}
	
	
	//中奖后填写信息
	public function add(){
		 if(IS_POST){
			$lid 				= $this->_post('lid');
			$wechaid 			= $this->_post('wechaid');
			//$sn		= $this->_post('sncode');
			$data['sn'] = $sn = uniqid();//生成sn码
			
			//自定义表单处理
			if(isset($_POST['formdata']) && !empty($_POST['formdata'])){
				foreach($_POST['formdata'] as $value){
					if(empty($value)){
						$this->ajaxReturn(array('success'=>'1','msg'=>'表单填写不完整，请重新填写'));
					}
				}
				$data['formdata'] = json_encode($_POST['formdata']);
			}else{
				$data['phone'] 		= $this->_post('phone');
				$data['myname'] = $this->_post('myname');
				$data['idnumber'] = I("post.idnumber");
			}
			$where = array('lid'=>$lid,'wecha_id'=>$wechaid);
			//检测奖项是否真实存在
			$record = M('Lottery_record')->where($where)->find();
			if(!$record || $record['islottery'] != 1){
				$this->ajaxReturn(array('success'=>'1','msg'=>'未检测到中奖记录'));
			}
			//检测身份证是否重复
			if(M('Lottery_record')->where(array('lid'=>$lid,'idnumber'=>$data['idnumber']))->count() > 0){
				$this->ajaxReturn(array('success'=>'1','msg'=>'该身份证已经中过奖'));
			}
			//奖品已经派发
			if($record['sendstutas'] != 0){
				$this->ajaxReturn(array('success'=>'1','msg'=>'奖品已派发，请不要重复提交'));
			}
			//记录用户联系信息,sn号码
			$rollback = M('Lottery_record')->where($where)->save($data);
			
			echo'{"success":1,"msg":"恭喜！尊敬的 '.$data['myname'].',请您保持手机通畅！你的领奖序号:'.$sn.'"}';
			exit;
		}
	}
}