<?php
class SelfformAction extends BaseAction{
	public $token;//商户token
	public $wecha_id;//微信唯一id
	public $selfform_model;//selfform表模型
	public $selfform_input_model;
	public $selfform_value_model;
	public function __construct(){
		parent::__construct();
		$this->token		= $this->_get('token');//获取商户token
		$this->wecha_id	= I('wecha_id',I('get.wecha_id'));//获取wecha_id
		//判断wecha_id 没有的话跳转到宣传页
		// if (!$this->wecha_id){
		// 	$this->redirect("Home/Adma/index?token=".$this->token);
		// }
		//初始化模型实例
		$this->selfform_model=M('Selfform');//报名主表模型
		$this->selfform_input_model=M('Selfform_input');//报名表单项设置模型
		$this->selfform_value_model=M('Selfform_value');//用户提交的表单值模型
		//将一些值传入模板
		$this->assign('wecha_id',$this->wecha_id);
		$this->assign('token',$this->token);
		//$this->assign('staticFilePath',str_replace('./','/',THEME_PATH.'common/css/product'));
	}
	public function index(){
		$formid=intval($_GET['id']);
		$thisForm=$this->selfform_model->where(array('id'=>$formid))->find();
		//判断截止时间
		if(time()>$thisForm['endtime']){
			$this->error("活动已经截止咯");
		}
		$thisForm['successtip']=$thisForm['successtip']==''?'提交成功':$thisForm['successtip'];
		$this->assign('thisForm',$thisForm);
		$where=array('formid'=>$formid);
		$list = $this->selfform_input_model->where($where)->order('taxis ASC')->select();
		$listByKey=array();
		if ($list){
			$i=0;
			foreach ($list as $l){
				if ($l['inputtype']=='select'){
					$options=explode('|',$l['options']);
					$optionStr='<option value="" selected>请选择'.$l['displayname'].'</option>';
					if ($options){
						foreach ($options as $o){
							$optionStr.='<option value="'.$o.'">'.$o.'</option>';
						}
					}
					$list[$i]['optionStr']=$optionStr;
				}
				if ($l['errortip']==''){
					$list[$i]['errortip']='请输入'.$l['displayname'];
				}
				$listByKey[$l['fieldname']]=$l;
				$i++;
			}
		}
		if (IS_POST){
			$row=array();
			$fields=array();
			if ($list){
				foreach ($list as $l){
					$fields[$l['fieldname']]=$_POST[$l['fieldname']];
				}
			}
			$row['values']=serialize($fields);
			$row['formid']=$thisForm['id'];
			$row['wecha_id']=$this->wecha_id;
			$row['time']=time();
			$id = $this->selfform_value_model->add($row);
			$this->redirect(U('Selfform/index',array('token'=>$this->token,'wecha_id'=>$this->wecha_id,'id'=>$thisForm['id'],'success'=>1)));
		}else {
			$cookie_key = "selfform_{$formid}_{$this->token}";
			$cookie_data = cookie($cookie_key);
			if(!$cookie_data || !$cookie_data['wecha_id']){
				$cookie_data = array(
					"wecha_id" => uniqid(),//伪造一个wecha_id
				);
				cookie($cookie_key,$cookie_data);				
			}
			$this->wecha_id = $cookie_data['wecha_id'];//伪造的wecha_id
			$submitted=0;
			
			//判断是否提交过信息了
			$submitInfo=$this->selfform_value_model->where(array('wecha_id'=>$this->wecha_id,'formid'=>$thisForm['id']))->find();
			if ($submitInfo){
				$info=unserialize($submitInfo['values']);
				if ($info){
					foreach ($info as $k=>$v){
						$info[$k]=array('displayname'=>$listByKey[$k]['displayname'],'value'=>$v);
					}
				}
				$this->assign('submitInfo',$info);
				//$submitted=1;
				//二维码图片
				//$imgSrc=generateQRfromGoogle(C('site_url').'/index.php?g=Wap&m=Selfform&a=submitInfo&token='.$this->token.'&wecha_id='.$this->wecha_id.'&id='.$thisForm['id']);
				
				//$this->assign('imgSrc',$imgSrc);
			}
			
			$this->assign('company',$company);
			$this->assign('submitted',$submitted);
			$this->assign('list',$list);
			$this->display();
		}
	}
	public function detail(){
		$formid=intval($_GET['id']);
		$thisForm=$this->selfform_model->where(array('id'=>$formid))->find();
		$this->assign('thisForm',$thisForm);
		$this->display();
	}
	public function submitInfo(){
		$formid=intval($_GET['id']);
		$thisForm=$this->selfform_model->where(array('id'=>$formid))->find();
		$thisForm['successtip']=$thisForm['successtip']==''?'提交成功':$thisForm['successtip'];
		$this->assign('thisForm',$thisForm);
		$where=array('formid'=>$formid);
		$list = $this->selfform_input_model->where($where)->order('taxis ASC')->select();
		$listByKey=array();
		if ($list){
			$i=0;
			foreach ($list as $l){
				if ($l['inputtype']=='select'){
					$options=explode('|',$l['options']);
					$optionStr='<option value="" selected>请选择'.$l['displayname'].'</option>';
					if ($options){
						foreach ($options as $o){
							$optionStr.='<option value="'.$o.'">'.$o.'</option>';
						}
					}
					$list[$i]['optionStr']=$optionStr;
				}
				if ($l['errortip']==''){
					$list[$i]['errortip']='请输入'.$l['displayname'];
				}
				$listByKey[$l['fieldname']]=$l;
				$i++;
			}
		}
		$submitInfo=$this->selfform_value_model->where(array('wecha_id'=>$this->wecha_id,'formid'=>$thisForm['id']))->find();
		if ($submitInfo){
			$info=unserialize($submitInfo['values']);
			if ($info){
				foreach ($info as $k=>$v){
					$info[$k]=array('displayname'=>$listByKey[$k]['displayname'],'value'=>$v);
				}
			}
			$this->assign('submitInfo',$info);
		}else {
			$submitted=0;
		}
		$this->assign('submitted',$submitted);
		$this->assign('list',$list);
		$this->display();
	}
}
function generateQRfromGoogle($chl,$widhtHeight ='150',$EC_level='L',$margin='0'){
	$chl = urlencode($chl);
    $src='http://chart.apis.google.com/chart?chs='.$widhtHeight.'x'.$widhtHeight.'&cht=qr&chld='.$EC_level.'|'.$margin.'&chl='.$chl;
    return $src;
}
?>