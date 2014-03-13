<?php
//统计表模型
class TongjiModel extends Model{
	
	//获取统计概况，分别是今日，昨日，月，总
	function getGlobalCount(){
		$globalCount = array();
		$this->getTotalGlobalCount($globalCount);
	}

	//总的统计
	function getTotalGlobalCount(&$globalCount){

	}
}