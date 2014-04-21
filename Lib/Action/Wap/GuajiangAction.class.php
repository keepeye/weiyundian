<?php
class GuajiangAction extends BaseAction{
	public $token;
	public $wecha_id;
	public $wxsign;
	public function index(){
		$agent = $_SERVER['HTTP_USER_AGENT'];
		$id = I('request.id');//活动id
		//初始化用户信息
		$this->token = $token		= I('request.token');
		$this->wecha_id = $wecha_id	= I('request.wecha_id');//获取wecha_id
		$this->wxsign = $wxsign = I('request.wxsign');//获取加密字符串
		$Lottery = M('Lottery')->where(array('id'=>$id,'token'=>$token,'type'=>2,'status'=>1))->find();//为了处理推广信息，提前查询
		if(!$Lottery){
			$this->error("活动不存在或尚未开始");
		}
		//推广处理
		$fromuser = I('fromuser','');//获取推广用户
		if(strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger')!==false && !empty($fromuser) && !cookie("guajiang_fromuid_".$Lottery['id']) && $Lottery['spread'] == "1"){
			$fromuid = encrypt($fromuser,"D",C('safe_key'));//解密字符串
			if($fromuid){
				$lt_re=M('Lottery_record')->field('spread_count,usenums')->where(array("lid"=>$Lottery['id'],"token"=>$this->token,"wecha_id"=>$fromuid))->find();
				if($lt_re && ($Lottery['spread_limit']==0 || $lt_re['spread_count'] < $Lottery['spread_limit'])){
					M('Lottery_record')->where(array("lid"=>$Lottery['id'],"token"=>$this->token,"wecha_id"=>$fromuid))->data(array("spread_count"=>$lt_re['spread_count']+1,"usenums"=>$lt_re['usenums']+1))->save();
				}
				cookie("guajiang_fromuid_".$Lottery['id'],'1');
			}
			//给fromuid的用户增加一次抽奖机会
		}
		//生成当前用户的fromuser
		$this->assign("fromuser",rawurlencode(encrypt($this->wecha_id,"E",C('safe_key'))));

		//检测当前访问的合法性
		if($wecha_id == "" || md5($token.$wecha_id.C('safe_key'))!=$wxsign){
			$wxuser = M('Wxuser')->field('has_oauth')->where(array('token'=>$token))->find();
			if($wxuser && $wxuser['has_oauth']=="1"){
				redirect(U("Wap/Oauth/getCode",array("token"=>$this->token,"referer"=>rawurlencode('http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING']))));
			}else{
				$this->redirect("Home/Adma/index?token=".$token);
			}
			//$this->redirect("Home/Adma/index?token=".$token);
		}
		$this->assign("token",$token);
		$this->assign("wecha_id",$wecha_id);
		$this->assign("wxsign",$wxsign);
		//检测活动状态
		$data['token'] = $token;
		$data = array_merge($data,$Lottery);//模板数据

