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