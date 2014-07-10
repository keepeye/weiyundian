<?php
function strExists($haystack, $needle)
{
	return !(strpos($haystack, $needle) === FALSE);
}
class IndexAction extends WapAction{
	
	public function _initialize(){
		parent::_initialize();
		$agent = $_SERVER['HTTP_USER_AGENT']; 
	//	if(!strpos($agent,"MicroMessenger")) {
	//		echo '此功能只能在微信浏览器中使用';exit;
	//	}
		$this->assign('wecha_id',$this->wecha_id);
		$this->assign('token',$this->token);
	}
	
	protected function getIsCopyright(){
		
		$gid=D('Users')->field('gid')->find($tpl['uid']);
		$copy=D('user_group')->field('iscopyright')->find($gid['gid']);	//查询用户所属组
		return $copy['iscopyright'];
		
	}
	//获取公司信息
	protected function getCompany(){
		$company_db=M('company');
		return $company_db->where(array('token'=>$this->token,'isbranch'=>0))->find();
	}
	//获取分类列表
	protected function getClasses(){
		$info=M('Classify')->where(array('token'=>$this->token,'status'=>1))->order('sorts desc')->select();
		$info=$this->convertLinks($info);//加外链等信息
		return $info;
	}

	//获取wxuser
	protected function getTpl(){
		$where['token']=$this->token;
		return D('Wxuser')->where($where)->find();
	}

	public function classify(){
		$this->assign('info',$this->info);
		
		//$this->display($this->tpl['tpltypename']?$this->tpl['tpltypename']:'muban1_index');
		$this->display('muban1_index');
	}
	
	public function index(){		
		$where['token']=$this->token;
		//首页配置
        $home=M('Home')->where(array('token'=>$this->token))->find();
        $this->assign('home',$home);
        //幻灯片
        $flash=M('Flash')->where($where)->select();
		$count=count($flash);
		$this->assign('flash',$flash);

		//分类列表
		$this->assign('info',$this->getClasses());

		$tpl = $this->getTpl();
		$this->assign('tpl',$tpl);

		$this->assign('num',$count);
		
		$this->assign('copyright',$this->copyright);
		$this->assign("company",$this->getCompany());
		$this->display($this->tpl['tpltypename']?$this->tpl['tpltypename']:'muban1_index');
	}
	
	public function lists(){
		$pernum = 5;//每页显示数量
		import("@.ORG.Page");//导入分页类
		$db=D('Img');//图文信息模型	
		
		$where = array();//初始化查询条件
		$where['token']= $token =$this->_get('token','trim');//获取商户token
		$where['classid']= $classid = $this->_get('classid','intval');//分类id
		
        $classify = M('classify')->where(array('token'=>$token,'id'=>$classid))->find();//读取分类信息
		$count=$db->where($where)->count();//总数量
		$Page       = new Page($count,$pernum);// 实例化分页类 传入总记录数和每页显示的记录数
		
		
		$Page->setConfig('theme','%upPage% %nowPage%/%totalPage% %downPage%');
		$pagestr       = $Page->show();// 分页显示输出
		$res=$db->where($where)->limit($Page->firstRow,$Page->listRows)->select();//查询列表
		
		$res=$this->convertLinks($res);//url处理
		
		//$this->assign('page',$pagecount);
		$this->assign('classify',$classify);
		//$this->assign('p',$p);
		$this->assign('info',$this->getClasses());
		$this->assign('tpl',$this->tpl);
		$this->assign('res',$res);
		$this->assign('copyright',$this->copyright);
		$this->assign('pagestr',$pagestr);//分页链接
		$this->assign("company",$this->getCompany());
		$this->display($this->tpl['tpllistname']?$this->tpl['tpllistname']:'weimob1_list');
	}
	
