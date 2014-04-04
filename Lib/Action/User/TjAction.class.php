<?php
//访问、转发统计查看
class TjAction extends UserAction{
	private $_token;
	private $_model;
	private $_types;
	function _initialize(){
		parent::_initialize();
		$this->_token = $this->token;//获取商户token
		$this->_model = D('Tongji');
		$this->_types = $this->_model->types;
		$this->assign("types",$this->_types);
	}
	
	//默认统计
	function index(){
		$end_time = I('end_date',date("Y-m-d",time()),'strtotime')+86400;//截止时间戳
		$start_time = I('start_date','','trim')?strtotime(I('start_date')):($end_time-86400*30);
		$list = $this->_model->field("concat(year,'/',month,'/',day) as day,SUM(shares) as shares,SUM(clicks) as clicks")->where(array("token"=>$this->_token,"lasttime"=>array("in",array($start_time,$end_time))))->group("year,month,day")->order("id ASC")->select();
		dump($this->_model->getLastSql());
		$data['day'] = array_column($list,"day");
		$data['shares'] = array_column($list,"shares");
		//$data['clicks'] = array_column($list,"clicks");
		
		 $this->assign("data",$data);
		 $this->display();
	}

	//访问统计
	function clicks(){

	}

	//转发统计
	function shares(){
		//今日分享最多的十条图文
		$today_list = $this->_model->where(array("token"=>$this->_token,"year"=>date("Y",time()),"month"=>date("n",time()),"day"=>date("j",time())))->order("shares desc")->limit("0,10")->select();
		//昨天分享
		$yestoday_list = $this->_model->where(array("token"=>$this->_token,"year"=>date("Y",time()),"month"=>date("n",time()),"day"=>date("j",time()-86400)))->order("shares desc")->limit("0,10")->select();
		//本月分享最多的十条图文
		$month_list = $this->_model->field("title,type,SUM(shares) as shares")->group('pid')->where(array("token"=>$this->_token,"year"=>date("Y",time()),"month"=>date("n",time())))->order("shares desc")->limit("0,10")->select();
		$this->assign("today_list",$today_list);
		$this->assign("month_list",$month_list);
		$this->assign("yestoday_list",$yestoday_list);
		$this->display();
	}

	//获取指定日期的统计
	function oneDay(){
		$timestamp = I('date',null,'strtotime');
		if(!$timestamp){
			$this->error("请指定日期date，格式如2014-04-17");
		}
		$year = date('Y',$timestamp);
		$month = date('n',$timestamp);
		$day = date('j',$timestamp);
		//获取指定日期的转发统计
		$sharelist = $this->_model->where(array("token"=>$this->_token,"year"=>$year,"month"=>$month,"day"=>$day))->select();
		$this->assign("sharelist",$sharelist);
		$this->display();
	}

	//事件统计
	function events(){
		$event = I('event','','trim');//获取事件类型
		$event_key = I('event_key','','trim');//指定关键词
		$start_date = I('start_date','','trim');//开始日期
		$end_date = I('end_date','','trim');//结束日期
		$tj_list = D('TongjiEvent')->getTongji($this->_token,$event,$event_key,$start_date,$end_date);//获取统计结果列表
		$this->assign("list",$tj_list);
		$event_types = D('TongjiEvent')->getEventTypes();
		$this->assign("event_types",$event_types);
		$this->display();
	}

	//图文详细统计
	function imgs(){
		$start_date = I('start_date','','trim');//开始日期
		$end_date = I('end_date','','trim');//结束日期
		$type = I('type','','trim');//类型

		$map = array(
			"token"=>$this->_token
			);
		if($type!=""){
			$map['type'] = $type;
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

		$list = M('Tongji')->field("title,type,SUM(clicks) as clicks,SUM(shares) as shares")->where($map)->group("pid")->select();
		$this->assign("list",$list);
		$this->display();
	}
}