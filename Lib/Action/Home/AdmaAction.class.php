<?php
class AdmaAction extends BaseAction{
	//关注回复
	public function index(){
		if($this->_get('token')!=false){
			$adma=M('Adma')->where(array('token'=>$this->_get('token')))->find();
			if($adma==false){
				$this->error('你好本页面禁止从外部分享链接直接访问',U('Home/Index/index'));
			}else{
				if($adma['redirect']!=""){
					redirect($adma['redirect']);//如果设置了外链，则直接跳转到外链页面
					exit;
				}
				$this->assign('adma',$adma);
			}
		}else{
			$this->error('身份验证失败',U('Home/Index/index'));
		}
		$this->display();
	}

}
?>