<?php
class FoodAction extends SubshopAction{
	function _initialize(){
		parent::_initialize();
	}
	
	function index(){
		$this->display();
	}

	//菜单分类列表
	function cateList(){
		$this->display();
	}
	//添加、修改菜单
	function setCate(){
		$id = (int)$_REQUEST['id'];
		$cate = array();//待修改的分类信息
		//修改模式
		if($id > 0){
			$map = array(
				"id" => $id,
				"token" => $this->token,
				"shopid" => $this->shopid,
			);
			$cate = M('FoodCategory')->where($map)->find();
		}
		if(IS_POST){

		}else{
			if(!empty($cate)){
				$this->assign("cate",$cate);
			}
			$this->display();
		}
	}
	
}