<?php
class TongjiAction extends TongjiBaseAction{
	private $_token;
	private $_type;
	private $_pid;
	private $_title;
	private $_cookieid;
	private $_record;
	private $_now;
	function _initialize(){

		parent::_initialize();
		exit('fff');
		$this->_type = I('type');//类别
		$this->_pid = I('pid');//文档主键
		$this->_title = I('title');//文档标题
		$this->_cookieid = ACTION_NAME."-".$this->_type."-".$this->_pid;
		$this->_token = I('token');//商户token
		//检测请求是否合法或是否已经统计过当前用户
		if(!$this->check(ACTION_NAME)){
			eixt('1');
		}
		$this->_now['time'] = $nowtime = time();//当前时间戳
		$this->_now['year'] = date("Y",$nowtime);//当前年份数字
		$this->_now['month'] = date("n",$nowtime);//当前月份数字
		$this->_now['week'] = date("W",$nowtime);//当前一年中的第几周
		$this->_now['day'] = date('j',$nowtime);//当前一个月的第几天
		$map = array(
			"type"=>$this->_type,
			"pid"=>$this->_pid,
			"year"=>$this->_now['year'],
			"month"=>$this->_now['month'],
			"day"=>$this->_now['day']
			);
		//查询该文档的统计记录#########################################
		$this->_record = M('tongji')->where($map)->find();
		if(!$this->_record){//记录不存在，初始化记录
			$this->newRecord();
			exit('0');
		}
	}
	//更新访问统计
	function click(){
		exit('0');
	}

	//更新转发统计
	function share(){
		$this->_record->shares = $this->_record['shares']+1;
		$this->_record->save();//###########################################
		exit('0');
	}

	//检测是否已经记录过当前用户,false表示不合法或已经记录过
	private function check(){
		//检测type和pid字段是否空值
		if(empty($this->_type) || empty($this->_pid) || !$this->_token){
			return false;
		}
		//检测type是否已定义
		if(!in_array($this->_type,$this->_types)){
			return false;
		}
		//检测cookie，判断是否已经记录过该用户
		
		if(null != cookie($this->_cookieid)){
			return false;
		}
		//判断token是否存在  ###########################################
		if(!M('wxuser')->field('id')->where(array("token"=>$this->_token))->find()){
			return false;
		}
		return true;
	}

	//初始化统计 总是先统计到click数据的
	private function newRecord($ActionName="click"){
		//检测标题是否为空
		if(trim($this->_title) == ""){
			exit('56');
		}
		$data = array(
			"token"=>$this->_token,
			"type"=>$this->_type,
			"pid"=>$this->_pid,
			"title"=>$this->_title,
			"year"=>$this->_now['year'],
			"month"=>$this->_now['month'],
			"day"=>$this->_now['day'],
			"lasttime"=>$this->_now['time']
			);
		if($ActionName == "click"){
			$data['clicks'] = 1;
		}else{
			$data['shares'] = 1;
		}
		M('tongji')->add($data);//插入记录############################
	}
}