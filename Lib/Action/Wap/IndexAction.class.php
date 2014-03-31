<?php
function strExists($haystack, $needle)
{
	return !(strpos($haystack, $needle) === FALSE);
}
class IndexAction extends BaseAction{
	private $tpl;	//微信公共帐号信息
	private $info;	//分类信息
	private $wecha_id;
	private $copyright;
	public $company;
	public $token;
	
	public function _initialize(){
		parent::_initialize();
		$agent = $_SERVER['HTTP_USER_AGENT']; 
	//	if(!strpos($agent,"MicroMessenger")) {
	//		echo '此功能只能在微信浏览器中使用';exit;
	//	}
		$this->token=$this->_get('token','trim');
		$where['token']=$this->token;
		
		$tpl=D('Wxuser')->where($where)->find();
		//dump($where);
		$info=M('Classify')->where(array('token'=>$this->_get('token'),'status'=>1))->order('sorts desc')->select();
		$info=$this->convertLinks($info);//加外链等信息
		$gid=D('Users')->field('gid')->find($tpl['uid']);
		$copy=D('user_group')->field('iscopyright')->find($gid['gid']);	//查询用户所属组
		$this->copyright=$copy['iscopyright'];
		$this->wecha_id=$this->_get('wecha_id');
		$this->assign('wecha_id',$this->wecha_id);
		$this->info=$info;
		$this->tpl=$tpl;
		$company_db=M('company');
		$this->company=$company_db->where(array('token'=>$this->token,'isbranch'=>0))->find();
		$this->assign('company',$this->company);
		//
		$this->assign('token',$this->token);
	}
	
	
	public function classify(){
		$this->assign('info',$this->info);
		
		$this->display($this->tpl['tpltypename']?$this->tpl['tpltypename']:'muban1_index');
	}
	
	public function index(){		
		$where['token']=$this->token;
	    $flash=M('Flash')->where($where)->select();
        $home=M('Home')->where(array('token'=>$this->token))->find();
		$count=count($flash);
		$this->assign('flash',$flash);
		$this->assign('info',$this->info);
		$this->assign('num',$count);
		$this->assign('tpl',$this->tpl);
		$this->assign('copyright',$this->copyright);
		$this->assign('home',$home);
		echo $this->wecha_id;
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
		$this->assign('info',$this->info);
		$this->assign('tpl',$this->tpl);
		$this->assign('res',$res);
		$this->assign('copyright',$this->copyright);
		$this->assign('pagestr',$pagestr);//分页链接
		$this->display($this->tpl['tpllistname']?$this->tpl['tpllistname']:'weimob1_list');
	}
	
	public function content(){
		$db=M('Img');
		$where['token']=$this->_get('token','trim');
		$where['id']=array('neq',(int)$_GET['id']);
		$lists=$db->where($where)->limit(5)->order('uptatetime')->select();
		$where['id']=$this->_get('id','intval');
		$res=$db->where($where)->find();
		$this->assign('info',$this->info);	//分类信息
		$this->assign('lists',$lists);		//列表信息
		$this->assign('res',$res);			//内容详情;
		$this->assign('tpl',$this->tpl);				//微信帐号信息
		$this->assign('copyright',$this->copyright);	//版权是否显示
		$this->display($this->tpl['tplcontentname']?$this->tpl['tplcontentname']:'weimob1_content');
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
