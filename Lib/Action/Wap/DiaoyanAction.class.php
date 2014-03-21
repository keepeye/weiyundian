<?php
class DiaoyanAction extends BaseAction {
	public $token;
	public $wecha_id;
	function _initialize(){
		parent::_initialize();
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
				//session('wecha_id',$wecha_id);//==========================
			}else{
				$this->error("非法访问[02]");//这里应该跳转到授权页面
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

	}

	//检测合法性
	private function _checkWxsign($wecha_id,$wxsign){
		return true;//=========================
		//return !empty($wecha_id) && !empty($wxsign) && (md5($this->token.$wecha_id.C('safe_key')) == $wxsign);
	}
}