	public function content(){
		//读取图文内容
		$db=M('Img');
		$where['token']=$this->token;
		$where['id']=$this->_get('id','intval');
		$res=$db->where($where)->find();
		//检测推广
		if($res && $res['spread']==1 && $res['spread_score']>0 && $this->fromuser){
			$spread = M('ImgSpread')->where(array("pid"=>$res['id'],"wecha_id"=>$this->fromuser))->find();
			//初始化推广记录
			if(!$spread){
				M('ImgSpread')->data(array("pid"=>$res['id'],"wecha_id"=>$this->fromuser,"spread_socre"=>$res['spread_score']))->add();
			}else{
				//检测累计收益小于设置的上限
				if($spread['spread_score'] < $res['spread_score_limit']){
					//更新推广记录
					M('ImgSpread')->spread_score += $res['spread_score'];
					M('ImgSpread')->save();
					//增加用户积分
					if(D('WechaUser')->changeScore($this->token,$this->fromuser,$res['spread_score'])){
		                D('WechaLog')->addLog($this->token,$this->fromuser,"积分","推广图文{$res['title']}增加积分{$res['spread_score']}");
		            }
				}
			}
		}
		$this->assign("wecha_id",$this->wecha_id);
		$this->assign('res',$res);
		$this->display();
	}
	
	//查询推广收益
	public function income(){
		$this->ajaxReturn(array("info"=>$this->wecha_id));
	}


	public function flash(){
		$where['token']=$this->_get('token','trim');
		$flash=M('Flash')->where($where)->select();
		$count=count($flash);
		$this->assign('flash',$flash);
		$this->assign('info',$this->info);
		$this->assign('num',$count);
		$this->display('ty_index');
	}
	/**
	 * 获取链接
	 *
	 * @param unknown_type $url
	 * @return unknown
	 */
	public function getLink($url){
		$urlArr=explode(' ',$url);
		$urlInfoCount=count($urlArr);
		if ($urlInfoCount>1){
			$itemid=intval($urlArr[1]);
		}
		//会员卡 刮刮卡 团购 商城 大转盘 优惠券 订餐 商家订单
		if (strExists($url,'刮刮卡')){
			$link='/index.php?g=Wap&m=Guajiang&a=index&token='.$this->token.'&wecha_id='.$this->wecha_id;
			if ($itemid){
				$link.='&id='.$itemid;
			}
		}elseif (strExists($url,'大转盘')){
			$link='/index.php?g=Wap&m=Lottery&a=index&token='.$this->token.'&wecha_id='.$this->wecha_id;
			if ($itemid){
				$link.='&id='.$itemid;
			}
		}elseif (strExists($url,'优惠券')){
			$link='/index.php?g=Wap&m=Coupon&a=index&token='.$this->token.'&wecha_id='.$this->wecha_id;
			if ($itemid){
				$link.='&id='.$itemid;
			}
		}elseif (strExists($url,'商家订单')){
			if ($itemid){
				$link=$link='/index.php?g=Wap&m=Host&a=index&token='.$this->token.'&wecha_id='.$this->wecha_id.'&hid='.$itemid;
			}else {
				$link='/index.php?g=Wap&m=Host&a=Detail&token='.$this->token.'&wecha_id='.$this->wecha_id;
			}
		}elseif (strExists($url,'会员卡')){
			$link='/index.php?g=Wap&m=Card&a=vip&token='.$this->token.'&wecha_id='.$this->wecha_id;
		}elseif (strExists($url,'商城')){
			$link='/index.php?g=Wap&m=Product&a=index&token='.$this->token.'&wecha_id='.$this->wecha_id;
		}elseif (strExists($url,'订餐')){
			$link='/index.php?g=Wap&m=Product&a=dining&dining=1&token='.$this->token.'&wecha_id='.$this->wecha_id;
		}elseif (strExists($url,'团购')){
			$link='/index.php?g=Wap&m=Groupon&a=grouponIndex&token='.$this->token.'&wecha_id='.$this->wecha_id;
		}else {
			$link=$url;
		}
		return $link;
	}
	public function convertLinks($arr){
		$i=0;
		foreach ($arr as $a){
			if ($a['url']){
				$arr[$i]['url']=$this->getLink($a['url']);
			}
			$i++;
		}
		return $arr;
	}
}
