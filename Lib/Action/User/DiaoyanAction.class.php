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

	//报名记录
	function recordList(){

	}
}