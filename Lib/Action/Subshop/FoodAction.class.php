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
		$where = array(
			"shopid" => $this->shopid
		);
		$cateList = M('FoodCategory')->where($where)->order("`sort` ASC")->select();
		
		$this->assign("cateList",$cateList);
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
			$data['name'] = substr($data['name'],0,30);
			$data['sort'] = max(0,(int)$data['sort']);
			if(M('FoodCategory')->create($data)){
				//修改
				if(!empty($cate)){
					$re = M('FoodCategory')->save();
				}else{//插入
					$re = M('FoodCategory')->add();
				}
				if($re === false){
					$this->error("更新数据失败，请检查数据合法性");
				}else{
					$this->success("恭喜配置成功");
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

	//删除分类
	function delCate(){
		$id = (int)$_REQUEST['id'];
		if($id <= 0 ){
			$this->error("删除失败");
		}
		//删除菜品
		M('FoodList')->where(array("shopid"=>$this->shopid,"category_id"=>$id))->delete();
		//删除分类
		M('FoodCategory')->where(array("shopid"=>$this->shopid,"id"=>$id))->delete();

		$this->success("删除成功");
	}


	//菜品列表
	function foodList(){
		$this->display();
	}

	//添加修改菜品
	function setFood(){
		$id = (int)$_REQUEST['id'];
		$food = array();//待修改的分类信息
		//修改模式
		if($id > 0){
			$map = array(
				"id" => $id,
				"shopid" => $this->shopid,
			);
			$food = M('FoodList')->where($map)->find();
			if(empty($cate)){
				$this->error("菜品不存在");
			}
		}
		if(IS_POST){
			$data = $_POST;
			$data['token'] = $this->token;
			$data['shopid'] = $this->shopid;
			$data['name'] = substr($data['name'],0,30);
			$data['sort'] = max(0,(int)$data['sort']);
			if(M('FoodCategory')->create($data)){
				//修改
				if(!empty($food)){
					$re = M('FoodCategory')->save();
				}else{//插入
					$re = M('FoodCategory')->add();
				}
				if($re === false){
					$this->error("更新数据失败，请检查数据合法性");
				}else{
					$this->success("恭喜配置成功");
				}
			}else{
				$this->error(M('FoodCategory')->getError());
			}
			
		}else{
			if(!empty($food)){
				$this->assign("food",$food);
			}
			$this->display();
		}
	}

	//删除菜品
	function delFood(){

	}
	
}