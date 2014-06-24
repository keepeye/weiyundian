<?php
class GiftAction extends WapAction {
	public $token;
	public $wecha_id;
	public $wecha_user;//用户基本信息

	function _initialize(){
		parent::_initialize();
		
		if( ! $this->checkWxsign())
		{
			$this->redirect("Home/Adma/index?token=".$this->token);
		}

		//检测用户是否已关注
		$wecha_user = M('WechaUser')->where(array("token"=>$this->token,"wecha_id"=>$this->wecha_id))->find();
		if( ! $wecha_user)
		{
			$this->error("请先关注我们，并从公众号底部菜单进入礼品中心");
		}

		$this->wecha_user = $wecha_user;

		$this->assign("wecha_user",$wecha_user);
		$this->assign("token",$this->token);
		$this->assign("wecha_id",$this->wecha_id);
	}

	

	//活动入口
	function index(){
		$where = array(
			"token"=>$this->token,
			"status"=>1
		);
		$list = M('Gift')->select();
		$this->assign("list",$list);
		$this->display();
	}

	//礼品详情
	function detail()
	{
		$id = I('id',0);
		$m = M('Gift');
		$where = array(
			"token"=>$this->token,
			"status"=>1,
			"id"=>$id
		);
		if( ! $id || ! ($gift = $m->where($where)->find()))
		{
			$this->error("礼品不存在或已下架");
		}

		$this->assign("gift",$gift);
		$this->display();
	}

	//购买礼品
	function buy()
	{
		//并发锁
		$file = TEMP_PATH."/gift.lock";
		$fp = fopen($file,"w+");
		if(flock($fp,LOCK_EX | LOCK_NB))
		{
			//查询gift
			$id = I('id',0);
			$m = M('Gift');
			$where = array(
				"token"=>$this->token,
				"status"=>1,
				"id"=>$id,
			);
			try
			{
				//礼品不存在
				if( ! $id || ! ($gift = $m->where($where)->find()))
				{
					throw new Exception("礼品不存在或已下架");
				}
				//检查库存
				if($gift['stock'] <= 0)
				{
					throw new Exception("对不起，礼品被抢光了~");
				}
				//检查用户积分
				if($this->wecha_user['score'] < $gift['score'])
				{
					throw new Exception("对不起，您的积分不足~");
				}
				//检查限领
				if($gift['pernum'] > 0)
				{
					if(M('GiftSn')->where(array('token'=>$this->token,'pid'=>$id,'wecha_id'=>$this->wecha_id))->count() >= $gift['pernum'])
					{
						throw new Exception("抱歉，该礼品每人限制兑换".$gift['pernum']."个。");
					}
				}
				//库存-1
				$m->where($where)->setDec("stock",1);
				
				//创建购买记录
				$sn = uniqid();
				$data = array(
					"sn" => $sn,
					"token" => $this->token,
					"pid" => $id,
					"wecha_id" => $this->wecha_id,
					"time" => $_SERVER['REQUEST_TIME']
				);
				$re = M("GiftSn")->add($data);
				if($re === false)
				{
					throw new Exception("兑换失败");
				}
				//用户积分减去
				M('WechaUser')->where(array("token"=>$this->token,"wecha_id"=>$this->wecha_id))->setDec("score",$gift['score']);
			}
			catch(Exception $e)
			{
				flock($fp,LOCK_UN);
				fclose($fp);
				$this->error($e->getMessage());
			}
			//解锁
			flock($fp,LOCK_UN);
			fclose($fp);
			//前往提交表单
			$this->redirect("form",array("token"=>$this->token,"sn"=>$sn));
		}
		else
		{
			$this->error("抢购人数过多，请重试!");
		}
	}

	//用户个人表单
	function form()
	{
		if( ! IS_POST)
		{
			$code = I('sn','');
			$m = M('GiftSn');
			$sn = $m->where(array("sn"=>$code,"wecha_id"=>$this->wecha_id,"token"=>$this->token))->find();
			if(! $sn)
			{
				$this->error("兑换记录不存在");
			}
			$gift = M('Gift')->where(array("id"=>$sn['pid'],"token"=>$this->token))->find();
			if(!empty($gift['formset']))
			{
				$gift['formset'] = json_decode($gift['formset'],true);
			}
			$this->assign("gift",$gift);
			$this->assign("sn",$sn);
			$this->display();
		}
	}

	//我的礼品列表
	function mygifts()
	{
		$m = M('GiftSn');
		$where = array(
			"token"=>$this->token,
			"wecha_id"=>$this->wecha_id,
		);
		$list = $m->alias("sn")->where($where)
					->join("LEFT JOIN __GIFT__ AS gift ON gift.id = sn.pid AND gift.token = sn.token")
					->select();
		//$this->assign("list",$list);
		//$this->display();
		echo $m->getDbError()."<br/>";
		echo $m->getLastSql();
		dump($list);
	}
}