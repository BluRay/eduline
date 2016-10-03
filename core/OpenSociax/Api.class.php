<?php
/**
 * ThinkSNS API接口抽象类
 * @author lenghaoran
 * @version TS3.0
 */
abstract class Api {
    
    var $mid; //当前登录的用户ID
    var $since_id;
    var $max_id;
    var $page;
    var $count;
    var $user_id;
    var $user_name;
    var $id;
    var $data;
    
    private $_module_white_list = null; // 白名单模块

    /**
     * 架构函数
     * @param boolean $location 是否本机调用，本机调用不需要认证
     * @return void
     */
    public function __construct($location=false) {
        //外部接口调用
        if ($location == false && !defined('DEBUG')) {
            $this->verifyUser();
        } else {//本机调用
            $this->mid = @intval($_SESSION['mid']);
        }
        
        $GLOBALS['ts']['mid'] = $this->mid;
        
        //默认参数处理
        $this->since_id   = $_REQUEST['since_id']   ? intval($_REQUEST['since_id']) : '';
        $this->max_id     = $_REQUEST['max_id']     ? intval($_REQUEST['max_id'])   : '';
        $this->page       = $_REQUEST['page']       ? intval($_REQUEST['page'])     : 1;
        $this->count      = $_REQUEST['count']      ? intval($_REQUEST['count'])    : 20;
        $this->user_id    = $_REQUEST['user_id']    ? intval($_REQUEST['user_id'])  : 0;
        $this->user_name  = $_REQUEST['user_name']  ? h($_REQUEST['user_name'])     : '';
        $this->id         = $_REQUEST['id']         ? intval($_REQUEST['id'])       : 0;
        $this->data       = $_REQUEST;

        // findPage
        $_REQUEST[C('VAR_PAGE')] = $this->page;

        //接口初始化钩子
        Addons::hook('core_filter_init_api');
        
        //控制器初始化
        if(method_exists($this,'_initialize'))
            $this->_initialize();
    }

    /**
     * 用户身份认证
     * @return void
     */
    private function verifyUser() {
    	$canaccess =  false;
    	//ACL访问控制
    	if (file_exists(SITE_PATH.'/config/api.inc.php')) {
    		$acl = include SITE_PATH.'/config/api.inc.php';
    	}
    
    	if(!isset($acl['access']))
    		$acl['access'] = array('Oauth/*' => true);
    
    	if(isset($acl['access'][MODULE_NAME.'/'.ACTION_NAME])){
    		$canaccess =  (boolean) $acl['access'][MODULE_NAME.'/'.ACTION_NAME];
    	}elseif(isset($acl['access'][MODULE_NAME.'/*'])){
    		$canaccess =  (boolean) $acl['access'][MODULE_NAME.'/*'];
    	}else{
    		$canaccess =  false;
    	}
    	//OAUTH_TOKEN认证
    	if(isset($_REQUEST['oauth_token']) && !empty ( $_REQUEST['oauth_token'] ) && $_REQUEST['oauth_token'] != 'null' ){
    		$verifycode['oauth_token'] = h($_REQUEST['oauth_token']);
    		$verifycode['oauth_token_secret'] = h($_REQUEST['oauth_token_secret']);
    		$login = M('ZyLoginsync')->where($verifycode)->find();
    		if(isset($login['uid']) && $login['uid']>0){
    			$this->mid = (int) $login['uid'];
    			$_SESSION['mid'] = $this->mid;
    			$canaccess = true;
    		} else{
    			$canaccess = false;
    		}
    	}

		//白名单无需认证
    	if(!$canaccess){
    		$this->verifyError();
    	} else {
    		return;
    	}
    }
    
    /**
     * 输出API认证失败信息
     * @return  object|json
     */
    protected function verifyError() {
        $message['message'] = '认证失败';
        $message['code']    = '00001';
        exit( json_encode( $message ) );
    }

    /**
     * 通过api方法调用API时的赋值
     * api('WeiboStatuses')->data($data)->public_timeline();
     * @param array $data 方法调用时的参数
     * @return void
     */
    public function data($data){
        if(is_object($data)){
            $data   =   get_object_vars($data);
        }
        $this->since_id   = $data['since_id']   ? intval( $data['since_id'] ) : '';
        $this->max_id     = $data['max_id']     ? intval( $data['max_id'] )   : '';
        $this->page       = $data['page']       ? intval( $data['page'] )     : 1;
        $this->count      = $data['count']      ? intval( $data['count'] )    : 20;
        $this->user_id    = $data['user_id']    ? intval( $data['user_id'])   : $this->mid;
        $this->user_name  = $data['user_name']  ? h( $data['user_name'])      : '';
        $this->id         = $data['id']         ? intval( $data['id'])        : 0;
        $this->data = $data;
        return $this;
    }

    /**
     * api返回数据的标准格式
     * @param  [type] $data 待返回的数据
     * @param  [type] $code 错误码
     * @param  [type] $msg  提示信息
     * @return [json]       如果是本地调用返回数据数组.否则返回json
     */
    protected function exitJson($data=null,$code=0,$msg='ok'){
        if ($this->isLocation) return $data;
        header('Content-Type:application/json; charset=utf-8');
        exit(json_encode(array(
            'data' => $data,
            'code' => intval($code),
            'msg'  => $msg,
        )));
    }
    
    //组合limit
    protected function _limit(){
    	return ((($this->page-1)*$this->count).','.$this->count);
    }
}
?>