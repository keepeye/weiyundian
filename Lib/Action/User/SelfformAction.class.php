<?php
class SelfformAction extends UserAction{
	public $token;
	public $selfform_model;
	public $selfform_input_model;
	public $selfform_value_model;
	public function _initialize() {
		
		parent::_initialize();
		$token_open=M('token_open')->field('queryname')->where(array('token'=>session('token')))->find();
		if(!strpos($token_open['queryname'],'diymen_set')){
            	$this->error('您还开启该模块的使用权,请到功能模块中添加',U('Function/index',array('token'=>session('token'),'id'=>session('wxid'))));
		}
		$this->selfform_model=M('Selfform');
		$this->selfform_input_model=M('Selfform_input');
		$this->selfform_value_model=M('Selfform_value');
		$this->token=session('token');
		$this->assign('token',$this->token);
		$this->assign('module','Selfform');
	}
	public function index(){
		if (IS_POST){
			if ($_POST['token']!=$this->token){
				exit();
			}
			for ($i=0;$i<40;$i++){
				if (isset($_POST['id_'.$i])){
					
				}
			}
			$this->success('操作成功',U('Selfform/index',array('token'=>$this->token)));
		}else{
			$where=array('token'=>$this->token);
			if(IS_POST){
				$key = $this->_post('searchkey');
				if(empty($key)){
					$this->error("关键词不能为空");
				}

				$where['name|intro|keyword|content'] = array('like',"%$key%");
				$list = $this->selfform_model->where($where)->select();
				$count      = $this->selfform_model->where($where)->count();
				$Page       = new Page($count,20);
				$show       = $Page->show();
			}else {
				$count      = $this->selfform_model->where($where)->count();
				$Page       = new Page($count,20);
				$show       = $Page->show();
				$list=$this->selfform_model->where($where)->order('time DESC')->select();
			}
			$this->assign('list',$list);

			$this->assign('page',$show);
			$this->display();
		}
	}
	public function add(){ 
		if(IS_POST){
			$_POST['endtime'] = strtotime($_POST['enddate']);
			$this->all_insert('Selfform','/index?token='.$this->token);
		}else{
			$set=array();
			$set['endtime']=time()+10*24*3600;
			$this->assign('set',$set);
			$this->display('set');
		}
	}
	public function set(){

        $id = intval($this->_get('id')); 
		$checkdata = $this->selfform_model->where(array('id'=>$id))->find();
		if(empty($checkdata)||$checkdata['token']!=$this->token){
            $this->error("没有相应记录.您现在可以添加.",U('Selfform/add'));
        }
		if(IS_POST){ 
			$_POST['endtime'] = strtotime($_POST['enddate']);
            $where=array('id'=>$this->_post('id'),'token'=>$this->token);
			$check=$this->selfform_model->where($where)->find();
			if($check==false)$this->error('非法操作');
			if($this->selfform_model->create()){
				if(false !== $this->selfform_model->where($where)->save()){
					$this->success('修改成功!',U('Selfform/index',array('token'=>$this->token)));
					$keyword_model=M('Keyword');
					$keyword_model->where(array('token'=>$this->token,'pid'=>$id,'module'=>'Selfform'))->save(array('keyword'=>$_POST['keyword']));
				}else{
					$this->error('操作失败');
				}
			}else{
				$this->error($this->selfform_model->getError());
			}
		}else{
			$this->assign('isUpdate',1);
			
			$this->assign('set',$checkdata);
			$this->display();	
		
		}
	}
	public function del(){
		if($this->_get('token')!=$this->token){$this->error('非法操作');}
        $id = intval($this->_get('id'));
        if(IS_GET){                              
            $where=array('id'=>$id,'token'=>$this->token);
            $check=$this->selfform_model->where($where)->find();
            if($check==false)   $this->error('非法操作');

            $back=$this->selfform_model->where($wehre)->delete();
            if($back==true){
            	$keyword_model=M('Keyword');
            	$keyword_model->where(array('token'=>$this->token,'pid'=>$id,'module'=>'Selfform'))->delete();
                $this->success('操作成功',U('Selfform/index',array('token'=>$this->token)));
            }else{
                 $this->error('服务器繁忙,请稍后再试',U('Selfform/index',array('token'=>$this->token)));
            }
        }        
	}
	public function inputs(){
		$formid=$this->_get('id');
		$thisForm=$this->selfform_model->where(array('id'=>$formid))->find();
		//dump($thisForm);
		if ($thisForm['token']!=$this->token){
			//exit();
		}
		$this->assign('thisForm',$thisForm);
		$where=array('formid'=>$formid);
		$list = $this->selfform_input_model->where($where)->order('taxis ASC')->select();
		$this->assign('list',$list);
		$this->display();
	}
	public function inputAdd(){ 
		if(IS_POST){
			$this->insert('Selfform_input','/inputs?id='.$this->_post('formid'));
		}else{
			$formid=intval($_GET['formid']);
			$thisForm=$this->selfform_model->where(array('id'=>$formid))->find();
			$this->assign('thisForm',$thisForm);

			$this->inputTypeOptions();
			$this->requireOptions(0);
			$this->regexOptions();
			$this->display('inputSet');
		}
	}
	public function inputTypeOptions($selected=''){
		$options=array(
		array('value'=>'','text'=>'请选择'),
		array('value'=>'text','text'=>'文本输入框'),
		array('value'=>'textarea','text'=>'多行文本输入框'),
		array('value'=>'select','text'=>'下拉列表')
		);
		$str='';
		foreach ($options as $o){
			$selectedStr='';
			if ($selected==$o['value']){
				$selectedStr=' selected';
			}
			$str.='<option value="'.$o['value'].'"'.$selectedStr.'>'.$o['text'].'</option>';
		}
		$this->assign('inputTypeOptions',$str);
	}
	public function requireOptions($selected=0){
		$options=array(
		array('value'=>'1','text'=>'必填'),
		array('value'=>'0','text'=>'不是必填')
		);
		$str='';
		foreach ($options as $o){
			$selectedStr='';
			if ($selected==$o['value']){
				$selectedStr=' selected';
			}
			$str.='<option value="'.$o['value'].'"'.$selectedStr.'>'.$o['text'].'</option>';
		}
		$this->assign('requireOptions',$str);
	}
	public function regexOptions(){
		$options=array(
		array('value'=>'','text'=>'选择常用输入限制'),
		array('value'=>'/^[A-Za-z]+$/','text'=>'英文大小写字符'),
		//array('value'=>'/^[\x{4e00}-\x{9fa5}]+$/u','text'=>'纯中文'),
		array('value'=>'/^[\u4e00-\u9fa5]+$/','text'=>'纯中文'),
		array('value'=>'/\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/','text'=>'邮箱'),
		array('value'=>'/^[1-9]\d*|0$/','text'=>'0或正整数'),
		array('value'=>'/^[0-9]{1,30}$/','text'=>'正整数'),
		array('value'=>'/^[-\+]?\d+(\.\d+)?$/','text'=>'小数'),
		array('value'=>'/^13[0-9]{9}$|^15[0-9]{9}$|^18[0-9]{9}$/','text'=>'手机'),
		);
		$str='';
		foreach ($options as $o){
			$str.='<option value="'.$o['value'].'">'.$o['text'].'</option>';
		}
		$this->assign('regexOptions',$str);
	}
	public function inputSet(){
        $id = intval($this->_get('id')); 
		$checkdata = $this->selfform_input_model->where(array('id'=>$id))->find();
		if(empty($checkdata)){
            $this->error("没有相应记录.您现在可以添加.",U('Selfform/inputAdd'));
        }
		if(IS_POST){
            $where=array('id'=>$this->_post('id'));
            $thisInput=$this->selfform_input_model->where($where)->find();
            $thisForm=$this->selfform_model->where(array('id'=>$thisInput['formid']))->find();
            if ($thisForm['token']!=$this->token){
            	exit();
            }
			if($this->selfform_input_model->create()){
			$rt=$this->selfform_input_model->where($where)->save($_POST);
				if(false !== $rt){
					$this->success('修改成功',U('Selfform/inputs',array('token'=>$this->token,'id'=>$thisForm['id'])));
				}else{
					$this->error('操作失败');
				}
			}else{
				$this->error($this->selfform_input_model->getError());
			}
		}else{
			$this->assign('isUpdate',1);
			
			$this->assign('set',$checkdata);
			//
			$formid=intval($checkdata['formid']);
			$thisForm=$this->selfform_model->where(array('id'=>$formid))->find();
			$this->assign('thisForm',$thisForm);

			$this->inputTypeOptions($checkdata['inputtype']);
			$this->requireOptions($checkdata['require']);
			$this->regexOptions();
			$this->display();	
		
		}
	}
	public function inputDelete(){
		$id = intval($this->_get('id'));
		$where=array('id'=>$id);
		$thisInput=$this->selfform_input_model->where($where)->find();
		$thisForm=$this->selfform_model->where(array('id'=>$thisInput['formid']))->find();
		if ($thisForm['token']!=$this->token){
			exit();
		}
		$back=$this->selfform_input_model->where($wehre)->delete();
		if($back==true){
			$this->success('操作成功',U('Selfform/inputs',array('token'=>$this->token,'id'=>$thisForm['id'])));
		}else{
			$this->error('服务器繁忙,请稍后再试',U('Selfform/inputs',array('token'=>$this->token,'id'=>$thisForm['id'])));
		}
	}
	//
	public function infos(){
		//form
		$id = intval($this->_get('id'));
		$where=array('id'=>$id);
		$thisForm=$this->selfform_model->where($where)->find();
		$this->assign('thisForm',$thisForm);
		if ($thisForm['token']!=$this->token){
			exit();
		}
		//fields
		$fieldWhere=array('formid'=>$thisForm['id']);
		$fields = $this->selfform_input_model->where($fieldWhere)->order('taxis ASC')->select();
		$fieldsByKey=array();
		if ($fields){
			$i=0;
			foreach ($fields as $l){
				$fieldsByKey[$l['fieldname']]=$l;
				$i++;
			}
		}
		$this->assign('fields',$fields);
		//提交的信息
		$infoWhere=array('formid'=>$thisForm['id']);
		$key = I('searchkey');
		if(!empty($key)){
			$infoWhere['values'] = array('like',"%$key%");
		}
		$count      = $this->selfform_value_model->where($infoWhere)->count();
		$Page       = new Page($count,20);
		$show       = $Page->show();
		$list=$this->selfform_value_model->where($infoWhere)->order('time DESC')->limit($Page->firstRow.','.$Page->listRows)->select();
		if ($list){
			foreach ($list as $k=>$l){
				$values=unserialize($l['values']);
				if ($fields){
					foreach ($fields as $f){
						$list[$k]['vallist'][$f['fieldname']]=$values[$f['fieldname']];
					}
				}
				$list[$k]['time']=date('Y-m-d H:i:s',$l['time']);
				
			}
			
		}
		$this->assign('count',$count);
		$this->assign('page',$show);
		$this->assign('list',$list);
		//
		$this->display();
	}
	public function infoDelete(){
		$thisInfo=$this->selfform_value_model->where(array('id'=>intval($_GET['id'])))->find();
		$thisFrom=$this->selfform_model->where(array('id'=>$thisInfo['formid']))->find();
		if ($thisFrom['token']!=$this->token){
			exit();
		}
		$back=$this->selfform_value_model->where(array('id'=>intval($_GET['id'])))->delete();
		if($back==true){
			$this->success('操作成功',U('Selfform/infos',array('token'=>$this->token,'id'=>$thisFrom['id'])));
		}else{
			$this->error('服务器繁忙,请稍后再试',U('Selfform/infos',array('token'=>$this->token,'id'=>$thisFrom['id'])));
		}
	}


