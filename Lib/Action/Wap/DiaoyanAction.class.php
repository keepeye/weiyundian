<?php
class DiaoyanAction extends BaseAction {
	public $token;
	public $wecha_id;
	function _initialize(){
		parent::_initialize();
		//session("wecha_id",null);//=============
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
		$this->wecha_id = session('wecha_id');
		//没有session则从url参数中获取wecha_id和wxsign并验证
		if(!$this->wecha_id){
			$wecha_id = I('wecha_id','');
			$wxsign = I('wxsign','');
			if($this->_checkWxsign($wecha_id,$wxsign)){
				$this->wecha_id = $wecha_id;
				session('wecha_id',$wecha_id);//==========================
			}else{
				//$this->error("非法访问[02]");//这里应该跳转到授权页面
				redirect(U("Wap/Oauth/getCode",array("token"=>$this->token,"referer"=>rawurlencode('http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING']))));
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
		//检测用户是否已经参加过本次调研
		if(M('DiaoyanRecord')->where(array("diaoyan_id"=>$diaoyan_id,"wecha_id"=>$this->wecha_id))->find()){
			$this->assign("done","1");
		}else{
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
		}
		//
		
		$this->display();
	}
	//提交结果
	function submit(){
		if(IS_POST){
			$diaoyan_id = I('diaoyan_id','0','intval');
			if(M('DiaoyanRecord')->where(array("diaoyan_id"=>$diaoyan_id,"wecha_id"=>$this->wecha_id))->find()){
				$this->error("已参加过");
			}
			$results = $_POST['results'];//结果数组
			
			//取题库信息
			$tiku_list = M('DiaoyanTiku')->where(array("token"=>$this->token,"diaoyan_id"=>$diaoyan_id))->limit(0,10)->select();//获取题库列表
			$tiku_ids = array();
			foreach($tiku_list as $v){
				$tiku_ids[] = $v['id'];
			}
			unset($v);

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
						"option_id"=>$option_id
						);
					M('DiaoyanRecord')->add($data);
				}
			}
			$dazhuanpan = U('/Wap/Lottery/index',array('id'=>47,'token'=>$this->token,'wecha_id'=>$this->wecha_id,"wxsign"=>md5($this->token.$this->wecha_id.C('safe_key'))));
			$this->success("恭喜你获得一次大转盘抽奖机会，点击链接进入：<a href='$dazhuanpan'>大转盘活动开始啦</a>");
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