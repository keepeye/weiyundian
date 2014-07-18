<?php
class ZajindanAction extends UserAction
{
	function __construct(){
		parent::__construct();
		if(!$this->token){
			$this->error('非法访问');
		}
		$this->assign("token",$this->token);
		C('TOKEN_ON',false);
	}

	//默认首页
	function index()
	{
		$m = M('Zajindan');
		//读取活动列表
		$list = $m->field("m.*,k.keyword")->alias('m')->join("left join __KEYWORD__ k ON k.pid=m.id AND k.token='{$this->token}' AND k.module='Zajindan'")->where(array("m.token"=>$this->token))->select();
		
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
		$m = M('Zajindan');
		if( ! $id || ! ($huodong = $m->where(array("id"=>$id,"token"=>$this->token))->find()))
		{
			$this->error("活动不存在");
		}
		if(!empty($huodong['formset'])){
			$huodong['formset'] = unserialize($huodong['formset']);
		}
		//读取关键词
		$keyword = M('Keyword')->where(array("token"=>$this->token,'pid'=>$id,'module'=>'Zajindan'))->getField('keyword');
		$this->assign("info",$huodong);
		$this->assign('keyword',$keyword);
		$this->display();
	}
	
	//更新数据
	function update()
	{
		if( ! IS_POST)
		{
			$this->error("非法提交");
		}
		$id = (int)$_POST['info']['id'];
		$m = M('Zajindan');
		$_POST['info']['token'] = $this->token;
		//处理formset
		$_POST['info']['formset'] = $this->parseformset();
		//时间格式转换
		$_POST['info']['stime'] = strtotime($_POST['info']['stime']);
		$_POST['info']['etime'] = strtotime($_POST['info']['etime']);
		if($zdata = $m->create($_POST['info']))
		{
			if($id)
			{
				$re = $m->save();//更新记录
			}
			else
			{
				$id = $re = $m->add();//新增记录
			}
			if($re === false)
			{
				$this->error("error:数据库操作失败");
			}
			else
			{
				//处理关键词
				if ( ! empty($_POST['keyword'])) {
		            $data['pid']     = $id;//主键
		            $data['module']  = 'Zajindan';
		            $data['token']   = $this->token;
		            $data['keyword'] = $_POST['keyword'];
		            //删除旧的关键词记录
		            M('Keyword')->where(array("pid"=>$id,"module"=>'Zajindan','token'=>$this->token))->delete();
		            //重新插入关键词
		            M('Keyword')->add($data);
		        }
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
		$id = I('id',0,'intval');
		//删除奖品和中奖记录
		
		M()->execute("DELETE m,r,p,sn FROM `tp_zajindan` m LEFT JOIN `tp_zajindan_record` r ON r.pid=m.id LEFT JOIN `tp_zajindan_prize` p ON p.pid=m.id LEFT JOIN `tp_zajindan_sn` sn ON sn.pid=p.id WHERE m.id='{$id}' AND m.token='{$this->token}'");
		//删除关键词
		M('Keyword')->where(array("token"=>$this->token,'pid'=>$id,'module'=>'Zajindan'))->delete();

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

	//奖品设置
	function prize(){
		$id = I('id',0,'intval');
		$m = M('Zajindan');
		if( ! $id || ! ($huodong = $m->where(array("id"=>$id,"token"=>$this->token))->find()))
		{
			$this->error("活动不存在");
		}
		
		if( ! IS_POST){
			//读取奖品列表
			$prizes = M('ZajindanPrize')->where(array("pid"=>$huodong['id']))->select();
			$this->assign("prizes",$prizes);
			$this->assign("huodong",$huodong);
			$this->display();
		}else{
			//新的奖品
			$newprizes = $_POST['new'];
			foreach($newprizes as $k=>&$v){
				if(!empty($v['name'])){
					$v['pid'] = $huodong['id'];
				}else{
					unset($newprizes[$k]);
				}
			}
			M('ZajindanPrize')->addAll($newprizes);//批量插入新奖品
			//更新旧奖品设置
			$oldprizes = $_POST['old'];
			foreach($oldprizes as &$oldprize){
				M('ZajindanPrize')->where(array("pid"=>$huodong['id'],"id"=>$oldprize['id']))->data($oldprize)->save();
			}
			//删除的奖品
			M('ZajindanPrize')->where(array("pid"=>$huodong['id'],"id"=>array("in",I('delids',array()))))->delete();
			$this->success("保存成功");
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