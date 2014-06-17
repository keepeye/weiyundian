<?php
/*
 * wap分组的全局控制器
 */
class WapAction extends BaseAction
{
	public $token;
	public $wecha_id;
	public $wxsign;
	public $fromuser="";//通过checkFromuser方法赋值，如果有值则表示本次算一次有效推广点击
	public function _initialize()
	{
		parent::_initialize();
		//基本信息
		$this->token = I('request.token','');
		$this->wecha_id = I('request.wecha_id','');//获取wecha_id
		$this->wxsign = I('request.wxsign','');//获取加密字符串
		//检测推广点击
		$this->checkFromuser();
	}


	//检测当前访问的合法性
	public function checkWxsign()
	{
		//检测当前访问的合法性
		if($this->wecha_id == "" || md5($this->token.$this->wecha_id.C('safe_key'))!=$this->wxsign){
			return false;
		}
		return true;
	}

	//检测分享点击
	public function checkFromuser()
	{
		$fromuser = I('fromuser','');
		$id = I('id','');
		if( strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger')!==false && !empty($fromuser) )
		{

			$fromuser = encrypt($fromuser,"D",C('safe_key'));//解密字符串

			//if($fromuser && !cookie(MODULE_NAME."_FROM_".$fromuser."_".$id) ){
			if($fromuser ){
				$this->fromuser = $fromuser;
				$this->onFromuser();
				cookie(MODULE_NAME."_FROM_".$fromuser."_".$id,'1');
			}
		}
		$this->assign("fromuser",rawurlencode(encrypt($this->wecha_id,"E",C('safe_key'))));
	}

	//分享时的事件
	public function onFromuser()
	{
		//增加积分等
	}

}