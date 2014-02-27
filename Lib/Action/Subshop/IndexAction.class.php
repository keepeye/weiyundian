<?php
class IndexAction extends SubshopAction{
	function _initialize(){
		parent::_initialize();
	}
	
	function index(){
		$this->display();
	}
	
	function welcome(){
		echo "欢迎使用门店子账户系统，请点击左侧菜单使用相应的功能";
	}
}