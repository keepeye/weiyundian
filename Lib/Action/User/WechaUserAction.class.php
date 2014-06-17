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
}