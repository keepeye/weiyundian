<?php
class LotteryAction extends UserAction{
	public function index(){
		if(session('gid')==1){
			$this->error('vip0无法使用抽奖活动,请充值后再使用',U('User/Index/index'));
		}
		$user=M('Users')->field('gid,activitynum')->where(array('id'=>session('uid')))->find();
		$group=M('User_group')->where(array('id'=>$user['gid']))->find();
		$this->assign('group',$group);
		$this->assign('activitynum',$user['activitynum']);
		$where = array('token'=>session('token'),'type'=>1);
		//子账户显示列表
		if(($sub_uid = session('sub_uid'))!=""){
			$article_ids = M('WxuserSubAccessRow')->where(array("token"=>$this->token,"sub_uid"=>$sub_uid,"module"=>"lottery"))->getField("article_id",true);
			$where['id']=array("in",$article_ids);
		}
		$list=M('Lottery')->where($where)->select();
		//echo M('Lottery')->getLastSql();
		$this->assign('count',M('Lottery')->where(array('token'=>session('token'),'type'=>1))->count());
		$this->assign('list',$list);
		$this->display();
	}
	public function sn(){
		
		$id=$this->_get('id');
		$data=M('Lottery')->where(array('token'=>session('token'),'id'=>$id))->find();

		if(!$data){
			$this->error("活动不存在");
		}
		//formset
		if(!empty($data['formset'])){
			$formset = json_decode($data['formset'],true);
			$data['formset'] = array();
			//dump($formset);
			foreach($formset as $fieldset){
				$data['formset'][$fieldset['id']] = $fieldset;
			}

		}

		$this->assign("lottery",$data);
		$map = array('token'=>session('token'),'lid'=>$id,'islottery'=>1);
		//搜索条件
		if(isset($_REQUEST['filter']) && !empty($_REQUEST['filter'])){
			$filters = array();
			foreach($_REQUEST['filter'] as $k=>$v){
				if($k == "formdata"){
					$v = preg_replace_callback(
				        "/[\x{4e00}-\x{9fa5}]+/u",
				        create_function(
				            '$matches',
				            'return addslashes(trim(json_encode($matches[0]),chr(34)));'
				        ),
				        $v
				    );
				}
				$filters[$k] = array("like","%{$v}%");
			}
			
			$map = array_merge($map,array_filter($filters));
		}
		
		$recordcount=M('Lottery_record')->where($map)->count();//中奖总数

		//分页
		$count      = $recordcount;
		$Page       = new Page($count,20);
		$pagestr       = $Page->show();
		$record=M('Lottery_record')->where($map)->order('`time` desc')->limit($Page->firstRow.','.$Page->listRows)->select();//中奖列表

		$this->assign('pagestr',$pagestr);
		//分页结束
		$datacount=$data['fistnums']+$data['secondnums']+$data['thirdnums']+$data['fournums']+$data['fivenums']+$data['sixnums'];//奖品总数
		$this->assign("data",$data);
		$this->assign('datacount',$datacount);
		$this->assign('recordcount',$recordcount);
		$this->assign('record',$record);
		$this->display();	
	}
	public function add(){
		if(session('gid')==1){
			$this->error('vip0无法使用抽奖活动,请充值后再使用',U('User/Index/index'));
		}
		$user=M('Users')->field('gid,activitynum')->where(array('id'=>session('uid')))->find();
		$group=M('User_group')->where(array('id'=>$user['gid']))->find();
		if($user['activitynum']>=$group['activitynum']){
			$this->error('您的免费活动创建数已经全部使用完,请充值后再使用',U('User/Index/index'));
		}		
		
		if(IS_POST){
			$user=M('Users')->where(array('id'=>session('uid')))->setInc('activitynum');
			//add the use times . 
			$_POST['statdate']=strtotime($this->_post('statdate'));
			$_POST['enddate']=strtotime($this->_post('enddate'));
			$_POST['token']=session('token');
			$_POST['interval'] = ($this->_post('interval') == '1')?86400:0;//抽奖时间限制，以秒计，但前台给用户选择以1天计
			//处理formset
			$_POST['formset'] = $this->parseformset();
			$this->all_insert('Lottery');
		}else{
			$lottery["starpicurl"]="/tpl/Wap/default/common/css/guajiang/images/activity-lottery-start.jpg";
			$lottery["endpicurl"]="/tpl/Wap/default/common/css/guajiang/images/activity-lottery-end.jpg";
			$this->assign('vo',$lottery);
			$this->display();
		}
	}
	public function setinc(){
		if(session('gid')==1){
			$this->error('vip0无法开启活动,请充值后再使用',U('User/Index/index'));
		}
		$id=$this->_get('id');
		$where=array('id'=>$id,'token'=>session('token'));
		$check=M('Lottery')->where($where)->find();
		if($check==false)$this->error('非法操作');
		$data=M('Lottery')->where($where)->data(array('status'=>1))->save();
		if($data!=false){
			$this->success('恭喜你,活动已经开始');
		}else{
			$this->error('服务器繁忙,请稍候再试');
		}

	}
	public function setdes(){
		$id=$this->_get('id');
		$where=array('id'=>$id,'token'=>session('token'));
		$check=M('Lottery')->where($where)->find();
		if($check==false)$this->error('非法操作');
		$data=M('Lottery')->where($where)->data(array("status"=>0))->save();
		if($data!=false){
			$this->success('活动已经结束');
		}else{
			$this->error('服务器繁忙,请稍候再试');
		}
	
	}
	public function edit(){
		if(IS_POST){
			$data=D('Lottery');
			$_POST['id']=$this->_get('id');
			$_POST['token']=session('token');
			$_POST['statdate']=strtotime($_POST['statdate']);
			$_POST['enddate']=strtotime($_POST['enddate']);
			$_POST['interval'] = ($this->_post('interval') == '1')?86400:0;//抽奖时间限制，以秒计，但前台给用户选择以1天计
			if(empty($_POST['fist']) || empty($_POST['fistnums'])){
				$this->error('必须设置一等奖奖品和数量');
				exit;
			}
			//处理formset
			$_POST['formset'] = $this->parseformset();
			$where=array('id'=>$_POST['id'],'token'=>$_POST['token']);
			$check=$data->where($where)->find();
			if($check==false)$this->error('非法操作');
			if($data->create()){
				if(false !== $data->where($where)->save($_POST)){
					$data1['pid']=$_POST['id'];
					$data1['module']='Lottery';
					$data1['token']=session('token');
					M('Keyword')->where($data1)->delete();//删除旧关键词
					$keywords = explode(" ",$_POST['keyword']);//关键词按空格分隔
                    foreach($keywords as $keyword){
                        $data1['keyword'] = $keyword;
                        M('Keyword')->add($data);
                    }

					$this->success('修改成功',U('Lottery/index',array('token'=>session('token'))));
				}else{
					$this->error('操作失败');
				}
			}else{
				$this->error($data->getError());
			}
		}else{
			$id=$this->_get('id');
			$where=array('id'=>$id,'token'=>session('token'));
			$data=M('Lottery');
			$lottery=$data->where($where)->find();
			if($lottery==false){
				$this->error('非法操作');
			}
			if(!empty($lottery['formset'])){
				$lottery['formset'] = json_decode($lottery['formset'],true);
			}
			$this->assign('vo',$lottery);
			//dump($_POST);
			$this->display('add');
		}
	
	}
	public function del(){
		$id=$this->_get('id');
		$where=array('id'=>$id,'token'=>session('token'));
		$data=M('Lottery');
		$check=$data->where($where)->find();
		if($check==false)$this->error('非法操作');
		$back=$data->where($wehre)->delete();
		if($back==true){
			M('Keyword')->where(array('pid'=>$id,'token'=>session('token'),'module'=>'Lottery'))->delete();//删除关键词关联数据
			M('LotteryRecord')->where(array("lid"=>$id))->delete();
			$this->success('删除成功');
		}else{
			$this->error('操作失败');
		}
	
	
	}
	function delRecord(){
		$id=I('id');
		$where=array('id'=>$id,'token'=>session('token'));
		M('LotteryRecord')->where($where)->delete();
		$this->success("删除成功");
	}
	public function sendprize(){
		$id=$this->_get('id');
		$where=array('id'=>$id,'token'=>session('token'));
		$data['sendtime'] = time();
		$data['sendstutas'] = 1;
		$back = M('Lottery_record')->where($where)->save($data);
		if($back==true){
			$this->success('成功发奖');
		}else{
			$this->error('操作失败');
		}
	}
	
