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
		
		//检测用户是否能抽奖
		$record_map = array(
			"pid"=>$this->huodong['id'],
			"wecha_id"=>$this->wecha_id,
		);
		$record = M('ZajindanRecord')->where($record_map)->find();
		if( ! $record){
			$this->ajaxReturn(array("status"=>0,"info"=>"用户记录不存在"));
		}
		//抽奖次数检测
		if($record['times'] <=0){
			if($this->huodong['needscore']<=0){
				$this->ajaxReturn(array("status"=>0,"info"=>"抽奖次数已用完"));
			}
			//读取用户信息
			$wecha_user = M('WechaUser')->field('score')->where(array('token'=>$this->token,'wecha_id'=>$this->wecha_id))->find();
			//使用积分抽奖
			if($wecha_user['score']>$this->huodong['needscore']){
				M('WechaUser')->where(array('token'=>$this->token,'wecha_id'=>$this->wecha_id))->setDec('score',$this->huodong['needscore']);
			}else{
				$this->ajaxReturn(array("status"=>0,"info"=>"积分不足"));
			}
		}
		//更新抽奖次数
		M('ZajindanRecord')->where($record_map)->data(array(
			"times"=>max($record['times']-1,0),
			"used"=>$record['used']+1
		))->save();
		
		$myprize = $this->_roll();//抽奖
		//判断是否中到奖品
		if( ! $myprize){
			//ajax返回数据
			$data = array(
				"status"=>0,
				"info"=>"未中奖，请再接再厉"
			);
		}else{
			//生成sn记录
			$sn = uniqid();
			M('ZajindanSn')->data(array("sn"=>$sn,"pid"=>$myprize['id'],"wecha_id"=>$this->wecha_id,"time"=>$_SERVER['REQUEST_TIME']))->add();
			//更新用户积分
			if($myprize['extra_score'] > 0){
				if(D('WechaUser')->changeScore($this->token,$this->wecha_id,$myprize['extra_score'])){
	                D('WechaLog')->addLog($this->token,$this->wecha_id,"积分","Zajindan:{$this->huodong['id']}:{$myprize['id']} +{$myprize['extra_score']}");
	            }
			}
			//ajax返回数据
			$data = array(
				"status"=>1,
				"data"=>array("prize"=>$myprize['name'],"formdata"=>unserialize($record['formdata']),"sn"=>$sn,"extra_score"=>$myprize['extra_score']),
				"info"=>"中奖啦"
			);
		}
		
		$this->ajaxReturn($data);
	}

	//抽奖过程
	private function _roll(){
		//随机判断是否中奖
		$gailv = $this->huodong['gailv']*100;//基数扩大一百倍
		$randnum = mt_rand(1,10000);
		$hit = $randnum<=$gailv?true:false;//随机数落在1~$gailv之间则中奖
		$myprize = array();//初始化奖品
		//中奖处理
		if($hit){
			//并发锁--开始
			$file = TEMP_PATH."/zajindan_getprize.lock";
			$fp = fopen($file,"w+");
			if(flock($fp,LOCK_EX | LOCK_NB)){
				//读取奖品列表
				$prizes = M('ZajindanPrize')->where(array("pid"=>$this->huodong['id'],"stock"=>array("gt","0")))->select();
				if($prizes){
					$weightsum = 0;//初始化区间数组下标
					foreach($prizes as $k=>$prize){
						//提取权重
						$weightsum += $prize['weight'];
						$rd_k[$weightsum] = $k;
					}
					$prize_rd = mt_rand(1,$weightsum);
					$start = 0;
					foreach($rd_k as $k1=>$v){
						//判断随机数落在某个区间
						if($prize_rd>$start && $prize_rd<=$k1){
							$myprize = $prizes[$v];
							//更新库存
							M('ZajindanPrize')->where("id='{$prizes[$v]['id']}'")->setDec('stock',1);
							break;
						}
						$start = $k1;
					}
				}
				flock($fp,LOCK_UN);//解除并发锁
			}
			
			fclose($fp);
		}
		return $myprize;
	}

	//提交表单
	function formsubmit(){
		if( ! IS_POST){
			$this->error("非法请求");
		}
		$this->success("成功");
	}

}