<?php
/**
 *关注回复
**/
class AreplyAction extends UserAction{
	public function index(){
		$db=D('Areply');
		$where['uid']=$_SESSION['uid'];
		$where['token']=$_SESSION['token'];
		$res=$db->where($where)->find();
		$this->assign('areply',$res);
		$this->display();
	}
	public function insert(){
		C('TOKEN_ON',false);
		if($_POST['status'] == 0)
		{
			$this->error('请选择模式');
		}
		$db=D('Areply');
		$where['uid']=$_SESSION['uid'];
		$where['token']=$_SESSION['token'];
		$res=$db->where($where)->find();
		if($res==false){
			$where['content']=$this->_post('content');
			$where['keyword']=$this->_post('keyword');
            if(!empty($_POST['home']))
            {
                $where['home']= 1;
            }
            else
            {
                
                $where['home']= 0;
            }
			$where['status'] = $this->_post('status');
			$where['createtime']=time();
			if($_POST['status'] == 1)
			{
				if($where['content']==false){$this->error('内容必须填写');}
			}
			else if($_POST['status'] == 2)
			{
				$where['keyword']="首页";
			}
			else if($_POST['status'] == 3)
			{
				if($where['keyword']==false){$this->error('图文关键词必须填写');}
			}
			
		
			$id=$db->data($where)->add();
			if($id){
				$this->success('发布成功',U('Areply/index'));
			}else{
				$this->error('发布失败',U('Areply/index'));
			}
		}else{
			$where['id']=$res['id'];
			$where['content']=$this->_post('content');
			$where['updatetime']=time();
			$where['keyword']=$this->_post('keyword');
			$where['status'] = $this->_post('status');
            if(!empty($_POST['home']))
            {
                $where['home']= 1;
            }
            else
            {
                
                $where['home']= 0;
            }

			if($_POST['status'] == 1)
			{
				if($where['content']==false){$this->error('内容必须填写');}
			}
			else if($_POST['status'] == 2)
			{
				$where['keyword']="首页";
			}
			else if($_POST['status'] == 3)
			{
				if($where['keyword']==false){$this->error('图文关键词必须填写');}
			}
			
			
			
			if($db->save($where)){
				$this->success('更新成功',U('Areply/index'));
			}else{
				$this->error('更新失败',U('Areply/index'));
			}
		}
	}
}
?>
