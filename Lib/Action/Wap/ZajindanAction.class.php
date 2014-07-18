<?php
/**
 * 砸金蛋wap端控制器
 * @author xianzhao.cheng <carlton.cheng@foxmail.com>
 */
class ZajindanAction extends WapAction {
	public $huodong;//活动信息

	function _initialize(){
		parent::_initialize();

		$id = I('id',0,'intval');//活动id
		$this->huodong = M('Zajindan')->where(array('token'=>$this->token,'id'=>$id))->find();

		//检测活动是否存在并开启
		if( ! $this->huodong || $this->huodong['status'] != 1){
			$this->error('活动不存在或未开启');
		}

		//检测活动时间
		if($_SERVER['REQUEST_TIME'] > $this->huodong['etime']){
			$this->error('活动已于'.date('Y-m-d H:i:s',$this->huodong['etime']).'结束~~');
		}
		if($_SERVER['REQUEST_TIME'] < $this->huodong['stime']){
			$this->error('活动将于'.date('Y-m-d H:i:s',$this->huodong['stime']).'开始!请不要错过哦~');
		}

		//检测来源，自动跳转宣传页
		if( ! $this->wecha_id)
		{
			if( ! $this->huodong['redirect']){
				$this->redirect("Home/Adma/index?token=".$this->token);
			}else{
				redirect($this->huodong['redirect']);
			}
		}

		
		$this->assign("token",$this->token);
		$this->assign("wecha_id",$this->wecha_id);
	}

	

	//活动入口
	function index(){
		//读取用户积分
		//初始化用户记录
		//读取中奖记录
		$this->display();
	}

	//ajax抽奖
	function getprize(){
		//随机判断是否中奖
		//并发锁--开始
			//读取奖品列表
			//随机中奖
				//检测库存
				//生成记录
				//返回结果
		//并发锁结束
	}

	//提交表单
	function formsubmit(){
		
	}

}