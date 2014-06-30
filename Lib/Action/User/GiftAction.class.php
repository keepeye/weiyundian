<?php
class GiftAction extends UserAction
{
	function __construct(){
		parent::__construct();
		if(!$this->token){
			$this->error('非法访问');
		}
		$this->assign("token",$this->token);
	}

	//默认首页
	function index()
	{
		$m = M('Gift');
		$list = $m->order("sort asc")->select();

		$this->assign("list",$list);
		$this->display();
	}

	//添加礼品
	function add()
	{
		$this->display("edit");
	}

	//编辑礼品
	function edit()
	{
		$id = I('id',0);
		$m = M('Gift');
		if( ! $id || ! ($gift = $m->where(array("id"=>$id,"token"=>$this->token))->find()))
		{
			$this->error("礼品不存在");
		}
		if(!empty($gift['formset'])){
			$gift['formset'] = unserialize($gift['formset']);
		}
		
		$this->assign("gift",$gift);
		$this->display();
	}
	
	//更新数据
	function update()
	{
		if( ! IS_POST)
		{
			$this->error("非法提交");
		}
		$id = I('id',0);
		$m = M('Gift');
		$_POST['token'] = $this->token;
		//处理formset
		$_POST['formset'] = $this->parseformset();
		if($m->create())
		{
			if($id)
			{
				$re = $m->save();
			}
			else
			{
				$id = $re = $m->add();
			}
			if($re === false)
			{
				$this->error("error:".$m->getDbError());
			}
			else
			{
				$this->success("保存成功");
			}
		}
		else
		{
			$this->error("创建数据失败");
		}
	}

	//删除礼品
	function delete()
	{
		$id = I('id',0);
		$m = M('Gift');
		$m->where(array("id"=>$id,"token"=>$this->token))->delete();
		$this->success("删除成功");
	}

	//处理formset字段
	function parseformset(){
		$origin = $_POST['formset'];//原始POST数据
		$data = array();
		foreach($origin['id'] as $k=>$v){
			if(trim($v)=="" || trim($origin['name'][$k])=="") continue;
			$data[] = array(
				"id"=>$v,
				"name"=>$origin['name'][$k],
				"type"=>$origin['type'][$k],
				"value"=>$origin['value'][$k]
			);
		}
		return serialize($data);
	}

	//设置
	function config()
	{
		$m = M('GiftConfig');
		$config = $m->where(array("token"=>$this->token))->find();

		if( ! IS_POST)
		{
			if($config)
			{
				$this->assign("config",$config);
			}
			$this->display();
		}
		else
		{
			$_POST['token'] = $this->token;
			if($m->create())
			{
				if($config)
				{
					$re = $m->save();
				}
				else
				{
					$re = $m->add();
				}
				if($re === false)
				{
					$this->error("error:".$m->getDbError());
				}
				else
				{
					$this->success("保存成功");
				}
			}
			else
			{
				$this->error("创建数据失败");
			}
		}
		
	}

	//领取记录
	function sn()
	{
		$id = I('id',0);
		$m = M('Gift');
		if( ! $id || ! ($gift = $m->where(array("id"=>$id,"token"=>$this->token))->find()))
		{
			$this->error("礼品不存在");
		}
		$gift['formset'] = unserialize($gift['formset']);
		$this->assign("gift",$gift);
		//sn列表
		$map = array(
			"pid" => $id,
			"token" => $this->token
		);
		//搜索条件
		if(isset($_REQUEST['filter']) && !empty($_REQUEST['filter'])){
			$filters = array();
			foreach($_REQUEST['filter'] as $k=>$v){
				if($k == "formdata")
				{
					$filters[$k] = array("like","%{$v}%");
				}
				else
				{
					$filters[$k] = $v;
				}

			}
			
			$map = array_merge($map,array_filter($filters));
		}
		//数量
		$count      = M('GiftSn')->where($map)->count();
		$Page       = new Page($count,20);
		$pagestr       = $Page->show();
		$this->assign('pagestr',$pagestr);
		$list = M('GiftSn')->where($map)->limit($Page->firstRow.','.$Page->listRows)->select();
		$this->assign("list",$list);
		$this->display();
	}

	//标记为已处理
	function snmark()
	{
		$sn = I('sn');
		$pid = I('pid');
		$where = array(
			"token" => $this->token,
			"pid" => $pid,
			'sn' => $sn
		);
		$re = M('GiftSn')->where($where)->data(array("status"=>1))->save();
		if($re === false)
		{
			$this->ajaxReturn(array("status"=>0,"info"=>"保存失败"));
		}
		else
		{
			$this->ajaxReturn(array("status"=>1,"info"=>"ok"));
		}
	}

	//删除sn记录
	function delsn()
	{
		$sn = I('sn');
		$pid = I('pid');
		$where = array(
			"token" => $this->token,
			"pid" => $pid,
			'sn' => $sn
		);
		$re = M('GiftSn')->where($where)->delete();
		if($re === false)
		{
			$this->ajaxReturn(array("status"=>0,"info"=>"删除失败"));
		}
		else
		{
			$this->ajaxReturn(array("status"=>1,"info"=>"ok"));
		}
		
	}
}