	//导出excel
	function exportExcel(){
		$id = I('id','0','intval');//活动id
		if(!$id){
			exit('非法id');
		}
		//查询活动基本信息
		$thisForm = $this->selfform_model->where(array("id"=>$id,"token"=>$this->token))->find();

		if(!$thisForm){
			exit('活动不存在');
		}
		//读取自定义字段信息
		$fields = array();
		$fields = $this->selfform_input_model->where(array('formid'=>$thisForm['id']))->order('taxis ASC')->select();
		//导入excel类文件
		import("@.ORG.phpexcel.Classes.PHPExcel",'',".php");
		$objPHPExcel = new PHPExcel();
		// 设置列名
		$excelobj = $objPHPExcel->setActiveSheetIndex(0);

		//判断是否有自定义表单设置
		if(!empty($fields)){
			$column_start = ord('A');//从C列开始，取得C字母的ASCII码
			$extra_columns = array();
			foreach($fields as $field){
				$column_name = chr($column_start);
				$extra_columns[$column_name] = $field;
				$excelobj->setCellValue($column_name.'1', $field['displayname']);//设定一个列来存储表单字段
				$column_start += 1;//ASCII码加+1表示下一个列字母
			}

		}
		//读取数据
		$list=$this->selfform_value_model->where(array('formid'=>$thisForm['id']))->select();
		$i=2;
		//自定义表单遍历
		foreach($list as $item){
			$item['time'] = $item['time']>0?date("Y-m-d H:i:s",$item['time']):"0";
			
			$excelobj = $objPHPExcel->setActiveSheetIndex(0);
		    $formdata = unserialize($item['values']);//用户提交的表单数据
		    foreach($extra_columns as $k1=>$v1){
		    	$excelobj->setCellValueExplicit($k1.$i, $formdata[$v1['fieldname']],PHPExcel_Cell_DataType::TYPE_STRING);
		    }
		    unset($k1,$v1);
		    $i++;
		}
		
		//设置sheet标题
		$objPHPExcel->getActiveSheet()->setTitle('报名记录');
		$objPHPExcel->setActiveSheetIndex(0);

		// Redirect output to a client’s web browser (Excel5)
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="'.$thisForm['name'].'报名统计"');
		header('Cache-Control: max-age=0');
		// If you're serving to IE 9, then the following may be needed
		header('Cache-Control: max-age=1');

		// If you're serving to IE over SSL, then the following may be needed
		header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
		header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
		header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
		header ('Pragma: public'); // HTTP/1.0

		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
		exit;

	}
}

