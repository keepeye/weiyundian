<?php
/*
旅行社模块
by 成先兆
*/
class TravelAction extends UserAction {
	function __construct(){
		parent::__construct();
		if($this->_get('token') != session('token')){
			$this->error("非法操作");
		}
	}
	//线路管理首页
	function index(){
		C('TOKEN_ON',false);
		
		$where['token'] = session('token');
		$M = M('TravelRoute');
		
		$key = $this->_request('searchkey');
		if(!empty($key)){
			$where['title'] = array('like',"%{$key}%");
		}
		
		
		$count      = $M->where($where)->count();
		$Page       = new Page($count,20);
		$show       = $Page->show();
		$list = $M->field('*')->where($where)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		$this->assign('pagestr',$show);
		$this->assign("list",$list);
		$this->display();
	}
	
	//添加线路
	function addRoute(){
		
		if(!IS_POST){
			$this->display();
		}else{
			$data = $this->_post();
			$data['token'] = session('token');
			$M = D('TravelRoute');
			if($M->create($data)){
				if($M->add()){
					$this->success("添加成功");
				}else{
					$this->error($M->getDbError());
				}
			}else{
				$this->error($M->getError());
			}
		}
	}
	//修改线路
	function editRoute(){
		
		$id = isset($_POST['id'])?$_POST['id']:(isset($_GET['id'])?$_GET['id']:0);
		if(!is_numeric($id) || $id <= 0){
			$this->error('非法参数');
		}
		if(!IS_POST){
			$info = M('TravelRoute')->where(array("id"=>$id,"token"=>session('token')))->find();
			if(!$info){
				$this->error('非法操作：数据不存在');
			}
			if(!empty($info['actions'])) $info['actions'] = unserialize($info['actions']);
			if(!empty($info['start_date'])) $info['start_date'] = unserialize($info['start_date']);
			if(!empty($info['imgurls'])) $info['imgurls'] = unserialize($info['imgurls']);
			if(!empty($info['flag'])) $info['flag'] = explode(",",$info['flag']);
			$this->assign('info',$info);
			$this->display();
		}else{
			$M = D('TravelRoute');
			if($M->create($data)){
				if(false !== $M->save()){
					$this->success("修改成功");
				}else{
					$this->error($M->getDbError());
				}
			}else{
				$this->error($M->getError());
			}
		
		}
		
	} 
	//删除线路
	function delRoute(){
		$token = session('token');
		$ids = isset($_REQUEST['ids'])?explode(",",$_REQUEST['ids']):(isset($_REQUEST['id'])?explode(",",$_REQUEST['id']):array());
		$ids = array_filter($ids);
		if(empty($ids)){
			$this->error('非法操作');
		}
		foreach($ids as $id){
			$re = M('TravelRoute')->where(array("id"=>$id,"token"=>$token))->delete();
			if($re){
				M('TravelRouteApply')->where(array('route_id'=>$id))->delete();
			}
		}
		$this->success('删除成功');
	}
	//报名列表
	function applyList(){
		$M = M('TravelRouteApply');
		$map = array(
			"b.token"=>session("token"),
			"a.status"=>array('egt','0'),
		);
		$list = $M->alias('a')->field("a.*,b.title as route_title")->join("__TRAVEL_ROUTE__ as b on b.id=a.route_id")->where($map)->order("status ASC,addtime DESC")->select();
		$this->assign('list',$list);
		$this->display();
	}
	//处理报名
	function applyDo(){
		if(!IS_POST){
			$id = $this->_get('id');
			$token = session('token');
			$M = M('TravelRouteApply');
			$map = array(
				"b.token"=>$token,
				"a.id"=>$id,
				"a.status"=>'0'
			);
			$info = $M->alias('a')->field("a.*,b.title as route_title")->join("__TRAVEL_ROUTE__ as b on b.id=a.route_id")->where($map)->find();//查询订单信息
			if(empty($info)){
				$this->error('订单不存在或已被处理过');
			}
			$this->assign('info',$info);
			$this->display();
		}else{
			$ids = $this->_post('ids');
			$id = $this->_post('id');
			$token = session('token');
			if(!empty($ids) && !is_array($ids)){
				$idarr = explode(",",$ids);//将批量id转换为数组
			}else{
				$idarr[] = $id;//将单个id也放到数组里
			}
			unset($ids,$id);//销毁两个不再使用的变量
			foreach($idarr as $id){
				$ck = M('TravelRouteApply')->field("a.id")->alias('a')->join("__TRAVEL_ROUTE__ as b on b.id=a.route_id")->where(array("a.id"=>$id,"b.token"=>$token))->find();
				if(!$ck){
					$this->error('线路不存在或非法请求');
				}
				$re = M('TravelRouteApply')->data(array("status"=>'1'))->where(array('id'=>$id))->save();
			}
			$this->success('处理完成',U('Travel/applyList',array('token'=>$token)));
		}
	}
	
	//设置
	function settings(){
		$token = session('token');
		$M = D('TravelContact');//实例化模型
		if(!IS_POST){
			//联系人信息
			$info = $M->where(array('token'=>$token))->find();
			//回复信息
			$reply = M('ReplyInfo')->where(array('token'=>$token,'infotype'=>'Travel'))->find();
			$this->assign('reply',$reply);
			$this->assign('info',$info);
			$this->display();
		}else{
			
			//处理联系人信息
			$re = $M->field('token')->where(array('token'=>$token))->find();//先查询是否有记录，用于后面判断是add还是save
			$_POST['token'] = $token;
			if($M->create()){
				if($re){
					if(false === $M->save()){
						$this->error('保存联系人信息失败');
					}
				}else{
					if(!$M->add()){
						$this->error('添加联系人信息失败');
					}
				}
			}else{
				$this->error('创建数据失败');
			}
			
			//处理自动回复信息
			$reply_data = $_POST['reply'];
			$M1 = M('ReplyInfo');
			if(is_array($reply_data)){
				$re = $M1->field('id')->where("token='{$token}' and infotype='Travel'")->find();//先查询是否有记录，用于后面判断是add还是save
				$reply_data['infotype'] = 'Travel';//设置回复类型为Travel
				$reply_data['keyword'] = '旅游';
				$reply_data['token'] = $token;
				if($re){
					//save
					$M1->where(array('token'=>$token,'infotype'=>'Travel'))->data($reply_data)->save();
					$this->success('保存成功');
				}else{
					C('TOKEN_ON',false);
					$_POST = $reply_data;
					$this->all_insert('ReplyInfo','/settings?token='.$token);
				}
			}else{
				$this->success('保存成功');
			}
			
			
		}
	}
}