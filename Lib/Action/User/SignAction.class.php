<?php
/*
by 先兆
 */
class SignAction extends UserAction {
	private $_token;
	function _initialize(){
		parent::_initialize();
		$this->_token = $this->token?$this->token:session('token');//获得token
	}
	//活动列表
	function index(){
		$M = M('SignRecord');
		$where = array('token'=>$this->_token);
		//附加条件处理
		if($filter = I('filter'))
		{
			//uid
			if( isset($filter['uid']) && $filter['uid']>0 )
			{
				$where['wecha_user_id'] = $filter['uid'];
			}
			//
			if($filter['stime'] != ""){
				$stime = strtotime($filter['stime']);//开始时间
			}else{
				$stime = 0;
			}
			if($filter['etime'] != ""){
				$etime = strtotime($filter['etime'])+86400;//结束时间
			}else{
				$etime = strtotime(date("Y-m-d",time()))+86400;//获取明天0点的时间戳
			}
			$where['lasttime'] = array("between",array($stime,$etime));//确定时间区间
		}
		$count      = $M->where($where)->count();//总数
		$Page       = new Page($count,20);
		$show       = $Page->show();
		$list = $M->field('*')->where($where)->order('lasttime desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		$this->assign('pagestr',$show);
		$this->assign("list",$list);
		$this->display();
	}
	
	function settings(){

		$model = M('Sign');
		$info = $model->where(array('token'=>$this->_token))->find();
		if(IS_POST){
			$data = $_POST;
			$data['token'] = $this->_token;
			if($model->create($data)){
				if($info){
					$re = $model->save();
				}else{
					$re = $model->add();
				}
				if($re !== false){
					$this->success("保存成功");
				}else{
					$this->error("数据库错误");
				}
			}else{
				$this->error("创建表单失败");
			}
		}else{
			if($info){
				$this->assign("info",$info);
			}
			$this->display();
		}
	}

}