<?php
/*
by 先兆
 */
class DiaoyanAction extends UserAction {
	private $_token;
	function _initialize(){
		parent::_initialize();
		$this->_token = $this->token?$this->token:session('token');//获得token
	}
	//活动列表
	function index(){
		$list = M('Diaoyan')->where(array("token"=>$this->_token))->select();//读取活动列表
		$this->assign("list",$list);
		$this->display();
	}
	//设置调研
	function setDiaoyan(){
		$id = I('id','0','intval');
		$isNew = $id > 0?false:true;//判断是否添加操作
		if(!$isNew){
			$diaoyan = M('Diaoyan')->where(array('id'=>$id,'token'=>$this->_token))->find();//更新模式下检查活动是否存在
			if(!$diaoyan){
				$this->error("活动不存在");
			}
			$diaoyan['keyword'] = M('Keyword')->where(array("pid"=>$id,"module"=>"Diaoyan","token"=>$this->_token))->getField("keyword");//获取关键词
		}
		//判断是否提交表单
		if(!IS_POST){
			$this->assign("diaoyan",$diaoyan);
			$this->display();//显示视图
		}else{
			
			$_POST['token'] = $this->_token;//将token添加到表单数据中，避免丢失
			$_POST['id'] = $id;//同上
			$_POST['end_time'] = strtotime($_POST['end_time']);
			if(M('Diaoyan')->create()){
				$re = $isNew?M('Diaoyan')->add():M('Diaoyan')->save();
				if($re !== false){
					$pid = $isNew?$re:$id;//获取主键
					if($pid > 0){
						//更新关键词
						D('Keyword')->setKeyword(I("post.keyword"),$pid,$this->_token,"Diaoyan",1);
					}
					$this->success("保存成功");
				}else{
					$this->error("保存数据失败:".M('Diaoyan')->getLastSql());
				}
			}else{
				$this->error(M('Diaoyan')->getError());
			}
		}
	}
	//题库
	function questionList(){
		$pid = I('id',0,'intval');//活动id
		//检测活动是否属于当前登录商户
		$diaoyan = M('Diaoyan')->where(array('token'=>$this->_token,'id'=>$pid))->find();
		if(!$diaoyan){
			$this->error('活动不存在');
		}
		$this->assign('diaoyan',$diaoyan);
		//读取记录列表
		$map = array(
			'diaoyan_id'=>$pid
			);
		
		$list=M('DiaoyanTiku')->where($map)->select();//题库列表
		$this->assign("list",$list);
		$this->assign("diaoyan",$diaoyan);
		$this->display();
	}
	//设置题目
	function setQuestion(){
		$id = I('id','0','intval');
		$diaoyan_id = I('diaoyan_id','0','intval');
		$isNew = $id > 0?false:true;//判断是否添加操作
		if(!$isNew){
			$tiku = M('DiaoyanTiku')->where(array('id'=>$id,'diaoyan_id'=>$diaoyan_id,'token'=>$this->_token))->find();//更新模式下检查活动是否存在
			if(!$tiku){
				$this->error("题目不存在");
			}
			$options = M('DiaoyanTikuOption')->where(array("tiku_id"=>$id,"token"=>$this->_token))->select();//读取选项表
		}
		//判断是否提交表单
		if(!IS_POST){
			$this->assign("options",$options);
			$this->assign("tiku",$tiku);
			$this->assign("diaoyan_id",$diaoyan_id);
			$this->display();//显示视图
		}else{
			$title = I('title');
			$type = I('type','0','intval')=="1"?"1":"0";
			$oldoptions = array_filter($_POST['oldoptions']);//旧选项
			$newoptions = array_filter($_POST['newoptions']);//新选项
			//进行一系列检测，时间关系就不使用模型自动验证了
			if((count($oldoptions) + count($newoptions))>10){
				$this->error("最多设置10个选项");
			}

			if((count($oldoptions) + count($newoptions))<2){
				$this->error("至少设置两个选项");
			}

			if($title == ""){
				$this->error("题目标题不能为空");
			}

			//更新题库表
			if($isNew){
				$data = array(
					"diaoyan_id"=>$diaoyan_id,
					"token"=>$this->_token,
					"title"=>$title,
					"type"=>$type
					);
				$re = M('DiaoyanTiku')->data($data)->add();
			}else{
				$data = array(
					"title"=>$title,
					"type"=>$type
					);
				$re = M('DiaoyanTiku')->where(array("id"=>$id,"token"=>$this->_token))->data($data)->save();
			}
			if($re === false){
				$this->error("设置题库信息失败");
			}
			//更新旧选项
			if(!empty($oldoptions) && !$isNew){
				foreach($oldoptions as $k=>$v){
					if($v==""){
						continue;
					}
					M('DiaoyanTikuOption')->where(array("tiku_id"=>$id,"id"=>$k))->data(array("value"=>$v))->save();
				}
			}
			unset($k,$v);
			//插入新选项
			foreach($newoptions as $v){
				if($v==""){
					continue;
				}
				M('DiaoyanTikuOption')->data(array("tiku_id"=>$id,"diaoyan_id"=>$diaoyan_id,"token"=>$this->_token,"value"=>$v))->add();
			}

			$this->success("操作完成",U('questionList',array('id'=>$diaoyan_id)));
		}
	}

	//报名记录
	function recordList(){

	}


	//删除活动
	function delDiaoyan(){
		$id = I('id','0','intval');
		if(!$id){
			$this->error("删除失败[01]");
		}
		$diaoyan = M('Diaoyan')->where(array("token"=>$this->_token))->find();
		if(!$diaoyan){
			$this->error("删除失败，活动不存在[02]");
		}
		M('Diaoyan')->delete($id);
		//删除题库信息
		$tiku_ids = M('DiaoyanTiku')->where(array("token"=>$this->_token,"diaoyan_id"=>$id))->getField("id");
		M('DiaoyanTiku')->where(array("token"=>$this->_token,"diaoyan_id"=>$id))->delete();
		//删除选项
		M('DiaoyanTikuOption')->where(array("tiku_id"=>array("in",$tiku_ids)))->delete();
		//删除回答记录
		M('DiaoyanRecord')->where(array("tiku_id"=>array("in",$tiku_ids)))->delete();
		//删除关键词
		D('Keyword')->deleteRecord($id,$this->_token,"Diaoyan");
		$this->success("操作完成");

	}
}