	public function sendnull(){
		$id=$this->_get('id');
		$where=array('id'=>$id,'token'=>session('token'));
		$data['sendtime'] = '';
		$data['sendstutas'] = 0;
		$back = M('Lottery_record')->where($where)->save($data);
		if($back==true){
			$this->success('已经取消');
		}else{
			$this->error('操作失败');
		}
	}
	
	//导出中奖记录到excel下载
	function exportExcel(){
		$id = I('id','0','intval');//活动id
		if(!$id){
			exit('非法id');
		}
		//查询活动基本信息
		$lottery = M('Lottery')->where(array("id"=>$id,"token"=>$this->token))->find();
		if(!$lottery){
			exit('活动不存在');
		}
		import("@.ORG.phpexcel.Classes.PHPExcel",'',".php");
		$objPHPExcel = new PHPExcel();
		// 设置列名
		$excelobj = $objPHPExcel->setActiveSheetIndex(0)
		            ->setCellValue('A1', '中奖sn号')
		            ->setCellValue('B1', '奖项');

		//判断是否有自定义表单设置
		if(!empty($lottery['formset'])){
			$formset = json_decode($lottery['formset'],true);
			$column_start = ord('C');//从C列开始，取得C字母的ASCII码
			$extra_columns = array();
			foreach($formset as $field){
				$column_name = chr($column_start);
				$extra_columns[$column_name] = $field;
				$excelobj->setCellValue($column_name.'1', $field['name']);//设定一个列来存储表单字段
				$column_start += 1;//ASCII码加+1表示下一个列字母
				
			}

		}else{
			$excelobj->setCellValue('C1', '手机号')->setCellValue('D1', '姓名')->setCellValue('E1', '身份证');
		}
		//读取数据
		$map = array(
			"token"=>$this->token,
			"islottery"=>1,
			"lid"=>$id
		);
		$list = M("LotteryRecord")->field("wecha_id,sn,prize,time,sendtime,phone,myname,idnumber,formdata")->where($map)->select();
		$i=2;
		//自定义表单的情况下
		if(isset($extra_columns) && !empty($extra_columns)){
			foreach($list as $item){
				$item['time'] = $item['time']>0?date("Y-m-d H:i:s",$item['time']):"0";
				$item['sendtime'] = $item['sendtime']>0?date("Y-m-d H:i:s",$item['sendtime']):"0";
				$excelobj = $objPHPExcel->setActiveSheetIndex(0)
			            ->setCellValueExplicit('A'.$i, $item['sn'],PHPExcel_Cell_DataType::TYPE_STRING)
			            ->setCellValueExplicit('B'.$i, $item['prize'],PHPExcel_Cell_DataType::TYPE_STRING);
			    $formdata = json_decode($item['formdata'],true);//用户提交的表单数据
			    foreach($extra_columns as $k1=>$v1){
			    	$excelobj->setCellValueExplicit($k1.$i, $formdata[$v1['id']],PHPExcel_Cell_DataType::TYPE_STRING);
			    }
			    unset($k1,$v1);
			    $i++;
			}
		}else{
			foreach($list as $item){
				$item['time'] = $item['time']>0?date("Y-m-d H:i:s",$item['time']):"0";
				$item['sendtime'] = $item['sendtime']>0?date("Y-m-d H:i:s",$item['sendtime']):"0";
				$objPHPExcel->setActiveSheetIndex(0)
			            ->setCellValueExplicit('A'.$i, $item['sn'],PHPExcel_Cell_DataType::TYPE_STRING)
			            ->setCellValueExplicit('B'.$i, $item['prize'],PHPExcel_Cell_DataType::TYPE_STRING)
			            ->setCellValueExplicit('C'.$i, $item['phone'],PHPExcel_Cell_DataType::TYPE_STRING)
			            ->setCellValueExplicit('D'.$i, $item['myname'],PHPExcel_Cell_DataType::TYPE_STRING)
			            ->setCellValueExplicit('E'.$i, $item['idnumber'],PHPExcel_Cell_DataType::TYPE_STRING);
			    $i++;
			}
		}
		
		//设置sheet标题
		$objPHPExcel->getActiveSheet()->setTitle('中奖记录');
		$objPHPExcel->setActiveSheetIndex(0);

		// Redirect output to a client’s web browser (Excel5)
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="'.$lottery['title'].'中奖统计"');
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
	//增加指定用户10次抽奖机会
	function addtimes(){
		$id = I('id','0','intval');//活动id
		if(!$id){
			exit('非法id');
		}
		
		if(!IS_POST){
			$this->assign("id",$id);
			//查询活动基本信息
			$this->display();
		}else{
			$lottery = M('Lottery')->where(array("id"=>$id,"token"=>$this->token))->find();
			if(!$lottery){
				exit('活动不存在');
			}
			$openids = I('post.openids');
			$openids = explode("\n",$openids);
			foreach($openids as $k=>$v){
				$openids[$k] = trim($v);
			}
			$re = M('LotteryRecord')->where(array('lid'=>$id,'islottery'=>'0','wecha_id'=>array('in',$openids)))->setInc("usenums",10);
			if($re !== false){
				$this->success("ok");
			}else{
				$this->error($re->getDbError());
			}
		}
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

