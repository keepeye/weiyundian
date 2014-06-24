<?php
class GiftAction extends WapAction {
	public $token;
	public $wecha_id;

	public $wecha_user;
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
		if( ! IS_POST)
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
				//礼品不存在
				if( ! $id || ! ($gift = $m->where($where)->find()))
				{
					flock($fp,LOCK_UN);
					fclose($fp);
					$this->error("礼品不存在或已下架");
				}
				//检查库存
				if($gift['stock'] <= 0)
				{
					flock($fp,LOCK_UN);
					fclose($fp);
					$this->error("对不起，礼品被抢光了~");
				}
				//库存-1
				$m->where($where)->setDec("stock",1);
				//解锁
				flock($fp,LOCK_UN);
				fclose($fp);
				//创建购买记录
				
				//显示表单视图
			}
			else
			{
				$this->error("抢购人数过多，请重试!");
			}
		}
		else
		{
			//更新formdata
		}
		
	}
}