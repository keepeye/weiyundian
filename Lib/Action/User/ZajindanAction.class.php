<?php
class ZajindanAction extends UserAction
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
		$m = M('Zajindan');
		//读取活动列表
		$list = $m->where(array("token"=>$this->token))->select();
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
	//导出中奖记录到excel下载
	function exportExcel(){
		$id = I('id','0','intval');//礼品id
		if(!$id){
			exit('非法id');
		}
		//查询活动基本信息
		$gift = M('Gift')->where(array("id"=>$id,"token"=>$this->token))->find();
		if(!$gift){
			exit('礼品不存在');
		}
		import("@.ORG.phpexcel.Classes.PHPExcel",'',".php");
		$objPHPExcel = new PHPExcel();
		// 设置列名
		$excelobj = $objPHPExcel->setActiveSheetIndex(0)
		            ->setCellValue('A1', 'sn号')
		            ->setCellValue('B1', '用户uid');

		//自定义表单列
		if(!empty($gift['formset'])){
			$formset = unserialize($gift['formset']);
			$column_start = ord('C');//从C列开始，取得字母的ASCII码
			$extra_columns = array();
			foreach($formset as $field){
				$column_name = chr($column_start);
				$extra_columns[$column_name] = $field;
				$excelobj->setCellValue($column_name.'1', $field['name']);//设定一个列来存储表单字段
				$column_start += 1;//ASCII码加+1表示下一个列字母
				
			}

		}
		//读取数据
		$map = array(
			"a.token"=>$this->token,
			"a.pid"=>$id
		);
		$list = M("GiftSn")->field("a.*,b.id as uid")->alias("a")->join("right join __WECHA_USER__ as b on a.wecha_id=b.wecha_id")->where($map)->select();
		$i=2;
		//自定义表单的情况下
		if(isset($extra_columns) && !empty($extra_columns)){
			foreach($list as $item){
				
				$excelobj = $objPHPExcel->setActiveSheetIndex(0)
			            ->setCellValueExplicit('A'.$i, $item['sn'],PHPExcel_Cell_DataType::TYPE_STRING)
			            ->setCellValueExplicit('B'.$i, $item['uid'],PHPExcel_Cell_DataType::TYPE_STRING);
			    $formdata = unserialize($item['formdata']);//用户提交的表单数据
			    foreach($extra_columns as $k1=>$v1){
			    	$excelobj->setCellValueExplicit($k1.$i, $formdata[$v1['id']],PHPExcel_Cell_DataType::TYPE_STRING);
			    }
			    unset($k1,$v1);
			    $i++;
			}
		}
		
		//设置sheet标题
		$objPHPExcel->getActiveSheet()->setTitle('领奖记录');
		$objPHPExcel->setActiveSheetIndex(0);

		// Redirect output to a client’s web browser (Excel5)
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="'.$gift['title'].'领取统计"');
		header('Cache-Control: max-age=0');
		// If you're serving to IE 9, then the following may be needed
		header('Cache-Control: max-age=1');

		// If you're serving to IE over SSL, then the following may be needed
		header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
		header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
		header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
		header ('Pragma: public'); // HTTP/1.0

		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
		exit;

	}
}