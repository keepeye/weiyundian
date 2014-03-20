<?php
class TongjiEventModel extends Model{
	protected $_eventType = array(
			"CLICK"=>"菜单点击",
			"VIEW"=>"菜单外链",
			"subscribe"=>"关注",
			"unsubscribe"=>"取消关注",
		);

	function tongji($token,$event,$event_key=""){
		if(!isset($this->_eventType[$event])){
			return false;
		}
		$nowtime = time();//当前时间戳
		$data = array(
			"token"=>$token,
			"year" => date("Y",$nowtime),
			"month" => date("n",$nowtime),
			"day" => date('j',$nowtime),
			"event"=>$event,
			);
		if($event_key != ""){
			$data['event_key'] = $event_key;
		}
		$record = $this->where($data)->find();//查询当天该事件统计记录
		if($record){
			//如果记录已经存在，则执行update操作
			$this->times = $this->times+1;//次数+1
			$this->lasttime = $nowtime;//更新记录时间
			$this->save();
		}else{
			//如果尚未统计，则执行insert操作
			$data['lasttime']=$nowtime;
			$data['times']=1;//计数器初始化为1次
			$this->add($data);
		}
		return true;
	}

	function getTongji($token,$event="",$event_key="",$start_date="",$end_date=""){
		$map = array(
			"token"=>$token
			);
		if($event!=""){
			$map['event'] = $event;
		}
		if($event_key != ""){
			$map['event_key']=$event_key;
		}
		if($start_date != ""){
			$stime = strtotime($start_date);//开始时间
		}else{
			$stime = 0;
		}
		if($end_date != ""){
			$etime = strtotime($end_date)+86400;//结束时间
		}else{
			$etime = strtotime(date("Y-m-d",time()))+86400;//获取明天0点的时间戳
		}
		$map['lasttime'] = array("between",array($stime,$etime));//确定时间区间

		return $this->field("event,event_key,SUM(times) as times")->where($map)->group("event,event_key")->select();//查询并求和times

	}

	//返回eventtype数组
	function getEventTypes(){
		return $this->_eventType;
	}
}