		//1.活动已关闭
		if (empty($Lottery)) {
			 $data['end'] = 1;
			 $data['endinfo'] = "活动已结束";
			 $this->assign('Guajiang',$data);
			 $this->display();
			 exit();
		}
		//活动尚未开始
		if($Lottery['statdate'] > time()){
			// $data['end'] = 1;
			// $data['endinfo'] = "活动将在 ".date("Y-m-d H:i:s",$Lottery['statdate'])." 开始";
			// $this->assign('Guajiang',$data);
			// $this->display();
			$this->error("活动将在 ".date("Y-m-d H:i:s",$Lottery['statdate'])." 开始");
			exit();
		}
		//活动过期
		//4.显示奖项,说明,时间
		if ($Lottery['enddate'] < time()) {
			 $data['end'] = 1;
			 $data['endinfo'] = $Lottery['endinfo'];
			 $this->assign('Guajiang',$data);
			 $this->display();
			 exit();
		}
		//获取用户抽奖记录
		$redata	  = M('Lottery_record');
		$where	  = array('token'=>$token,'wecha_id'=>$wecha_id,'lid'=>$id);
		$record   = $redata->where($where)->find();
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
			$redata->data(array('usenums'=>$Lottery['canrqnums']))->where($where)->save();//距离上次抽奖时间已超过时间限制，抽奖计数自动补满或重置，每日抽奖次数不积累
			$record['usenums'] = $Lottery['canrqnums'];//次数重置，用于接下来的流程
		}
		
		
		
		if ($record['islottery'] == 1) {
			$data['islottery'] = '1';
			$data['sncode']	 = $record['sn'];//sn号
			$data['uname']	 = $record['myname'];//姓名
			$data['winprize']	= $record['prize'];//奖项名
		}else{

			if ($record['usenums'] < 1 ) {
				//次数已经达到限定
				$data['usenums'] = 0;
				$data['winprize']	= '抽奖次数已用完';
			}else{

				M('Lottery_record')->where(array('id'=>$record['id']))->setDec('usenums',1);//抽奖机会-1
				M('Lottery_record')->where(array('id'=>$record['id']))->setInc('counts',1);//抽奖次数+1
				//$record = M('Lottery_record')->where(array('id'=>$record['id']))->find();//
				$firstNum=intval($Lottery['fistnums']);
				$secondNum=intval($Lottery['secondnums']);
				$thirdNum=intval($Lottery['thirdnums']);
				$fourthNum=intval($Lottery['fournums']);
				$fifthNum=intval($Lottery['fivenums']);
				$sixthNum=intval($Lottery['sixnums']);
				$multi=intval($Lottery['canrqnums']);//最多抽奖次数
				$prize_arr = array(
				'0' => array('id'=>1,'prize'=>'一等奖','v'=>$firstNum,'start'=>0,'end'=>$firstNum),
				'1' => array('id'=>2,'prize'=>'二等奖','v'=>$secondNum,'start'=>$firstNum,'end'=>$firstNum+$secondNum),
				'2' => array('id'=>3,'prize'=>'三等奖','v'=>$thirdNum,'start'=>$firstNum+$secondNum,'end'=>$firstNum+$secondNum+$thirdNum),
				'3' => array('id'=>4,'prize'=>'谢谢参与','v'=>(intval($Lottery['allpeople']))*$multi-($firstNum+$secondNum+$thirdNum),'start'=>$firstNum+$secondNum+$thirdNum,'end'=>intval($Lottery['allpeople'])*$multi)
				);


				foreach ($prize_arr as $key => $val) {
				    $arr[$val['id']] = $val;
				}
		 		if ($Lottery['allpeople'] == 1) {

					if ($Lottery['fistlucknums'] <= $Lottery['fistnums']) {
						$rid = 1;
					}else{
						$rid = 4;
					}

				}else{
					$rid = $this->get_rand($arr,intval($Lottery['allpeople'])*$multi);
				}


				$winprize = $prize_arr[$rid-1]['prize'];
				$zjl = false;

				switch($rid){
					case 1:

						if ($Lottery['fistlucknums'] >= $Lottery['fistnums']) {
							 $zjl = false;
							 $winprize = '谢谢参与';
						}else{
							$winprize = $Lottery['fist'];
							$zjl	= true;
							M('Lottery')->where(array('id'=>$id))->data(array("islottery"=>1,"prize"=>$winprize))->save();
						    M('Lottery')->where(array('id'=>$id))->setInc('fistlucknums');
						}
					break;

					case 2:
						if ($Lottery['secondlucknums'] >= $Lottery['secondnums']) {
								$zjl = false;
								$winprize = '谢谢参与';
						}else{
							//判断是否设置了2等奖&&数量
							if(empty($Lottery['second']) && empty($Lottery['secondnums'])){
								$zjl = false;
								$winprize = '谢谢参与';
							}else{ //输出中了二等奖
								$winprize = $Lottery['second'];
								$zjl	= true;
								M('Lottery')->where(array('id'=>$id))->data(array("islottery"=>1,"prize"=>$winprize))->save();
								M('Lottery')->where(array('id'=>$id))->setInc('secondlucknums');
							}

						}
					break;

					case 3:
						if ($Lottery['thirdlucknums'] >= $Lottery['thirdnums']) {
							 $zjl = false;
							 $winprize = '谢谢参与';
						}else{
							if(empty($Lottery['third']) && empty($Lottery['thirdnums'])){
								$zjl = false;
								$winprize = '谢谢参与';
							}else{
								$winprize = $Lottery['third'];
								$zjl	= true;
								M('Lottery')->where(array('id'=>$id))->data(array("islottery"=>1,"prize"=>$winprize))->save();
								M('Lottery')->where(array('id'=>$id))->setInc('thirdlucknums');
							}

						}
					break;

					default:
							$zjl = false;
							$winprize = '谢谢参与';
							break;
				}

				//$data['prizeid']  	= $rid;
				$data['zjl'] 		= $zjl;
				$data['wecha_id']	= $record['wecha_id'];
				$data['lid']		= $record['lid'];
				$data['winprize']	= $winprize;
			} //end if;
		} // end first if;

		$data['usenums'] 	= intval($record['usenums']);
		$data['counts']  = (int)$record['counts'];
		$data['canrqnums']	= $Lottery['canrqnums'];
		$data['fist'] 		= $Lottery['fist'];
		$data['second'] 	= $Lottery['second'];
		$data['third'] 		= $Lottery['third'];
		$data['fistnums'] 	= $Lottery['fistnums'];
		$data['secondnums'] = $Lottery['secondnums'];
		$data['thirdnums'] 	= $Lottery['thirdnums'];
		$data['info']		= $Lottery['info'];
		$data['endinfo']	= $Lottery['endinfo'];
		$data['txt']		= $Lottery['txt'];
		$data['sttxt']		= $Lottery['sttxt'];
		$data['title']		= $Lottery['title'];
		$data['statdate']	= $Lottery['statdate'];
		$data['enddate']	= $Lottery['enddate'];

		$this->assign('Guajiang',$data);

		$prizeStr='<p>'.$Lottery['fist'];
		if ($Lottery['displayjpnums']){
			$prizeStr.='奖品数量:'.$Lottery['fistnums'];
		}
		$prizeStr.='</p>';
		if ($Lottery['second']){
			$prizeStr.='<p>'.$Lottery['second'];
			if ($Lottery['displayjpnums']){
				$prizeStr.='奖品数量:'.$Lottery['secondnums'];
			}
			$prizeStr.='</p>';
		}
		if ($Lottery['third']){
			$prizeStr.='<p>'.$Lottery['third'];
			if ($Lottery['displayjpnums']){
				$prizeStr.='奖品数量:'.$Lottery['thirdnums'];
			}
			$prizeStr.='</p>';
		}
		$this->assign('prizeStr',$prizeStr);
		$this->display();

	}
	protected function get_rand($proArr,$total) {
		    $result = 7;
		    $randNum = mt_rand(1, $total);
		    foreach ($proArr as $k => $v) {

		    	if ($v['v']>0){//奖项存在或者奖项之外
		    		if ($randNum>$v['start']&&$randNum<=$v['end']){
		    			$result=$k;
		    			break;
		    		}
		    	}
		    }
		    return $result;
	}



	public function add(){
		if($_POST['action'] ==  'add'  ){
			$lid 				= $this->_post('lid');
			$wechaid 			= $this->_post('wechaid');
			$data['phone'] 		= $this->_post('tel');
			$data['myname'] = $this->_post('wxname');
			$data['idnumber'] = I("post.idnumber");
			//检测奖项是否真实存在
			$where = array('lid'=>$lid,'wecha_id'=>$wechaid);
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
			$data['time']		= time();
			$data['sn']			= uniqid();
			$rollback = M('Lottery_record')->where(array('lid'=> $lid,
				'wecha_id'=>$wechaid))->save($data);
			echo'{"success":1,"msg":"恭喜！尊敬的'.$data['myname'].'请您保持手机通畅！请您牢记的领奖号:'.$data['sn'].'"}';
			exit;
		}

	}

}