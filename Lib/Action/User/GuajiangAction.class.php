<?php
class GuajiangAction extends UserAction{
	public function index(){
		if(session('gid')==1){
			$this->error('vip0无法使用抽奖活动,请充值后再使用',U('User/Index/index'));
		}
		$user=M('Users')->field('gid,activitynum')->where(array('id'=>session('uid')))->find();
		$group=M('User_group')->where(array('id'=>$user['gid']))->find();
		$this->assign('group',$group);
		$this->assign('activitynum',$user['activitynum']);
		$list=M('Lottery')->field('id,title,joinnum,click,keyword,statdate,enddate,status')->where(array('token'=>session('token'),'type'=>2))->select();
		//dump($list);
		$this->assign('count',M('Lottery')->where(array('token'=>session('token'),'type'=>2))->count());
		$this->assign('list',$list);
		$this->display();	
	}
	public function sn(){
		$this->redirect("Lottery/sn",array('id'=>I('id')));
		// if(session('gid')==1){
		// 	$this->error('vip0无法使用抽奖活动,请充值后再使用',U('User/Index/index'));
		// }
		// $id=$this->_get('id');
		// $data=M('Lottery')->where(array('token'=>session('token'),'id'=>$id,'type'=>2))->find();
		// if(!$data){
		// 	$this->error("刮刮卡活动不存在");
		// }
		// $map = array('token'=>session('token'),'lid'=>$id,'sn'=>array('neq',''));
		// if(isset($_REQUEST['filter']) && !empty($_REQUEST['filter'])){
		// 	$map = array_merge($map,array_filter($_REQUEST['filter']));
		// }
		// $recordcount=M('Lottery_record')->where($map)->count();
		// //分页
		// $count      = $recordcount;
		// $Page       = new Page($count,20);
		// $pagestr       = $Page->show();
		// $record=M('Lottery_record')->where($map)->order('`time` desc')->limit($Page->firstRow.','.$Page->listRows)->select();//中奖列表
		// $this->assign('pagestr',$pagestr);
		// //分页结束
		// $datacount=$data['fistnums']+$data['secondnums']+$data['thirdnums'];
		// $this->assign('datacount',$datacount);//奖品数量
		// $this->assign('recordcount',$recordcount);//中讲数量
		// $this->assign('record',$record);
		// $this->display();
	
	
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
			$_POST['type'] = 2;
			$_POST['interval'] = ($this->_post('interval') == '1')?86400:0;//抽奖时间限制，以秒计，但前台给用户选择以1天计
			//处理formset
			$_POST['formset'] = $this->parseformset();
			$this->all_insert('Lottery');
		}else{
			$lottery["starpicurl"]="/tpl/User/default/common/images/img/activity-scratch-card-start.jpg";
			$lottery["endpicurl"]="/tpl/Wap/default/common/css/guajiang/images/activity-coupon-end.jpg";
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
		$data=M('Lottery')->where($where)->data(array("status"=>1))->save();
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
			$where=array('id'=>$_POST['id'],'token'=>$_POST['token'],'type'=>2);		
			$check=$data->where($where)->find();
			if($check==false)$this->error('非法操作');
			if($data->create()){				
				if(false !== $id=$data->where($where)->save($_POST)){
					$data1['pid']=$_POST['id'];
					$data1['module']='Lottery';
					$data1['token']=session('token');
					$da['keyword']=$_POST['keyword'];
					M('Keyword')->where($data1)->save($da);
					$this->success('修改成功');
				}else{
					$this->error('操作失败');
				}
			}else{
				$this->error($data->getError());
			}
			
		}else{
			$id=$this->_get('id');
			$where=array('id'=>$id,'token'=>session('token'),'type'=>2);
			$data=M('Lottery');
			$check=$data->where($where)->find();
			if($check==false)$this->error('非法操作');
			$lottery=$data->where($where)->find();		
			if(!empty($lottery['formset'])){
				$lottery['formset'] = json_decode($lottery['formset'],true);
			}
			$this->assign('vo',$lottery);
			//dump($lottery);
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
			M('Keyword')->where(array('pid'=>$id,'token'=>session('token'),'module'=>'Lottery'))->delete();
			M('LotteryRecord')->where(array("lid"=>$id))->delete();
			$this->success('删除成功');
		}else{
			$this->error('操作失败');
		}
	
	
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


?>
