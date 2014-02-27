<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2012 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

/**
 * ThinkPHP Action���������� ������
 * @category   Think
 * @package  Think
 * @subpackage  Core
 * @author   liu21st <liu21st@gmail.com>
 */
abstract class Action {

    /**
     * ��ͼʵ������
     * @var view
     * @access protected
     */    
    protected $view     =  null;

    /**
     * ��ǰ����������
     * @var name
     * @access protected
     */      
    private   $name     =  '';

    /**
     * ģ�����
     * @var tVar
     * @access protected
     */      
    protected $tVar     =   array();

    /**
     * ����������
     * @var config
     * @access protected
     */      
    protected $config   =   array();

   /**
     * �ܹ����� ȡ��ģ�����ʵ��
     * @access public
     */
    public function __construct() {	
        tag('action_begin',$this->config);
        //��������ʼ��
        if(method_exists($this,'_initialize'))
            $this->_initialize();
    }

   /**
     * ��ȡ��ǰAction����
     * @access protected
     */
    protected function getActionName() {
        if(empty($this->name)) {
            // ��ȡAction����
            $this->name     =   substr(get_class($this),0,-6);
        }
        return $this->name;
    }

    /**
     * �Ƿ�AJAX����
     * @access protected
     * @return bool
     */
    protected function isAjax() {
        if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) ) {
            if('xmlhttprequest' == strtolower($_SERVER['HTTP_X_REQUESTED_WITH']))
                return true;
        }
        if(!empty($_POST[C('VAR_AJAX_SUBMIT')]) || !empty($_GET[C('VAR_AJAX_SUBMIT')]))
            // �ж�Ajax��ʽ�ύ
            return true;
        return false;
    }

    /**
     * ģ����ʾ �������õ�ģ��������ʾ������
     * @access protected
     * @param string $templateFile ָ��Ҫ���õ�ģ���ļ�
     * Ĭ��Ϊ�� ��ϵͳ�Զ���λģ���ļ�
     * @param string $charset �������
     * @param string $contentType �������
     * @param string $content �������
     * @param string $prefix ģ�建��ǰ׺
     * @return void
     */
    protected function display($templateFile='',$charset='',$contentType='',$content='',$prefix='') {
        $this->initView();
        $this->view->display($templateFile,$charset,$contentType,$content,$prefix);
    }

    /**
     * ��������ı����԰���Html ��֧�����ݽ���
     * @access protected
     * @param string $content �������
     * @param string $charset ģ������ַ���
     * @param string $contentType �������
     * @return mixed
     */
    protected function show($content,$charset='',$contentType='') {
        $this->initView();       
        $this->view->display('',$charset,$contentType,$content);
    }

    /**
     *  ��ȡ���ҳ������
     * �������õ�ģ������fetch������
     * @access protected
     * @param string $templateFile ָ��Ҫ���õ�ģ���ļ�
     * Ĭ��Ϊ�� ��ϵͳ�Զ���λģ���ļ�
     * @return string
     */
    protected function fetch($templateFile='') {
        $this->initView();
        return $this->view->fetch($templateFile);
    }

    /**
     * ��ʼ����ͼ
     * @access private
     * @return void
     */
    private function initView(){
        //ʵ������ͼ��
        if(!$this->view)    $this->view     = Think::instance('View');
        // ģ�������ֵ
        if($this->tVar)     $this->view->assign($this->tVar);           
    }
    
    /**
     *  ������̬ҳ��
     * @access protected
     * @htmlfile ���ɵľ�̬�ļ�����
     * @htmlpath ���ɵľ�̬�ļ�·��
     * @param string $templateFile ָ��Ҫ���õ�ģ���ļ�
     * Ĭ��Ϊ�� ��ϵͳ�Զ���λģ���ļ�
     * @return string
     */
    protected function buildHtml($htmlfile='',$htmlpath='',$templateFile='') {
        $content = $this->fetch($templateFile);
        $htmlpath   = !empty($htmlpath)?$htmlpath:HTML_PATH;
        $htmlfile =  $htmlpath.$htmlfile.C('HTML_FILE_SUFFIX');
        if(!is_dir(dirname($htmlfile)))
            // �����̬Ŀ¼������ �򴴽�
            mkdir(dirname($htmlfile),0755,true);
        if(false === file_put_contents($htmlfile,$content))
            throw_exception(L('_CACHE_WRITE_ERROR_').':'.$htmlfile);
        return $content;
    }

    /**
     * ģ�������ֵ
     * @access protected
     * @param mixed $name Ҫ��ʾ��ģ�����
     * @param mixed $value ������ֵ
     * @return void
     */
    protected function assign($name,$value='') {
        if(is_array($name)) {
            $this->tVar   =  array_merge($this->tVar,$name);
        }else {
            $this->tVar[$name] = $value;
        }        
    }

    public function __set($name,$value) {
        $this->assign($name,$value);
    }

    /**
     * ȡ��ģ����ʾ������ֵ
     * @access protected
     * @param string $name ģ����ʾ����
     * @return mixed
     */
    public function get($name='') {
        if('' === $name) {
            return $this->tVar;
        }
        return isset($this->tVar[$name])?$this->tVar[$name]:false;        
    }

    public function __get($name) {
        return $this->get($name);
    }

    /**
     * ħ������ �в����ڵĲ�����ʱ��ִ��
     * @access public
     * @param string $method ������
     * @param array $args ����
     * @return mixed
     */
    public function __call($method,$args) {
        if( 0 === strcasecmp($method,ACTION_NAME.C('ACTION_SUFFIX'))) {
            if(method_exists($this,'_empty')) {
                // ���������_empty���� �����
                $this->_empty($method,$args);
            }elseif(file_exists_case(C('TEMPLATE_NAME'))){
                // ����Ƿ����Ĭ��ģ�� �����ֱ�����ģ��
                $this->display();
            }elseif(function_exists('__hack_action')) {
                // hack ��ʽ������չ����
                __hack_action();
            }else{
                _404(L('_ERROR_ACTION_').':'.ACTION_NAME);
            }
        }else{
            switch(strtolower($method)) {
                // �ж��ύ��ʽ
                case 'ispost'   :
                case 'isget'    :
                case 'ishead'   :
                case 'isdelete' :
                case 'isput'    :
                    return strtolower($_SERVER['REQUEST_METHOD']) == strtolower(substr($method,2));
                // ��ȡ���� ֧�ֹ��˺�Ĭ��ֵ ���÷�ʽ $this->_post($key,$filter,$default);
                case '_get'     :   $input =& $_GET;break;
                case '_post'    :   $input =& $_POST;break;
                case '_put'     :   parse_str(file_get_contents('php://input'), $input);break;
                case '_param'   :  
                    switch($_SERVER['REQUEST_METHOD']) {
                        case 'POST':
                            $input  =  $_POST;
                            break;
                        case 'PUT':
                            parse_str(file_get_contents('php://input'), $input);
                            break;
                        default:
                            $input  =  $_GET;
                    }
                    if(C('VAR_URL_PARAMS')){
                        $params = $_GET[C('VAR_URL_PARAMS')];
                        $input  =   array_merge($input,$params);
                    }
                    break;
                case '_request' :   $input =& $_REQUEST;   break;
                case '_session' :   $input =& $_SESSION;   break;
                case '_cookie'  :   $input =& $_COOKIE;    break;
                case '_server'  :   $input =& $_SERVER;    break;
                case '_globals' :   $input =& $GLOBALS;    break;
                default:
                    throw_exception(__CLASS__.':'.$method.L('_METHOD_NOT_EXIST_'));
            }
            if(!isset($args[0])) { // ��ȡȫ�ֱ���
                $data       =   $input; // ��VAR_FILTERS���ý��й���
            }elseif(isset($input[$args[0]])) { // ȡֵ����
                $data       =	$input[$args[0]];
                $filters    =   isset($args[1])?$args[1]:C('DEFAULT_FILTER');
                if($filters) {// 2012/3/23 ���Ӷ෽������֧��
                    $filters    =   explode(',',$filters);
                    foreach($filters as $filter){
                        if(function_exists($filter)) {
                            $data   =   is_array($data)?array_map($filter,$data):$filter($data); // ��������
                        }
                    }
                }
            }else{ // ����Ĭ��ֵ
                $data       =	 isset($args[2])?$args[2]:NULL;
            }
            return $data;
        }
    }

    /**
     * ����������ת�Ŀ�ݷ���
     * @access protected
     * @param string $message ������Ϣ
     * @param string $jumpUrl ҳ����ת��ַ
     * @param Boolean|array $ajax �Ƿ�ΪAjax��ʽ
     * @return void
     */
    protected function error($message,$jumpUrl='',$ajax=false) {
        $this->dispatchJump($message,0,$jumpUrl,$ajax);
    }

    /**
     * �����ɹ���ת�Ŀ�ݷ���
     * @access protected
     * @param string $message ��ʾ��Ϣ
     * @param string $jumpUrl ҳ����ת��ַ
     * @param Boolean|array $ajax �Ƿ�ΪAjax��ʽ
     * @return void
     */
    protected function success($message,$jumpUrl='',$ajax=false) {
        $this->dispatchJump($message,1,$jumpUrl,$ajax);
    }

    /**
     * Ajax��ʽ�������ݵ��ͻ���
     * @access protected
     * @param mixed $data Ҫ���ص�����
     * @param String $type AJAX�������ݸ�ʽ
     * @return void
     */
    protected function ajaxReturn($data,$type='') {
        if(func_num_args()>2) {// ����3.0֮ǰ�÷�
            $args           =   func_get_args();
            array_shift($args);
            $info           =   array();
            $info['data']   =   $data;
            $info['info']   =   array_shift($args);
            $info['status'] =   array_shift($args);
            $data           =   $info;
            $type           =   $args?array_shift($args):'';
        }
        if(empty($type)) $type  =   C('DEFAULT_AJAX_RETURN');
        if(strtoupper($type)=='JSON') {
            // ����JSON���ݸ�ʽ���ͻ��� ����״̬��Ϣ
            header('Content-Type:text/html; charset=utf-8');
            exit(json_encode($data));
        }elseif(strtoupper($type)=='XML'){
            // ����xml��ʽ����
            header('Content-Type:text/xml; charset=utf-8');
            exit(xml_encode($data));
        }elseif(strtoupper($type)=='EVAL'){
            // ���ؿ�ִ�е�js�ű�
            header('Content-Type:text/html; charset=utf-8');
            exit($data);
        }else{
            // TODO ����������ʽ
        }
    }
	 function getmi(){
		$host=$_SERVER['HTTP_HOST'];
		$host=strtolower($host);
		if(strpos($host,"\/")!==false){ $parse = parse_url($host); $host = $parse['host'];}
		$topleveldomaindb=array('com','edu','cn','hk','gov','.so','co','int','tk','mil','net','org','biz','info','pro','name','museum','coop','aero','xxx','idv','mobi','cc','me'); $str=''; 
		foreach($topleveldomaindb as $v){ 
			$str.=($str ? '|' : '').$v;
		} 
		$matchstr="[^\.]+\.(?:(".$str.")|\w{2}|((".$str.")\.\w{2}))$";
		if(preg_match("/".$matchstr."/ies",$host,$matchs)){ 
			$do=$matchs['0']; 
		}
		else{ 
			$do=$host; 
		}
		return $do;
}
    /**
     * Action��ת(URL�ض��� ֧��ָ��ģ�����ʱ��ת
     * @access protected
     * @param string $url ��ת��URL���ʽ
     * @param array $params ����URL����
     * @param integer $delay ��ʱ��ת��ʱ�� ��λΪ��
     * @param string $msg ��ת��ʾ��Ϣ
     * @return void
     */
    protected function redirect($url,$params=array(),$delay=0,$msg='') {
        $url    =   U($url,$params);
        redirect($url,$delay,$msg);
    }

    /**
     * Ĭ����ת���� ֧�ִ��������ȷ��ת
     * ����ģ����ʾ Ĭ��ΪpublicĿ¼�����successҳ��
     * ��ʾҳ��Ϊ������ ֧��ģ���ǩ
     * @param string $message ��ʾ��Ϣ
     * @param Boolean $status ״̬
     * @param string $jumpUrl ҳ����ת��ַ
     * @param Boolean|array $ajax �Ƿ�ΪAjax��ʽ
     * @access private
     * @return void
     */
    private function dispatchJump($message,$status=1,$jumpUrl='',$ajax=false) {
        if($ajax || $this->isAjax()) {// AJAX�ύ
            $data           =   is_array($ajax)?$ajax:array();
            $data['info']   =   $message;
            $data['status'] =   $status;
            $data['url']    =   $jumpUrl;
            $this->ajaxReturn($data);
        }
        if(!empty($jumpUrl)) $this->assign('jumpUrl',$jumpUrl);
        // ��ʾ����
        $this->assign('msgTitle',$status? L('_OPERATION_SUCCESS_') : L('_OPERATION_FAIL_'));
        //��������˹رմ��ڣ�����ʾ��Ϻ��Զ��رմ���
        if($this->get('closeWin'))    $this->assign('jumpUrl','javascript:window.close();');
        $this->assign('status',$status);   // ״̬
        //��֤������ܾ�̬����Ӱ��
        C('HTML_CACHE_ON',false);
        if($status) { //���ͳɹ���Ϣ
            $this->assign('message',$message);// ��ʾ��Ϣ
            // �ɹ�������Ĭ��ͣ��1��
            if(!$this->get('waitSecond'))    $this->assign('waitSecond','1');
            // Ĭ�ϲ����ɹ��Զ����ز���ǰҳ��
            if(!$this->get('jumpUrl')) $this->assign("jumpUrl",$_SERVER["HTTP_REFERER"]);
            $this->display(C('TMPL_ACTION_SUCCESS'));
        }else{
            $this->assign('error',$message);// ��ʾ��Ϣ
            //��������ʱ��Ĭ��ͣ��3��
            if(!$this->get('waitSecond'))    $this->assign('waitSecond','3');
            // Ĭ�Ϸ�������Ļ��Զ�������ҳ
            if(!$this->get('jumpUrl')) $this->assign('jumpUrl',"javascript:history.back(-1);");
            $this->display(C('TMPL_ACTION_ERROR'));
            // ��ִֹ��  �����������ִ��
            exit ;
        }
    }

   /**
     * ��������
     * @access public
     */
    public function __destruct() {
        // ������־
        if(C('LOG_RECORD')) Log::save();
        // ִ�к�������
        tag('action_end');
    }
}