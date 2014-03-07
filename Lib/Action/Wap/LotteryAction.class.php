<?php
class LotteryAction extends BaseAction{
	
	public function index(){
		dump($$_SERVER['HTTP_REFERER']);
		$agent = $_SERVER['HTTP_USER_AGENT']; 
		$token		= $this->_get('token');
		$wecha_id	= $this->_get('wecha_id') || cookie('openid');
		if($wecha_id == ""){
			$this->redirect("Home/Adma/index?token=".$token);
		}
		
		$id 		= $this->_get('id');
		$redata		= M('Lottery_record');
		$where 		= array('token'=>$token,'wecha_id'=>$wecha_id,'lid'=>$id);
		$record 	= $redata->where($where)->find();		
		if($record == Null){
			$redata->add($where);
			$record = $redata->where($where)->find();
			//浏览次数+1
			M('Lottery')->where(array('id'=>$id,'token'=>$token,'type'=>1,'status'=>1))->setInc('click',1);
		}
		$Lottery 	= M('Lottery')->where(array('id'=>$id,'token'=>$token,'type'=>1,'status'=>1))->find();
		
		//检测抽奖时间和抽奖次数，是否将抽奖次数归零
		if($Lottery['interval'] > 0 && (time()-$record['time']) > $Lottery['interval']){
			$redata->data(array('usenums'=>'0'))->where($where)->save();//距离上次抽奖时间已超过时间限制，次数归零
			$record['usenums'] = 0;//次数归零，下面用于判断
		}
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
		// 1. 中过奖金	
		if ($record['islottery'] == 1) {				
			$data['end'] = 5;
			$data['sn']	 	 = $record['sn'];
			$data['uname']	 = $record['wecha_name'];
			$data['prize']	 = $record['prize'];
			$data['tel'] 	 = $record['phone'];	
		}
		
		$data['On'] 		= 1;
		$data['token'] 		= $token;
		$data['wecha_id']	= $record['wecha_id'];		
		$data['lid']		= $record['lid'];
		$data['rid']		= $record['id'];
		$data['usenums'] 	= $record['usenums'];
		$data['canrqnums']	= $Lottery['canrqnums'];//抽奖次数限制
		$data['interval']	= $Lottery['interval'];//抽奖时间限制
		$data['fist'] 		= $Lottery['fist'];
		$data['second'] 	= $Lottery['second'];
		$data['third'] 		= $Lottery['third'];
		$data['four'] 		= $Lottery['four'];
		$data['five'] 		= $Lottery['five'];
		$data['six'] 		= $Lottery['six'];
		$data['fistnums'] 	= $Lottery['fistnums'];
		$data['secondnums'] = $Lottery['secondnums'];
		$data['thirdnums'] 	= $Lottery['thirdnums'];	
		$data['fournums'] 	= $Lottery['fournums'];
		$data['fivenums'] 	= $Lottery['fivenums'];
		$data['sixnums'] 	= $Lottery['sixnums'];
		$data['info']		= $Lottery['info'];
		$data['txt']		= $Lottery['txt'];
		$data['sttxt']		= $Lottery['sttxt'];		
		$data['title']		= $Lottery['title'];
		$data['statdate']	= $Lottery['statdate'];
		$data['enddate']	= $Lottery['enddate'];		
		$data['animpic'] = $Lottery['animpic'];
		$data['hitangle'] = $Lottery['hitangle'];
		$data['lostangle'] = $Lottery['lostangle'];
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
				if ($Lottery['sixlucknums'] >= $Lottery['sixenums']) {
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
		
		$token 		=	$this->_post('token');
		$wecha_id	=	$this->_post('oneid');
		$id 		=	$this->_post('id');//lid
		$rid 		= 	$this->_post('rid');//id	
		$redata 	=	M('Lottery_record');
		$where 		= 	array('token'=>$token,'wecha_id'=>$wecha_id,'lid'=>$id);
		$record 	=	$redata->where($where)->find();	//用户抽奖记录
		
		// 1. 中过奖金	
		if ($record['islottery'] == 1) {				
			//$norun = 1;
			$sn	 	 = $record['sn'];
			$uname	 = $record['wecha_name'];
			$prize	 = $record['prize'];
			$tel 	 = $record['phone'];
			$msg = "尊敬的:<font color='red'>$uname</font>,您已经中过<font color='red'> $prize</font> 了,您的领奖序列号:<font color='red'> $sn </font>请您牢记及尽快与我们联系.";
			echo '{"norun":1,"msg":"'.$msg.'"}';
			exit;		
		}
		
		
		// 2. 抽奖次数是否达到			
		$Lottery 	= M('Lottery')->where(array('id'=>$id,'token'=>$token,'type'=>1,'status'=>1))->find();
		
		if ($record['usenums'] >= $Lottery['canrqnums'] ) {
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
			//每次请求先增加 使用次数 usenums 
			$newdata = array(
				'time'=>time(),
				'usenums'=>array('exp','`usenums`+1')
			);
			$map = array('id'=>$rid,'wecha_id'=>$wecha_id,'lid'=>$id);
			
			//$record = M('Lottery_record')->where(array('id'=>$rid))->find();
			$prizetype	=	$this->get_prize($id);	

			if ($prizetype >= 1 && $prizetype <= 6) {				 
				$sn 	= uniqid();
				$prize = str_replace(array(1,2,3,4,5,6),array('一','二','三','四','五','六'),$prizetype)."等奖";
				$newdata['sn'] = $sn;//写入sn号码
				$newdata['prize'] = $prize;	//中奖等级描述
				$newdata['islottery'] = 1;
				echo '{"success":1,"sn":"'.$sn.'","prizetype":"'.$prizetype.'","usenums":"'.($record['usenums']+1).'"}';
			}else{
				echo '{"success":0,"prizetype":"","usenums":"'.($record['usenums']+1).'"}';
			}
			M('Lottery_record')->where($map)->data($newdata)->save();//更新抽奖记录、若中奖则写入sn号码		
			exit;
		} 
	}
	
	
	//中奖后填写信息
	public function add(){
		 if($_POST['action'] ==  'add' ){
			$lid 				= $this->_post('lid');
			$wechaid 			= $this->_post('wechaid');
			$sn		= $this->_post('sncode');
			$data['phone'] 		= $this->_post('tel');
			$data['wecha_name'] = $this->_post('wxname');

			$where = array('lid'=>$lid,'wecha_id'=>$wechaid);
			//检测奖项是否真实存在
			$record = M('Lottery_record')->where($where)->find();
			if(!$record || $record['islottery'] != 1 || $record['sn']==""){
				$this->ajaxReturn(array('success'=>'1','msg'=>'未检测到中奖记录'));
			}
			//奖品已经派发
			if($record['sendstutas'] != 0){
				$this->ajaxReturn(array('success'=>'1','msg'=>'奖品已派发，请不要重复提交'));
			}
			//记录用户联系信息
			$rollback = M('Lottery_record')->where($where)->save($data);
			
			echo'{"success":1,"msg":"恭喜！尊敬的 '.$data['wecha_name'].',请您保持手机通畅！你的领奖序号:'.$sn.'"}';
			exit;
		}
	}
}
	
?>
