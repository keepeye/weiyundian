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
		$list = $m->select();

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
			$gift['formset'] = json_decode($gift['formset'],true);
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
		return json_encode($data);
	}
}