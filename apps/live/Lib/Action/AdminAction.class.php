<?php
/**
 * 后台直播管理
 * @author wangjun@chuyouyun.com
 * @version chuyouyun2.0
 */
tsload(APPS_PATH.'/admin/Lib/Action/AdministratorAction.class.php');
class AdminAction extends AdministratorAction
{
	/**
	 * 初始化，
	 */
	public function _initialize() {
		$this->pageTitle['index']  = '直播间列表';
		$this->pageTitle['create'] = '创建直播间';
		$this->pageTitle['update'] = '修改直播间';
		
		$this->pageTab[] = array('title'=>'直播间列表','tabHash'=>'index','url'=>U('live/Admin/index'));
		$this->pageTab[] = array('title'=>'创建直播间','tabHash'=>'create','url'=>U('live/Admin/create'));
		parent::_initialize();
	}
	
	//直播间列表（带分页）
	public function index(){
		$_REQUEST['tabHash'] = 'index';
		$this->pageKeyList = array('id','name','templateTypeTxt','barrage','DOACTION');
		$url   = C('API_URL').'room/info?';
		$param = 'userid='.C('USER_ID');
		$hash  = md5( $param.'&time='.time().'&salt='.C('API_KEY') );
		$url   = $url.$param.'&time='.time().'&hash='.$hash;
		$list  = $this->getDataByUrl($url);
		foreach($list['rooms'] as &$val){
			$val['templateTypeTxt']  = '模板'.$val['templateType'];
			$val['barrage']          = $val['barrage'] ? '开启' : '关闭';
			if( $val['status'] == 10 ) {
				$val['DOACTION'] 		  = '<a href="'.U('live/Admin/update',array('roomid'=>$val['id'])).'">编辑</a> | ';
				$val['DOACTION']         .= '<a href="'.U('live/Admin/info',array('roomid'=>$val['id'])).'">直播列表</a>  | ';
				$val['DOACTION']         .= '<a href="'.U('live/Admin/getCode',array('roomid'=>$val['id'])).'">代码</a> | ';
				$val['DOACTION']         .= '<a href="'.U('live/Admin/close',array('roomid'=>$val['id'])).'">关闭</a> | ';
				$val['DOACTION']         .= '<a href="javascript:;">监控</a>';
			} else {
				$val['DOACTION'] 		  = '<a style="color:#bababa;">编辑</a> | ';
				$val['DOACTION']         .= '<a href="'.U('live/Admin/info',array('roomid'=>$val['id'])).'">直播列表</a>  | ';
				$val['DOACTION']         .= '<a style="color:#bababa;">代码</a> | ';
				$val['DOACTION']         .= '<a style="color:#bababa;">关闭</a> | ';
				$val['DOACTION']         .= '<a style="color:#bababa;">监控</a>';
			}
		}
		$list['data'] = $list['rooms'];
		$this->displayList($list);
	}
	
	//创建直播间
	public function create(){
		if( isset($_POST) ) {
			$roomid = t($_REQUEST['roomid']);
			$url    = C('API_URL').'room/create?';
			$param  = 'assistantpass='.t($_POST['assistantpass']).'&authtype='.t($_POST['authtype']).'&barrage='.t($_POST['barrage']).'&checkurl='.t($_POST['checkurl']).'&desc='.$_POST['desc'].'&foreignpublish='.t($_POST['foreignpublish']).'&name='.t($_POST['name']).'&playpass='.t($_POST['playpass']).'&publisherpass='.t($_POST['publisherpass']).'&templatetype='.t($_POST['templatetype']).'&userid='.C('USER_ID');
			$hash   = md5( $param.'&time='.time().'&salt='.C('API_KEY') );
			$url    = $url.$param.'&time='.time().'&hash='.$hash;
			$res   = $this->getDataByUrl($url);
			if($res['result'] == 'OK') {
				$this->assign( 'jumpUrl', U('live/Admin/index') );
				$this->success('创建成功');
			} else {
				$this->error('创建失败');
			}
		} else {
			$_REQUEST['tabHash'] = 'create';
			$this->pageKeyList   = array('name','templatetype','authtype','publisherpass','assistantpass','playpass','checkurl','barrage','foreignpublish','desc');
			$this->opt['barrage']        = array('1'=>'开启','0'=>'不开启');
			$this->opt['foreignpublish'] = array('1'=>'开启','0'=>'不开启');
			$this->opt['authtype']       = array('0'=>'接口验证','1'=>'密码验证','2'=>'免密码验证');
			$this->savePostUrl = U('live/Admin/create');
			$this->displayConfig();
		}
		
	}
	
