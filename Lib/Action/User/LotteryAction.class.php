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
		$list=M('Lottery')->where(array('token'=>session('token'),'type'=>1))->select();
		//echo M('Lottery')->getLastSql();
		$this->assign('count',M('Lottery')->where(array('token'=>session('token'),'type'=>1))->count());
		$this->assign('list',$list);
		$this->display();
	
	}
	public function sn(){
		if(session('gid')==1){
			$this->error('vip0无法使用抽奖活动,请充值后再使用',U('User/Index/index'));
		}
		$id=$this->_get('id');
		$data=M('Lottery')->where(array('token'=>session('token'),'id'=>$id))->find();
		if(!$data){
			$this->error("活动不存在");
		}
		$map = array('token'=>session('token'),'lid'=>$id,'islottery'=>1);
		if(isset($_REQUEST['filter']) && !empty($_REQUEST['filter'])){
			$map = array_merge($map,array_filter($_REQUEST['filter']));
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
			$where=array('id'=>$_POST['id'],'token'=>$_POST['token']);
			$check=$data->where($where)->find();
			if($check==false)$this->error('非法操作');
			if($data->create()){
				if(false !== $data->where($where)->save($_POST)){
					$data1['pid']=$_POST['id'];
					$data1['module']='Lottery';
					$data1['token']=session('token');
					$da['keyword']=$_POST['keyword'];
					M('Keyword')->where($data1)->save($da);
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
			$check=$data->where($where)->find();
			if($check==false)$this->error('非法操作');
			$lottery=$data->where($where)->find();
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
		$objPHPExcel->setActiveSheetIndex(0)
		            ->setCellValue('A1', '用户唯一id')
		            ->setCellValue('B1', '中奖sn号')
		            ->setCellValue('C1', '奖项')
		            ->setCellValue('D1', '中奖时间')
		            ->setCellValue('E1', '领奖时间')
		            ->setCellValue('F1', '手机号')
		            ->setCellValue('G1', '微信号')
		            ->setCellValue('H1', '身份证');
		//读取数据
		$map = array(
			"token"=>$this->token,
			"islottery"=>1,
			"lid"=>$id
		);
		$list = M("LotteryRecord")->field("wecha_id,sn,prize,time,sendtime,phone,wecha_name,idnumber")->where($map)->select();
		$i=2;
		foreach($list as $item){
			$item['time'] = date("Y-m-d H:i:s",$item['time']);
			$item['sendtime'] = date("Y-m-d H:i:s",$item['sendtime']);
			$objPHPExcel->setActiveSheetIndex(0)
		            ->setCellValue('A'.$i, $item['wecha_id'])
		            ->setCellValue('B'.$i, $item['sn'])
		            ->setCellValue('C'.$i, $item['prize'])
		            ->setCellValue('D'.$i, $item['time'])
		            ->setCellValue('E'.$i, $item['sendtime'])
		            ->setCellValue('F'.$i, $item['phone'])
		            ->setCellValue('G'.$i, $item['wecha_name'])
		            ->setCellValue('H'.$i, $item['idnumber']);
		    $i++;
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
}


?>
