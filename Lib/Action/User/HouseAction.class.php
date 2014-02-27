<?php
class HouseAction extends UserAction{
	//构造函数获取token by成
	function __construct(){
		parent::__construct();
		$this->token = session('token');
		if(!$this->token){
			$this->error('非法访问');
		}
		$this->assign("token",$this->token);
	}
	//楼盘设置
	public function set(){
		$house=M('House')->where(array('token'=>session('token')))->find();
		if(IS_POST){
			if($house==false){
				$this->all_insert('House','/set');
			}else{
				$_POST['id']=$house['id'];
				$this->all_save('House','/set');
			}
		}else{
			$this->assign('house',$house);
			$this->display();
		}
	}
	//子楼盘列表 by成
	function category(){
		$list = M('house_category')->where(array('token'=>$this->token))->select();
		$this->assign('list',$list);
		$this->display();
	}
	//子楼盘添加、修改 by成
	function categorySet(){
		if(!IS_POST){
			$id = $this->_get('id');
			if(!empty($id)){
				$info = M('house_category')->where(array('id'=>$id,'token'=>$this->token))->find();
				if(empty($info)){
					$this->error('该子楼盘不存在');
				}
				$this->assign('info',$info);
			}
			$this->display();
		}else{
			$id = $this->_post('id');
			$M = M('house_category');
			//设置自动验证
			$validate = array(
				array('name','require','楼盘名必须！'),
				array('info','/^.{0,200}$/isu','简介太长'),
			);
			$M->setProperty("_validate",$validate);
			//设置自动完成
			$auto = array ( 
				array('token',$this->token,1),//商户token
			);
			$M-> setProperty("_auto",$auto);

			if($M->create()){
				if($id){
					$re = $M->save();
					$errText = '数据保存失败';
				}else{
					$re = $M->add();
					$errText = '数据添加失败';
				}
				if($re !== false){
					$this->success('ok',U('category'));
				}else{
					$this->error($errText);
				}
			}else{
				$this->error($M->getError());
			}
		}
	}
	//子楼盘删除，同时删除关联户型
	function categoryDel(){
		$id = $this->_get('id');
		if(!$id){
			$this->error("未指定id");
		}
		M()->startTrans();//启动事务
		$re = M('house_category')->where(array('token'=>$this->token,'id'=>$id))->delete();
		if($re === false){
			M()->rollback();//回滚事务
			$this->error('删除子楼盘失败，数据库错误');
		}
		//删除关联户型
		$re = M('house_room')->where(array('category_id'=>$id,'token'=>$this->token))->delete();
		if($re === false){
			M()->rollback();//回滚事务
			$this->error('删除关联户型数据失败，数据库错误');
		}
		M()->commit();//提交事务
		$this->redirect('category');
	}
	//户型设置
    public function room_index(){
        $db=D('HouseRoom');
		$where['token']=session('token');
		$count=$db->where($where)->count();
		$page=new Page($count,25);
		$rooms=$db->where($where)->order('`order` desc')->limit($page->firstRow.','.$page->listRows)->select();
		$this->assign('page',$page->show());
		$this->assign('rooms',$rooms);
		$this->display();
    }   
    //添加户型
    public function room_add(){
    	if(IS_POST){
    		$this->all_insert('HouseRoom','/room_index');
    	}else{
			//读取子楼盘分类by成
			$cateList = M('house_category')->field('id,name')->where(array('token'=>$this->token))->select();
			$this->assign('cateList',$cateList);
			//
    		$db=M('Photo');
    		$photos = $db->where(array('token'=>session('token')))->select();
    		$this->assign('photos',$photos);
    		$this->display("room_set");
    	}
    }
    
    //添加和修改公用同一个模版 House_room_set.html
    public function room_edit(){
    	if(IS_POST){
    		$this->all_save("HouseRoom","/room_index");
    	}
    	else{
			//读取子楼盘分类by成
			$cateList = M('house_category')->field('id,name')->where(array('token'=>$this->token))->select();
			$this->assign('cateList',$cateList);
			//
    		$id=$this->_get('id','intval');
    		$room=M('HouseRoom')->find($id);
    		$this->assign('room',$room);
    		$db=M('Photo');
    		$photos = $db->where(array('token'=>session('token')))->select();
    		$this->assign('photos',$photos);
    		$this->display("room_set");
			
    	}
    }

    //删除户型
    public function room_del(){
        $where['id']=$this->_get('id','intval');
        if(D("HouseRoom")->where($where)->delete()){
            $this->success('操作成功',U(MODULE_NAME.'/room_index'));
        }else{
            $this->error('操作失败',U(MODULE_NAME.'/room_index'));
        }
    }
    