	//编辑直播间
	public function update(){
		if( isset($_POST) ) {
			$url    = C('API_URL').'room/update?';
			$param  = 'assistantpass='.t($_POST['assistantPass']).'&authtype='.t($_POST['authType']).'&barrage='.t($_POST['barrage']).'&checkurl='.t($_POST['checkUrl']).'&desc='.$_POST['desc'].'&name='.t($_POST['name']).'&playpass='.t($_POST['playPass']).'&publisherpass='.t($_POST['publisherPass']).'&roomid='.t($_POST['id']).'&userid='.C('USER_ID');
			$hash   = md5( $param.'&time='.time().'&salt='.C('API_KEY') );
			$url    = $url.$param.'&time='.time().'&hash='.$hash;
			$res    = $this->getDataByUrl($url);
			if($res['result'] == 'OK') {
				$this->assign( 'jumpUrl', U('live/Admin/index') );
				$this->success('修改成功');
			} else {
				$this->error('修改失败');
			}
		} else {
			$_REQUEST['tabHash'] = 'create';
			$this->pageKeyList = array('id','name','authType','publisherPass','assistantPass','playPass','checkUrl','barrage','desc');
			$this->opt['barrage']        = array('1'=>'开启','0'=>'不开启');
			$this->opt['foreignPublish'] = array('1'=>'开启','0'=>'不开启');
			$this->opt['authType']       = array('0'=>'接口验证','1'=>'密码验证','2'=>'免密码验证');
			
			$roomid = t($_REQUEST['roomid']);
			$list   = $this->roomInfo($roomid);
			$this->savePostUrl = U('live/Admin/update');
			$this->displayConfig($list['room']);
		}
	}
	
	//关闭直播间
	public function close(){
		$roomid = t($_REQUEST['roomid']);
		$url    = C('API_URL').'room/close?';
		$param  = 'roomid='.$roomid.'&userid='.C('USER_ID');
		$hash   = md5( $param.'&time='.time().'&salt='.C('API_KEY') );
		$url    = $url.$param.'&time='.time().'&hash='.$hash;
		$res = $this->getDataByUrl($url);
		if($res['result'] == 'OK') {
			$this->success('关闭成功');
		} else {
			$this->error('关闭失败');
		}
	}
	
	//直播间信息
	private function roomInfo($roomid){
		$url    = C('API_URL').'room/search?';
		$param  = 'roomid='.$roomid.'&userid='.C('USER_ID');
		$hash   = md5( $param.'&time='.time().'&salt='.C('API_KEY') );
		$url    = $url.$param.'&time='.time().'&hash='.$hash;
		return $this->getDataByUrl($url);
	}
	
	//直播列表信息（带分页）
	public function info(){
		$roomid = t($_REQUEST['roomid']);
		$url    = C('API_URL').'live/info?';
		$param  = 'roomid='.$roomid.'&userid='.C('USER_ID');
		$hash   = md5( $param.'&time='.time().'&salt='.C('API_KEY') );
		$url    = $url.$param.'&time='.time().'&hash='.$hash;
		$list   = $this->getDataByUrl($url);
		$this->assign('list',$list);
		$this->assign('type','info');
		$this->display('list');
	}
	
	//直播间连接数统计
	public function connections(){
	
	}
	
	//获取直播间代码
	public function getCode(){
		$roomid = t($_REQUEST['roomid']);
		$url    = C('API_URL').'room/code?';
		$param  = 'roomid='.$roomid.'&userid='.C('USER_ID');
		$hash   = md5( $param.'&time='.time().'&salt='.C('API_KEY') );
		$url    = $url.$param.'&time='.time().'&hash='.$hash;
		$list   = $this->getDataByUrl($url);
		$this->assign('list',$list);
		$this->assign('type','code');
		$this->display('list');
	}
	
	//获取直播间内用户登录、退出行为统计
	public function useraction(){
	
	}
	
	//直播间模板信息
	public function templateInfo(){
		$url    = C('API_URL').'viewtemplate/info?';
		$param  = 'userid='.C('USER_ID');;
		$hash   = md5( $param.'&time='.time().'&salt='.C('API_KEY') );
		$url    = $url.$param.'&time='.time().'&hash='.$hash;
		$list = $this->getDataByUrl($url);
		dump($list);exit;
	}
	
	//根据url读取文本
	private function getDataByUrl($url , $type = true){
		return json_decode(file_get_contents($url) , $type);
	}
}