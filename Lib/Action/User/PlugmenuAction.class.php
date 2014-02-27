<?php
class PlugmenuAction extends UserAction{
	function __construct(){
		parent::__construct();
		$this->token = session('token');
		if(!$this->token){
			$this->error('请先登录');
		}
		$this->assign('token',$this->token);
		//获取公司信息
		$company = M('Company')->where(array('token'=>$this->token))->find();
		$this->assign("company",$company);
	}
	public function index(){
		$menus=M('Plugmenu')->where(array('token'=>$this->token))->order("sort ASC")->select();
		$this->assign('menus',$menus);
		$style=M('PlugmenuStyle')->where(array('token'=>$this->token))->find();
		$this->assign('style',$style);
		$this->display();
	}
	
	public function add(){
		if(IS_POST){
			$_POST['token'] = $this->token;
			$M = M('Plugmenu');
			$validate = array(
				array('name','require','快捷名称必须！'), // 仅仅需要进行验证码的验证
				array('url','require','链接类型必须')
			);
			$M->setProperty("_validate",$validate);

			if($M->create()){
				if($M->add()){
					$this->success('添加成功',U('index'));
				}else{
					$this->error("插入数据失败");
				}
			}else{
				$this->error('数据验证失败：'.$M->getError());
			}
			
		}else{
			$this->display();
		}
	}
	
	public function edit(){
		if(IS_POST){
			$id = $this->_post('id');
			if(!$id){
				$this->error('请指定id');
			}
			$M = M('Plugmenu');
			$validate = array(
				array('name','require','快捷名称必须！'), // 仅仅需要进行验证码的验证
				array('url','require','链接类型必须')
			);
			$M->setProperty("_validate",$validate);
    		if($M->create()){
				if($M->where(array('id'=>$id,'token'=>$this->token))->save()){
					$this->success('修改成功',U('index'));
				}else{
					$this->error("修改数据失败");
				}
			}else{
				$this->error('数据验证失败：'.$M->getError());
			}
    	}
    	else{
    		$id=$this->_get('id','intval');
    		$menu=M('Plugmenu')->find($id);
    		$this->assign('menu',$menu);
    		$this->display();
			
    	}
	}
	
	public function del(){
		$where['id']=$this->_get('id','intval');
		$where['token']=$this->token;
		if(D("Plugmenu")->where($where)->delete()){
			$this->success('操作成功',U('index'));
		}else{
			$this->error('操作失败',U('index'));
		}
	}
	
	public function save_style(){
		if(IS_POST){
			$style=M('PlugmenuStyle')->where(array('token'=>session('token')))->find();
			$data = array(
					'token' => $this->token,
					'style' => $this->_post('style'),
					'style_color' => $this->_post('style_color'),
					'copyright' => $this->_post('copyright'),
			);
			if($style){
				$re = M('PlugmenuStyle')->where(array('token'=>$this->token))->data($data)->save();
			}
			else{
				$re = M('PlugmenuStyle')->data($data)->add();
			}
			if($re !== false){
				$this->redirect('index');
			}else{
				$this->error('数据库错误');
			}
		}
	}
}