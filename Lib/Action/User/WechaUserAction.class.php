<?php
class WechaUserAction extends UserAction
{
	function __construct(){
		parent::__construct();
		if(!$this->token){
			$this->error('非法访问');
		}
		$this->assign("token",$this->token);
	}

	function index()
	{
		$M = M('WechaUser');
		$where = array('token'=>$this->token);

		//有附加条件
		$filter = I('filter');
		if($filter)
		{
			//uid
			if( isset($filter['uid']) && $filter['uid']>0 )
			{
				$where['id'] = $filter['uid'];
			}
		}
		$total = $M->where($where)->count();//列表总数

		import('@.ORG.Page');//引入Page类
		$Page = new Page($total,20);
		$pagestr = $Page->show();

		$list = $M->where($where)->limit($Page->firstRow.','.$Page->listRows)->select();
		$this->assign('pagestr',$pagestr);
		$this->assign('list',$list);
		$this->display();
	}

	//编辑用户视图
	function edit()
	{
		$uid = I('id',0);
		if( ! $uid || ! $user = M('WechaUser')->find($uid))
		{
			$this->error("用户不存在或未指定uid");
		}
		if( ! IS_POST)
		{
			$this->assign("user",$user);
			$this->display();
		}
		else
		{
			$M = M('WechaUser');
			if($M->create())
			{
				if($M->save() === false)
				{
					$this->error("sql错误");
				}
			}
			else
			{
				$this->error("创建数据失败");
			}
			$this->success("保存成功");
		}
		
	}
	
}