<?php
/*
子店铺管理模块
by 成先兆
*/
class SubshopAction extends UserAction {
	function __construct(){
		parent::__construct();
		$this->token = session('token');
		$this->assign('token',$this->token);
	}
	
	//列表
	function lists(){
		$list = M('Subshop')->where(array('token'=>$this->token))->select();
		$this->assign('list',$list);
		$this->display();
	}
	
	//添加、编辑
	function set(){
		if(!IS_POST){
			$id = $this->_get('id');
			if(!empty($id)){
				$info = M('Subshop')->where(array('id'=>$id,'token'=>$this->token))->find();
				if(empty($info)){
					$this->error('该门店不存在');
				}
				$this->assign('info',$info);
			}
			$this->display();
		}else{
			$id = $this->_post('id');
			$M = M('Subshop');
			//设置自动验证
			$validate = array(
				array('shopname','require','门店名必须！'), 
				array('tel','require','电话必须')
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
					$this->success('ok',U('lists'));
				}else{
					$this->error($errText);
				}
			}else{
				$this->error($M->getError());
			}
		}
	}
	
	//删除
	function del(){
	
	}
}