    //参考相册添加照片列表的做法
    public function add_360(){
    	$room_id=$this->_get("room_id");
    	if(IS_POST){
    		$db   = D("HouseRoom360");
    		if ($db->create() === false) {
    			$this->error($db->getError());
    		} else {
    			$id = $db->add();
    			if ($id == true) {
    				$this->success('操作成功', U("add_360",array("room_id"=>$room_id)));
    			} else {
    				$this->error('操作失败', U("add_360",array("room_id"=>$room_id)));
    			}
    		}
    	}
    	else{
    		$images=D("HouseRoom360")->where(array("room_id"=>$room_id))->select();
    		$this->assign("images",$images);
    		$this->assign("room_id",$room_id);
    		$this->display();
    	}
    }
	
    public function edit_360(){
    	$room_id=$this->_post("room_id");
    	if(IS_POST){
    	$db   = D("HouseRoom360");
    		if ($db->create() === false) {
    			$this->error($db->getError());
    		} else {
    			$id = $db->save();
    			if ($id == true) {
    				$this->success('操作成功', U("add_360",array("room_id"=>$room_id)));
    			} else {
    				$this->error('操作失败', U("add_360",array("room_id"=>$room_id)));
    			}
    		}
    	}
    }
    
    public function del_360(){
    	$room_id=$this->_get("room_id");
    	$where['id']=$this->_get('id','intval');
    	if(D("HouseRoom360")->where($where)->delete()){
    		$this->success('操作成功',U(MODULE_NAME.'/add_360',array("room_id"=>$room_id)));
    	}else{
    		$this->error('操作失败',U(MODULE_NAME.'/add_360',array("room_id"=>$room_id)));
    	}
    }
	
	//印象列表 by成
	function impress(){
		$list = M('house_impress')->where(array('token'=>$this->token))->select();
		$this->assign('list',$list);
		$this->display();
	}
	
	//印象增加修改
	function impressSet(){
		if(!IS_POST){
			$id = $this->_get('id');
			if(!empty($id)){
				$info = M('house_impress')->where(array('id'=>$id,'token'=>$this->token))->find();
				if(empty($info)){
					$this->error('该印象不存在');
				}
				$this->assign('info',$info);
			}
			$this->display();
		}else{
			$id = $this->_post('id');
			$M = M('house_impress');
			//设置自动验证
			$validate = array(
				array('title','require','标题必须！'),
			);
			$M->setProperty("_validate",$validate);
			//设置自动完成
			$auto = array ( 
				array('token',$this->token,1),//商户token
			);
			$M-> setProperty("_auto",$auto);

			if($M->create()){
				if($id){
					$re = $M->save();
					$errText = '数据保存失败';
				}else{
					$re = $M->add();
					$errText = '数据添加失败';
				}
				if($re !== false){
					$this->success('ok',U('impress'));
				}else{
					$this->error($errText);
				}
			}else{
				$this->error($M->getError());
			}
		}
	}
	
	//印象删除
	function impressDel(){
		$id = $this->_get('id');
		M('house_impress')->where(array('token'=>$this->token,'id'=>$id))->delete();
		$this->redirect('impress');
	}
	
	//专家点评 by 成
	function dianping(){
		$list = M('house_dianping')->where(array('token'=>$this->token))->select();
		$this->assign('list',$list);
		$this->display();
	}
	//点评添加、修改 by成
	function dianpingSet(){
		if(!IS_POST){
			$id = $this->_get('id');
			if(!empty($id)){
				$info = M('house_dianping')->where(array('id'=>$id,'token'=>$this->token))->find();
				if(empty($info)){
					$this->error('该点评不存在');
				}
				$this->assign('info',$info);
			}
			$this->display();
		}else{
			$id = $this->_post('id');
			$M = M('house_dianping');
			//设置自动验证
			$validate = array(
				array('title','require','标题必须！'),
				array('name','require','专家姓名必须！'),
				array('position','require','专家职位必须！'),
				array('pic','require','专家照片必须！'),
				array('intro','require','专家必须！'),
			);
			$M->setProperty("_validate",$validate);
			//设置自动完成
			$auto = array ( 
				array('token',$this->token,1),//商户token
			);
			$M-> setProperty("_auto",$auto);

			if($M->create()){
				if($id){
					$re = $M->save();
					$errText = '数据保存失败';
				}else{
					$re = $M->add();
					$errText = '数据添加失败';
				}
				if($re !== false){
					$this->success('ok',U('dianping'));
				}else{
					$this->error($errText);
				}
			}else{
				$this->error($M->getError());
			}
		}
	}
	
	//点评删除 by成
	function dianpingDel(){
		$id = $this->_get('id');
		M('house_dianping')->where(array('token'=>$this->token,'id'=>$id))->delete();
		$this->redirect('dianping');
	}
}

