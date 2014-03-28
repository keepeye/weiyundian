<?php
class DiaoyanAction extends BaseAction {
	public $token;
	public $wecha_id;
	function _initialize(){
		parent::_initialize();
		//cookie("wecha_id",null);//=============
		$this->token = I('token',I('get.token',''),'');//获取token
		if(!$this->token){
			$this->error("非法访问[01]");
		}

		$this->_initSession();

		$this->assign("token",$this->token);
		$this->assign("wecha_id",$this->wecha_id);
	}

	//初始化用户会话信息
	private function _initSession(){
		$this->wecha_id = cookie('wecha_id');//改用cookie
		//没有session则从url参数中获取wecha_id和wxsign并验证
		if(!$this->wecha_id){
			$wecha_id = I('wecha_id','');
			$wxsign = I('wxsign','');
			if($this->_checkWxsign($wecha_id,$wxsign)){
				$this->wecha_id = $wecha_id;
				cookie('wecha_id',$wecha_id,7200);//==========================
			}else{
				//$this->error("非法访问[02]");//这里应该跳转到授权页面
				//redirect(U("Wap/Oauth/getCode",array("token"=>$this->token,"referer"=>rawurlencode('http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING']))));
				$this->redirect("Home/Adma/index?token=".$this->token);
			}
		}
	}

	//活动入口
	function index(){
		$id = I('id','0','intval');//获取活动id

		$diaoyan = M('Diaoyan')->where(array("token"=>$this->token,"id"=>$id))->find();//查询活动信息
		if(!$diaoyan){
			exit("页面不存在404");
		}

		//检查活动是否已经结束
		if(time()>$diaoyan['end_time']){
			$this->error("活动已经结束了");
		}

		$fromuser = I('fromuser','');//获取推广用户
		if(!empty($fromuser) && !cookie("diaoyan_fromuid_".$diaoyan['id'])){
			$fromuid = encrypt($fromuser,"D",C('safe_key'));//解密字符串
			if($fromuid && $diaoyan['activity_module'] == "dazhuanpan" && $diaoyan['activity_module_id']>0){
				M('Lottery_record')->where(array("lid"=>$diaoyan['activity_module_id'],"token"=>$this->token,"wecha_id"=>$fromuid))->setInc("usenums");
				cookie("diaoyan_fromuid_".$diaoyan['id'],'1');
			}
			//给fromuid的用户增加一次抽奖机会
		}
		//生成当前用户的fromuser
		$this->assign("fromuser",rawurlencode(encrypt($this->wecha_id,"E",C('safe_key'))));
		$this->assign("diaoyan",$diaoyan);
		$this->display();
	}

