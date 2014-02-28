<?php
class FoodAction extends SubshopAction{
	function _initialize(){
		parent::_initialize();
	}
	
	function index(){
		$this->display();
	}

	function cateList(){
		echo '111';
	}
	
}