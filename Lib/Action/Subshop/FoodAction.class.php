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
			if(empty($cate)){
				$this->error("分类不存在");
			}
		}
		if(IS_POST){
			$data = $_POST;
			$data['token'] = $this->token;
			$data['shopid'] = $this->shopid;
			if(M('FoodCategory')->create()){
				//修改
				if(!empty($cate)){
					$re = M('FoodCategory')->save();
				}else{//插入
					$re = M('FoodCategory')->add();
				}
				if($re === false){
					$this->error("更新数据失败，请检查数据合法性");
				}else{
					$this->success("恭喜，添加成功");
				}
			}else{
				$this->error(M('FoodCategory')->getError());
			}
			
		}else{
			if(!empty($cate)){
				$this->assign("cate",$cate);
			}
			$this->display();
		}
	}
	
}