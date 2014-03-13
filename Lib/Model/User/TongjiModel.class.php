<?php
//统计表模型
class TongjiModel extends Model{
	
	//获取统计概况，分别是今日，昨日，月，总
	function getGlobalCount(){
		$globalCount = array();
		$this->setFields();
		$this->getTotalGlobalCount($globalCount);
	}

	//总概况
	function getTotalGlobalCount(&$globalCount){
		$map = array();
		$globalCount['total'] = $this->where($map)->find();
	}
	//月概况
	function getMonthGlobalCount(&$globalCount){
		$map = array(
			"month"=>date('n',time())
			);
		$globalCount['month'] = $this->where($map)->find();
	}
	//昨日概况
	function getYestodayGlobalCount(&$globalCount){
		$map = array(
			"day"=>date('j',time()-86400)
			);
		$globalCount['yestoday'] = $this->where($map)->find();
	}
	//今日概况
	function getTodayGlobalCount(&$globalCount){
		$map = array(
			"day"=>date('j',time())
			);
		$globalCount['today'] = $this->where($map)->find();
	}
	//
	function setFields(){
		$this->field("SUM('shares') as shares");
	}
}