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
		$record = $this->where(array($data))->find();//查询当天该事件统计记录
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
}