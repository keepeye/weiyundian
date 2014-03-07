<?php
class PlugmenuWidget extends Widget{
	private $token;
	private $wecha_id;
	public function render($data){
		$this->token = isset($data['token'])?$data['token']:$_GET['token'];//保存商户token
		$this->wecha_id = isset($data['wecha_id'])?$data['wecha_id']:$_GET['wecha_id'];//获取wecha_id
		if(!$this->token){
			return "";
		}
		$menus = $this->getMenus();//获取快捷菜单列表
		$settings = $this->getSettings();//获取快捷菜单样式设置
		if(empty($menus) || empty($settings) || $settings['style']==-1){
			return "";
		}
		$tpl = "style".$settings['style'];//指定模板
		//检测模板文件是否存在
		if(!file_exists(dirname(__FILE__).'/Plugmenu/'.$tpl.'.html')){
			return "";
		}
		//渲染模板，取得内容
		$content = $this->renderFile($tpl,array('settings'=>$settings,'menus'=>$menus,'params'=>array('token'=>$this->token,'wecha_id'=>$this->wecha_id)));
		//输出内容
		return $content;
	}
	//获取快捷菜单列表
	function getMenus(){
		return M('Plugmenu')->where(array('token'=>$this->token))->select();
	}
	//获取商户设置信息
	function getSettings(){
		return M('plugmenu_style')->where(array('token'=>$this->token))->find();
	}
}