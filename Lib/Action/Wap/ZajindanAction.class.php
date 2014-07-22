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
		$this->assign("wxsign",$this->wxsign);
	}

	

	//活动入口
	function index(){
		//用户记录查询条件
		$record_map = array(
			"pid"=>$this->huodong['id'],
			"wecha_id"=>$this->wecha_id,
		);
		//初始化用户记录
		$record = M('ZajindanRecord')->where($record_map)->find();
		if( ! $record){
			//构建初始记录，并插入到数据库中
			$record = array(
				"pid"=>$this->huodong['id'],
				"wecha_id"=>$this->wecha_id,
				"times"=>$this->huodong['initnums'],
				"used"=>"0",
				"spread_times"=>0,
				"spread_score"=>0,
				"lasttime"=>0,
			);
			$re = M('ZajindanRecord')->data($record)->add();
			if($re === false){
				$this->error("页面异常，请重试");
			}
		}
		
		//读取奖品列表
		$prizes = M('ZajindanPrize')->where(array("pid"=>$this->huodong['id']))->select();

		$this->assign("prizes",$prizes);
		$this->assign("huodong",$this->huodong);
		$this->assign("record",$record);
		$this->display();
	}

	//ajax抽奖
	function getprize(){
		$data = array(
			"status"=>1,
			"data"=>array("prize"=>"蛋糕券","formdata"=>""),
			"info"=>"中奖啦"
		);
		$this->ajaxReturn($data);
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
		if( ! IS_POST){
			$this->error("非法请求");
		}
		$this->success("成功");
	}

}