<?php

class BaseAction extends Action
{
    protected function _initialize()
    {
        define('RES', THEME_PATH . 'common');
        define('STATICS', TMPL_PATH . 'static');
        $this->assign('action', $this->getActionName());
    }
	
    //自动回复关键词表keyword相关模块列表 by 成
	protected function getKewordModules(){
		$arr = array(
                    'Img'=>'图文回复',
                    'Text'=>'文本回复',
                    'Voiceresponse'=>'语音回复',
                    'Lottery'=>'大转盘',
                    'Host'=>'酒店KTV',
                    'Product'=>'微商品',
                    'Selfform'=>'微报名',
                	'House'=>'微房产',
					'Ordering'=>'订单',
                    'Diaoyan'=>'微调研'
        );
		return $arr;
	}
	
	
    protected function all_insert($name = '', $back = '/index')
    {
        $name = $name ? $name : MODULE_NAME;
        $db   = D($name);
        if ($db->create() === false) {
            $this->error($db->getError());
        } else {
            $id = $db->add();
            if ($id) {
                $m_arr = array_keys($this->getKewordModules());//关键词关联模块列表 by 成
                if (in_array($name, $m_arr) && !empty($_POST['keyword'])) {
                    $data['pid']     = $id;
                    $data['module']  = $name;
                    $data['token']   = session('token');
                    $keywords = explode(" ",$_POST['keyword']);
                    foreach($keywords as $keyword){
                        $data['keyword'] = $keyword;
                        M('Keyword')->add($data);
                    }
                }
                $this->success('操作成功', U(MODULE_NAME . $back));
            } else {
                $this->error('操作失败', U(MODULE_NAME . $back));
            }
        }
    }
    
    protected function insert($name = '', $back = '/index')
    {
        $name = $name ? $name : MODULE_NAME;
        $db   = D($name);
        if ($db->create() === false) {
            $this->error($db->getError());
        } else {
            $id = $db->add();
            if ($id == true) {
                $this->success('操作成功', U(MODULE_NAME . $back));
            } else {
                $this->error('操作失败', U(MODULE_NAME . $back));
            }
        }
    }
    
    protected function save($name = '', $back = '/index')
    {
        $name = $name ? $name : MODULE_NAME;
        $db   = D($name);
        if ($db->create() === false) {
            $this->error($db->getError());
        } else {
            $id = $db->save();
            if ($id == true) {
                $this->success('操作成功', U(MODULE_NAME . $back));
            } else {
                $this->error('操作失败', U(MODULE_NAME . $back));
            }
        }
    }
    
    protected function all_save($name = '', $back = '/index')
    {
        $name = $name ? $name : MODULE_NAME;
        $db   = D($name);
        if ($db->create() === false) {
            $this->error($db->getError());
        } else {
            $id = $db->save();
            if ($id!==false) {

                $m_arr = array_keys($this->getKewordModules());
                if (in_array($name, $m_arr) && !empty($_POST['keyword'])) {
                    $data['pid']    = $_POST['id'];
                    $data['module'] = $name;
                    $data['token']  = session('token');
					M('Keyword')->where($data)->delete();
					$keywords = explode(" ",$_POST['keyword']);
                    foreach($keywords as $keyword){
                        $data['keyword'] = $keyword;
                        M('Keyword')->add($data);
                    }
					
                }
                $this->success('操作成功', U(MODULE_NAME . $back));
            } elseif($id === false) {
                $this->error('操作失败', U(MODULE_NAME . $back));
            }else
			{

                $this->success('没有变化', U(MODULE_NAME . $back));
			}
        }
    }
    
    protected function all_del($id, $name = '', $back = '/index')
    {
        $name = $name ? $name : MODULE_NAME;
        $db   = D($name);
        if ($db->delete($id)) {
            $this->ajaxReturn('操作成功', U(MODULE_NAME . $back));
        } else {
            $this->ajaxReturn('操作失败', U(MODULE_NAME . $back));
        }
    }
}

?>
