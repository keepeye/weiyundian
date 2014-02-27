<?php
/**
 *文本回复
**/
class TmplsAction extends UserAction{
	public function index(){
		$db=D('Wxuser');
		$where['token']=session('token');
		$where['uid']=session('uid');
		$info=$db->where($where)->find();
		$this->assign('info',$info);
		$this->display();
	}
	public function add(){
		$gets=$this->_get('style');
        Log::write("Tmpl:",Log::INFO);
		$db=M('Wxuser');
		switch($gets){
			case 1:
				$data['tpltypeid']=1;
				$data['tpltypename']='ty_index2';
				break;
			case 2:
				$data['tpltypeid']=2;
				$data['tpltypename']='mr_index';
				break;
			case 3:
				$data['tpltypeid']=3;
				$data['tpltypename']='weimob21_index';
				break;
			case 4:
				$data['tpltypeid']=4;
				$data['tpltypename']='ty_index';
				break;
			case 5:
				$data['tpltypeid']=5;
				$data['tpltypename']='flash_index';
				break;
			case 6:
				$data['tpltypeid']=6;
				$data['tpltypename']='weimob3_index';
				break;
			case 7:
				$data['tpltypeid']=7;
				$data['tpltypename']='weimob5_index';
				break;
			case 8:
				$data['tpltypeid']=8;
				$data['tpltypename']='weimob3_qc_index';
				break;
			case 9:
				$data['tpltypeid']=9;
				$data['tpltypename']='weimob6_index';
				break;
			case 10:
				$data['tpltypeid']=10;
				$data['tpltypename']='weimob11_index';
				break;
			case 11:
				$data['tpltypeid']=11;
				$data['tpltypename']='weimob13_index';
				break;
			case 12:
				$data['tpltypeid']=12;
				$data['tpltypename']='weimob14_index';
				break;
			case 13:
				$data['tpltypeid']=13;
				$data['tpltypename']='weimob15_index';
				break;
			case 14:
				$data['tpltypeid']=14;
				$data['tpltypename']='weimob16_index';
				break;
			case 15:
				$data['tpltypeid']=15;
				$data['tpltypename']='weimob18_index';
				break;
			case 16:
				$data['tpltypeid']=16;
				$data['tpltypename']='weimob20_index';
				break;
			case 17:
				$data['tpltypeid']=17;
				$data['tpltypename']='weimob22_index';
				break;
			case 18:
				$data['tpltypeid']=18;
				$data['tpltypename']='weimob23_index';
				break;
			case 19:
				$data['tpltypeid']=19;
				$data['tpltypename']='weimob24_index';
				break;
			case 20:
				$data['tpltypeid']=20;
				$data['tpltypename']='weimob25_index';
				break;
			case 21:
				$data['tpltypeid']=21;
				$data['tpltypename']='weimob30_index';
				break;
			case 22:
				$data['tpltypeid']=22;
				$data['tpltypename']='weimob33_index';
				break;
			case 23:
				$data['tpltypeid']=23;
				$data['tpltypename']='weimob34_index';
				break;
			case 24:
				$data['tpltypeid']=24;
				$data['tpltypename']='weimob35_index';
				break;
			case 25:
				$data['tpltypeid']=25;
				$data['tpltypename']='weimob36_index';
				break;
		}
		$where['token']=session('token');
		$back=$db->where($where)->save($data);
        if($back){
            $this->success('操作成功');
        }else{
            $this->error('操作失败');
        }   
	}
	public function lists(){
		$gets=$this->_get('style');
		$db=M('Wxuser');
		switch($gets){
			case 4:
				$data['tpllistid']=4;
				$data['tpllistname']='ktv_list';
				break;
			case 1:
				$data['tpllistid']=1;
				$data['tpllistname']='yl_list';
				break;
		}
		$where['token']=session('token');
		$db->where($where)->save($data);
	}
	public function content(){
		$gets=$this->_get('style');
		$db=M('Wxuser');
		switch($gets){
			case 1:
				$data['tplcontentid']=1;
				$data['tplcontentname']='yl_content';
				break;
			case 3:
				$data['tplcontentid']=3;
				$data['tplcontentname']='ktv_content';
				break;
		}
		$where['token']=session('token');
		$db->where($where)->save($data);
	}
	public function insert(){
	
	}
	public function upsave(){
	
	}
}
?>