	//答题开始
	function questions(){
		$diaoyan_id = I('diaoyan_id');
		$diaoyan = M('Diaoyan')->where(array("token"=>$this->token,"id"=>$diaoyan_id))->find();//查询活动信息
		if(!$diaoyan){
			exit("页面不存在404");
		}
		$this->assign("diaoyan",$diaoyan);
		$lastrecord = M('DiaoyanRecord')->where(array("diaoyan_id"=>$diaoyan_id,"wecha_id"=>$this->wecha_id))->find();
		//检测用户是否已经参加过本次调研
		if($lastrecord){

			if($diaoyan['everyday'] == "1"){//如果设置为每天参加，则检测上次投票时间
				$day = date("j",time());//今日day数字
				echo $lastrecord['day'],$lastrecord['time'];
				if($lastrecord['day']==$day && (time()-$lastrecord['time'])<=86400){
					$this->error("今天你已经参加过，请明天再来");
				}
			}else{
				$this->error("你已参加过了，可以转发给你的朋友们一起来玩。");
			}
		}
		if(!$diaoyan_id){
			$this->error("非法请求[03]");
		}
		$tiku_list = M('DiaoyanTiku')->where(array("token"=>$this->token,"diaoyan_id"=>$diaoyan_id))->limit(0,10)->select();//获取题库列表
		$tiku_ids = array();

		foreach($tiku_list as $v){
			$tiku_ids[] = $v['id'];
		}
		unset($v);
		$options = M('DiaoyanTikuOption')->where(array("tiku_id"=>array("in",$tiku_ids)))->select();//读取选项表
		$option_list = array();
		foreach($options as $v){
			$option_list[$v['tiku_id']][]=$v;
		}
		unset($v);
		$this->assign("diaoyan_id",$diaoyan_id);
		$this->assign("tiku_list",$tiku_list);
		$this->assign("option_list",$option_list);
		
		//
		
		$this->display();
	}
	//提交结果
	function submit(){
		if(IS_POST){
			$diaoyan_id = I('diaoyan_id','0','intval');
			//查询活动信息
			$diaoyan = M('Diaoyan')->where(array("token"=>$this->token,"id"=>$diaoyan_id))->find();//查询活动信息
			if(!$diaoyan){
				exit("页面不存在404");
			}
			$this->assign("diaoyan",$diaoyan);
			//查询参与记录
			if($lastrecord = M('DiaoyanRecord')->where(array("diaoyan_id"=>$diaoyan_id,"wecha_id"=>$this->wecha_id))->find()){
				if($diaoyan['everyday'] == "1"){//如果设置为每天参加，则检测上次投票时间
					$day = date("j",time());//今日day数字
					if($lastrecord['day']==$day && (time()-$lastrecord['time'])<=86400){
						$this->error("今天你已经参加过，请明天再来");
					}
				}else{
					$this->error("已参加过");
				}
				
			}
			$results = $_POST['results'];//结果数组
			
			//取题库信息
			$tiku_list = M('DiaoyanTiku')->where(array("token"=>$this->token,"diaoyan_id"=>$diaoyan_id))->limit(0,10)->select();//获取题库列表
			$tiku_ids = array();
			foreach($tiku_list as $v){
				$tiku_ids[] = $v['id'];
			}
			unset($v);
			$nowtime = time();//当前时间戳
			$year = date("Y",$nowtime);//当前年份数字
			$month = date("n",$nowtime);//当前月份数字
			$day = date('j',$nowtime);//当前一个月的第几天
			//开始写入记录
			foreach ($results as $result) {
				if(!in_array($result['tiku_id'],$tiku_ids)){
					continue;//如果题库id不属于当前活动，则跳过本次循环
				}
				//循环插入record
				foreach($result['option_ids'] as $option_id){
					$data = array(
						"wecha_id"=>$this->wecha_id,
						"tiku_id"=>$result['tiku_id'],
						"diaoyan_id"=>$diaoyan_id,
						"option_id"=>$option_id,
						"year"=>$year,
						"month"=>$month,
						"day"=>$day,
						"time"=>$nowtime
					);
					M('DiaoyanRecord')->add($data);
				}
			}
			//提交表单后生成活动链接
			if($diaoyan['activity_module'] != "" && (int)$diaoyan['activity_module_id'] != ""){
				if($diaoyan['activity_module'] == "dazhuanpan"){
					$activity_url = "<a href='".U('Wap/Lottery/index',array('id'=>$diaoyan['activity_module_id'],'token'=>$this->token,'wecha_id'=>$this->wecha_id,"wxsign"=>md5($this->token.$this->wecha_id.C('safe_key'))))."'>幸运抽奖</a>";
				}
			}
			
			//返回提交信息
			$this->success($diaoyan['success_info']."<br/>".$activity_url);
		}else{
			$this->error("非法提交[04]");
		}
	}
	//检测合法性
	private function _checkWxsign($wecha_id,$wxsign){
		//return true;//=========================
		return !empty($wecha_id) && !empty($wxsign) && (md5($this->token.$wecha_id.C('safe_key')) == $wxsign);
	}
}