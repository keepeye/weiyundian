<?php
class GuajiangAction extends WapAction{
	public $token;
	public $wecha_id;
	public $wxsign;
	public function index(){
		$agent = $_SERVER['HTTP_USER_AGENT'];
		$id = I('request.id');//活动id
		//初始化用户信息
		$token = $this->token;
		$wecha_id	= $this->wecha_id;
		$wxsign = $this->wxsign;
		//读取活动信息
		$Lottery = M('Lottery')->where(array('id'=>$id,'token'=>$token,'type'=>2,'status'=>1))->find();//为了处理推广信息，提前查询
		if(!$Lottery){
			$this->error("活动不存在或尚未开始");
		}
		
		//@file_put_contents("./fromuser.txt", $Lottery['spread']."\t".$Lottery['id']."\t".$_SERVER['HTTP_USER_AGENT']."\t".encrypt($fromuser,"D",C('safe_key'))."\n",FILE_APPEND);
	
		//检测当前访问的合法性
		if(!$this->wecha_id){
			//exit($this->fromuser);
			//dump(encrypt(I('fromuser',''),"D",C('safe_key')));exit;
			// $wxuser = M('Wxuser')->field('has_oauth')->where(array('token'=>$token))->find();
			// if($wxuser && $wxuser['has_oauth']=="1"){
			// 	redirect(U("Wap/Oauth/getCode",array("token"=>$this->token,"referer"=>rawurlencode('http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING']))));
			// }else{
				if(!empty($Lottery['redirect'])){
					redirect($Lottery['redirect']);
				}else{
					$this->redirect("Home/Adma/index?token=".$token);
				}
				
			//}
			//$this->redirect("Home/Adma/index?token=".$token);
		}

		//推广处理
		if($this->fromuser && $Lottery['spread'] == "1"){

			//推广者的抽奖记录
			$lt_re=M('Lottery_record')->where(array("lid"=>$id,"token"=>$this->token,"wecha_id"=>$this->fromuser))->find();
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

			M('Lottery')->where(array('id'=>$id))->setInc('joinnum',1);//参与人数+1
			$data1 = $where;
			$data1['usenums'] = $Lottery['canrqnums'];//从用户端计数，先将抽奖次数一次性赋予用户。
			$data1['time'] = time();
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
		
		
		//判断是否已经中奖
		if ($record['islottery'] == 1) {
			$this->assign("islottery","1");
			$data['islottery'] = '1';
			$data['sncode']	 = $record['sn'];//sn号
			$data['uname']	 = $record['myname'];//姓名
			$data['winprize']	= $record['prize'];//奖项名
		}else{
			//次数已经达到限定
			if ($record['usenums'] < 1 ) {
				$data['usenums'] = 0;
				$data['winprize']	= '抽奖次数已用完';

			}else{
				//先更新抽奖记录
				M('Lottery_record')->where(array('id'=>$record['id']))->data(array(
					"usenums"=>$record['usenums']-1,
					"counts"=>$record['counts']+1,
					"time"=>time()
				))->save();
				
				//随机抽奖
				//并发锁
				$file = TEMP_PATH."/lottery{$id}.lock";
				$fp = fopen($file,"w+");
				if(flock($fp,LOCK_EX | LOCK_NB)){
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
								$this->assign("islottery","1");
								$zjl	= true;
								M('LotteryRecord')->where(array('id'=>$record['id']))->data(array("islottery"=>1,"prize"=>$winprize))->save();
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
									$this->assign("islottery","1");
									$zjl	= true;
									M('LotteryRecord')->where(array('id'=>$record['id']))->data(array("islottery"=>1,"prize"=>$winprize))->save();
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
									$this->assign("islottery","1");
									$zjl	= true;
									M('LotteryRecord')->where(array('id'=>$record['id']))->data(array("islottery"=>1,"prize"=>$winprize))->save();
									M('Lottery')->where(array('id'=>$id))->setInc('thirdlucknums');
								}

							}
						break;

						default:
								$zjl = false;
								$winprize = '谢谢参与';
								break;
					}
					//解锁
					flock($fp,LOCK_UN);
					fclose($fp);
				}else{//未获取到文件锁，则视为未中奖
					$zjl = false;
					$winprize = '谢谢参与';
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
		//formset
		if(!empty($data['formset'])){
			$data['formset'] = json_decode($data['formset'],true);
		}
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
		if(IS_POST){
			$lid 				= $this->_post('lid');
			$wechaid 			= $this->_post('wechaid');
			//自定义表单处理
			if(isset($_POST['formdata']) && !empty($_POST['formdata'])){
				$data['formdata'] = json_encode($_POST['formdata']);
			}else{
				$data['phone'] 		= $this->_post('phone');
				$data['myname'] = $this->_post('myname');
				$data['idnumber'] = I("post.idnumber");
			}
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