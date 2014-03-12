<?php
class TongjiAction extends TongjiBaseAction{
	private $_type;
	private $_pid;
	private $_title;
	private $_cookieid;
	private $_record;
	function _initialize(){
		parent::_initialize();
		$this->_type = I('type');//类别
		$this->_pid = I('pid');//文档主键
		$this->_title = I('title');//文档标题
		$this->_cookieid = ACTION_NAME."-".$this->_type."-".$this->_pid;
		//检测请求是否合法或是否已经统计过当前用户
		if(!$this->check(ACTION_NAME)){
			eixt('1');
		}
		//查询该文档的统计记录
		$this->_record = M('tongji')->where(array("type"=>$this->_type,"pid"=>$this->_pid))->find();
		if(!$this->_record){//记录不存在，初始化记录
			$this->newRecord();
			exit('0');
		}
	}
	//更新访问统计
	function click(){
		$nowtime = time();
		$nowyear = date("Y",$nowtime);//当前年份数字
		$nowmonth = date("n",$nowtime);//当前月份数字
		$nowweek = date("W",$nowtime);//当前一年中的第几周
		$nowday = date('j',$nowtime);//当前一个月的第几天
		$data = array(
			"total_click"=>$this->_record['total_click']+1,
			);
		if($nowyear == date("Y",$this->_record['month_uptime'])){//判断是否同一年
			if($nowmonth <= date("n",$this->_record['month_uptime'])){
				$data['month_click'] = $this->_record['month_click']+1;//月统计+1
				if($nowweek <= date("W",$this->_record['week_uptime'])){
					$data['week_click'] = $this->_record['week_click']+1;//周统计+1
					if($nowday <= date('j',$this->_record['today_uptime'])){
						$data['today_click'] = $this->_record['today_click']+1;//日统计+1
					}else{//跨日，归零日统计
						$data['today_click'] = 1;
						$data['today_uptime'] = $nowtime;
					}
				}else{//跨周了，归零周以下的统计
					$data['week_click'] = $data['today_click'] = 1;
					$data['week_uptime'] = $data['today_uptime'] = $nowtime;
				}
			}else{//跨月，归零统计数据
				$data['month_click'] = $data['week_click'] = $data['today_click'] = 1;
				$data['month_uptime'] = $data['week_uptime'] = $data['today_uptime'] = $nowtime;
			}
		}else{//恰好跨年,则归零月以下的数据
			$data['month_click'] = $data['week_click'] = $data['today_click'] = 1;
			$data['month_uptime'] = $data['week_uptime'] = $data['today_uptime'] = $nowtime;
		}
		M('tongji')->where(array("type"=>$this->_type,"pid"=>$this->_pid))->save($data);//更新统计数据
		exit('0');
	}

	//更新转发统计
	function share(){
		$nowtime = time();
		$nowyear = date("Y",$nowtime);//年份数字
		$nowmonth = date("n",$nowtime);//月份数字
		$nowweek = date("W",$nowtime);//一年中的第几周
		$nowday = date('j',$nowtime);//一个月的第几天
		$data = array(
			"total_share"=>$this->_record['total_share']+1,
			);
		if($nowyear == date("Y",$this->_record['month_uptime'])){//判断是否同一年
			if($nowmonth <= date("n",$this->_record['month_uptime'])){
				$data['month_share'] = $this->_record['month_share']+1;//月统计+1
				if($nowweek <= date("W",$this->_record['week_uptime'])){
					$data['week_share'] = $this->_record['week_share']+1;//周统计+1
					if($nowday <= date('j',$this->_record['today_uptime'])){
						$data['today_share'] = $this->_record['today_share']+1;//日统计+1
					}else{//跨日，归零日统计
						$data['today_share'] = 1;
						$data['today_uptime'] = $nowtime;
					}
				}else{//跨周了，归零周以下的统计
					$data['week_share'] = $data['today_share'] = 1;
					$data['week_uptime'] = $data['today_uptime'] = $nowtime;
				}
			}else{//跨月，归零统计数据
				$data['month_share'] = $data['week_share'] = $data['today_share'] = 1;
				$data['month_uptime'] = $data['week_uptime'] = $data['today_uptime'] = $nowtime;
			}
		}else{//恰好跨年,则归零月以下的数据
			$data['month_share'] = $data['week_share'] = $data['today_share'] = 1;
			$data['month_uptime'] = $data['week_uptime'] = $data['today_uptime'] = $nowtime;
		}
		M('tongji')->where(array("type"=>$this->_type,"pid"=>$this->_pid))->save($data);//更新统计数据
		exit('0');
	}

	//检测是否已经记录过当前用户,false表示不合法或已经记录过
	private function check(){
		//检测type和pid字段是否空值
		if(empty($this->_type) || empty($this->_pid)){
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
		return true;
	}

	//初始化统计 总是先统计到click数据的
	private function newRecord(){
		//检测标题是否为空
		if(trim($this->_title) == ""){
			exit('56');
		}
		$data = array(
			"type"=>$this->_type,
			"pid"=>$this->_pid,
			"title"=>$this->_title,
			);
		$data['total_click'] = $data['month_click'] = $data['week_click'] = $data['today_click'] = 1;//初始化点击次数
		$data['month_uptime'] = $data['week_uptime'] = $data['today_uptime'] = time();//记录更新时间点
		M('tongji')->add($data);//插入记录
	}
}