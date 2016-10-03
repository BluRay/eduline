<?php
/**
 * 云播公共控制器
 * @author ashangmanage <ashangmanage@phpzsm.com>
 * @version CY1.0
 */
tsload(APPS_PATH.'/classroom/Lib/Action/CommonAction.class.php');
class PublicAction extends CommonAction
{
	private $_config;					// 注册配置信息字段
	private $_invite;					// 是否是邀请注册
	private $_invite_code;				// 邀请码
	/**
	 * 初始化，配置内容标题
	 * @return void
	 */
	public function _initialize() {
		parent::_initialize();


		$this->_config = model('Xdata')->get('admin_Config:register');
		$this->_invite = false;
	}

	//http://127.0.0.1/gaojiao/index.php?app=classroom&mod=Public&act=index
	/**
	 * 云播公共控制器
	 * @return void
	 */
	public function index()
	{
		D('ZyQuestion')->myAnswer(4);
		exit;
		$this->display();
	}
	/**
	 * 处理意见反馈
	 * @return bool
	 */
	public function dosuggest(){
		$value = t($_POST['value']);
		$code  = t($_POST['code']);

		if(!trim($code)){
			$this->mzError('参数错误!');
		}

		if($code == session('mzoncecode')){
			$data['uid']     = intval($this->mid);
			$data['content'] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');;
			$data['ctime']   = time();
				
			$i = model('ZySuggest')->add($data);
				
			session('mzoncecode',null);//将其清除掉此时再按F5则无效
			if($i){
				//添加成功!
				$this->mzSuccess('添加成功!');
			}else{
				//添加失败!
				$this->mzError('添加失败!');
			}
		}else{
			//请不要重复刷新
			$this->mzError('请不要重复刷新!');
		}
	}
    /**
     *      关注对方
     */
    public function followyou(){
        $fid=intval($_POST['fid']);
        $uid=$this->mid;
        if ( intval( $uid ) <= 0 || $fid <= 0 ){
            $this->mzError(L('PUBLIC_WRONG_DATA')) ;			// 错误的参数
        }
        if ($uid == $fid){
            $this->mzError(L('PUBLIC_FOLLOWING_MYSELF_FORBIDDEN'));		// 不能关注自己

        }

        if (!model('User')->find($fid)){
            $this->mzError(L('PUBLIC_FOLLOWING_PEOPLE_NOEXIST'));			// 被关注的用户不存在
        }

        if (model('UserPrivacy')->isInBlackList($uid,$fid)) {
            $this->mzError('根据对方设置，您无法关注TA');

        }else if(model('UserPrivacy')->isInBlackList($fid,$uid)){
            $this->mzError('您已把对方加入黑名单');

        }
        //维护感兴趣的人的缓存
        model('Cache')->set('related_user_'.$uid, '' , 24 * 60 * 60);
        // 获取双方的关注关系
        $follow_state = model('Follow')->getFollowState($uid, $fid);
        // 未关注状态
        if(0 == $follow_state['following']) {
            // 添加关注
            $map['uid']  = $uid;
            $map['fid']  = $fid;
            $map['ctime'] = time();
            $result = model('Follow')->add($map);
            S('follow_remark_'.$uid,null);
            if($result) {
                $maps['key'] = 'email';
                $maps['uid'] = $fid;
                $isEmail = D('user_privacy')->where($map)->field('value')->find();
                if($isEmail['value'] === 0){
                    $userInfo = model('User')->getUserInfo($fid);
                    model('Mail')->send_email($userInfo['email'],'您增加了一个新粉丝','content');
                }
                $this->mzSuccess(L('PUBLIC_ADD_FOLLOW_SUCCESS')) ;			// 关注成功
                $this->_updateFollowCount($uid, $fid, true);			// 更新统计

            } else {
                $this->mzError(L('PUBLIC_ADD_FOLLOW_FAIL'));				// 关注失败

            }
        }else {
            $this->mzError( L('PUBLIC_FOLLOW_ING'));						// 已关注
        }
        $res=model('Follow')->doFollow($this->mid,$fid);
        if($res){
            $this->mzSuccess("关注成功");
        }else{
            $this->mzError(model('Follow')->error);
        }
    }
	/**
	 * 处理投票
	 * @return bool
	 */
	public function doreviewvote(){
		$kztype = intval($_POST['kztype']);
		$kzid   = intval($_POST['kzid']);
		$type   = intval($_POST['type']);
		$uid    = intval($this->mid);

		if($kztype <= 0){
			$this->mzError('投票资源错误!');
		}

		if(!$uid){
			$this->mzError('投票需要登录');
		}


		$zyVoteMod = D('ZyVote');
		$stable    = $zyVoteMod->_collType[$kztype];

		if($type){
			//取消投票
			$i = $zyVoteMod->delvote($kzid,$stable,$uid);
		}else{
			//投票
			$i = $zyVoteMod->addvote(array(
				'uid'               => $uid,
				'source_id'         => $kzid,
				'source_table_name' => $stable,
				'ctime'             => time(),
			));
		}
		$this->mzSuccess('点赞成功!');
	}

	/**
	 * classroom/Public/collect
	 * 收藏功能
	 * 专辑收藏/课程收藏/提问收藏/笔记收藏/点评收藏
	 *  1=>'zy_album',//专辑收藏
		2=>'zy_video',//课程收藏
		3=>'zy_question',//提问收藏
		4=>'zy_note',//笔记收藏
		5=>'zy_review',//点评收藏
	 * @param int $type 0:取消收藏;1:收藏;
	 * @return bool
	 */
	public function collect(){
		$zyCollectionMod = D('ZyCollection');
		$type   = intval($_POST['type']);//0:取消收藏;1:收藏;
		$sctype = intval($_POST['sctype']);//专辑收藏/课程收藏/提问收藏/笔记收藏/点评收藏
		$source_id = intval($_POST['source_id']);//资源ID
		if($sctype <= 0){
			$this->mzError('收藏资源错误!');
		}
		$data['uid'] = intval($this->mid);
		$data['source_id'] = intval($source_id);
		$data['source_table_name'] = $zyCollectionMod->_collType[$sctype];
		$data['ctime'] = time();
		if(!$type){
			$i = $zyCollectionMod->delcollection($data['source_id'],$data['source_table_name'],$data['uid']);
			if($i === false){
				$this->mzError($zyCollectionMod->getError());
			}else{
				$this->mzSuccess('取消收藏成功!');
			}
		}else{
			$i = $zyCollectionMod->addcollection($data);
			if($i === false){
				$this->mzError($zyCollectionMod->getError());
			}else{
				$this->mzSuccess('收藏成功!');
			}
		}
	}

	//删除问题和笔记
	//$mid,$id,$type
	public function delresource(){
		$types = array(
		3 => 'ZyQuestion',
		4 => 'ZyNote',
		);

		$mid  = intval($_POST['mid']);
		$id   = intval($_POST['id']);
		$type = intval($_POST['type']);

		if(!$this->mid){
			$this->mzError('需要登录');
		}
		//获取表名
		$stable = $types[$type];
		if(!$stable){
			$this->mzError('资源错误!');
		}
		if($mid != $this->mid){
			$this->mzError('没有权限删除!');
		}
		//看看下面有没有回答--有就不能删除
		$count = M($stable)->where(array('parent_id'=>array('eq',$id)))->count();
		if($stable){
			if($type == 3){
				$this->mzError('该问题下面有回答,不能删除,请尝试修改！');
			}else{
				$this->mzError('该笔记下面有评论,不能删除,请尝试修改！');
			}
		}

		$i = M($stable)->where(array('id'=>array('eq',$id)))->delete();
		if($i === false){
			$this->mzError('删除失败!');
		}else{
			$url = ($type == 3)?U('classroom/Home/wenti'):U('classroom/Home/note');
			$this->mzSuccess('删除成功!',$url);
		}
	}

	//getAppConfig('cc_code', 'other', default);
	//获取验证码
	public function getcode(){
		//取一个session的值
		session('mzcodeforccvideo',genRandomString(6));
		//后台验证码
		$cc_code = getAppConfig('cc_code', 'other','ddasgagefeagegfeafefe');
		//课程ID
		$mzcur_lesson_id  = t($_POST['id']);
		//课程ccID
		$vid              = t($_POST['vid']);

		$data = array(
			'enable'   => 0,
			'vid'      => $vid,
			'uid'      => intval($this->mid),
			'sess'     => session('mzcodeforccvideo'),
			'sesionid' => session_id(),
			'lid'      => $mzcur_lesson_id,
		);

		//序列化数据
		$info = serialize($data);

		$str = $info.$cc_code;

		echo base64_encode($str);exit;
	}


	//cc回调的函数
	public function validate(){
		//后台验证码
		$cc_code = getAppConfig('cc_code', 'other','ddasgagefeagegfeafefe');

		$verificationcode = t($_POST['verificationcode']);
		$vid              = t($_POST['vid']);

		$verificationcode = base64_decode($verificationcode);

		//取信息
		$info             = str_replace($cc_code,'',$verificationcode);
		$info             = unserialize($info);

		//初始化session
		session('[destroy]');
		session(array('id'=>$info['sesionid']));
		//取得session里面的值
		$_session = session('mzcodeforccvideo');

		$enable  = intval($info['enable']);
		$message = '请购买后重试';

		//'mzBuyLabelShow'
		if($info['lid'] == 'mz'){
			//是否是从系列连载出来的视频
			$count = D('ZyLzContent')->where(array('video_id'=>array('eq',$info['vid'])))->count();
			if($count){
				$enable  = 1;
				$message = '可以直接播放';
				$this->myreturn($enable,$message,'');
			}
		}

		if($info['lid'] == 'houtai'){
			$enable  = 1;
			$message = '可以直接播放';
			$this->myreturn($enable,$message,'');
		}

		if(!$info['uid']){
			$enable  = 0;
			$message = '请登录之后再观看!';
			$this->myreturn($enable,$message,'');
		}

		if($vid !== $info['vid']){
			$enable  = 0;
			$message = 'CC课程ID不匹配!'.$info['lid'];
			$this->myreturn($enable,$message,'');
		}
		if($_session !== $info['sess']){
			$enable  = 0;
			$message = '课程播放信息错误!';
			$this->myreturn($enable,$message,'');
		}
		//判断是否有权限
		$isok = D('ZyService')->checkVideoAccess(intval($info['uid']),$info['lid']);
		if($isok){
			$enable  = 1;
			$message = '有权限,可以直接观看';
			$this->myreturn($enable,$message,'');
		}
		//判断是否是登录人创建的
		$_uid = M('ZyVideo')->where(array('id'=>array('eq',intval($info['lid']))))->getField('uid');
		if($_uid === intval($info['uid'])){
			$enable  = 1;
			$message = '你为创建者,可以直接观看!';
			$this->myreturn($enable,$message,'');
		}else{
			//判断是否为购买
			$isok = isBuyVideo(intval($info['uid']),$info['lid']);
			if($isok){
				$enable  = 1;
				$message = '已经购买,可以直接观看';
				$this->myreturn($enable,$message,'');
			}
		}
		$this->myreturn($enable,$message,'mzBuyLabelShow');
	}

	private function myreturn($enable,$message,$callback){
		$times = intval(getAppConfig('video_free_time'));
		$data['response'] = array(
			'version'  => "1",
			'enable'   => $enable,
			'freetime' => $times,
			'message'  => $message,
			"callback" => $callback
		);
		echo json_encode($data);exit;
	}
	//下载附件
	public function downVideoFile(){
		$vid  = intval($_GET['id']);
		$file = M('ZyVideo')->where(array('id'=>$vid))->getField('videofile_ids');
		$attachInfo = model('Attach')->getAttachById($file);

		$fileurl  = UPLOAD_URL . '/' . $attachInfo['save_path'].$attachInfo['save_name'];
		$filepath = UPLOAD_PATH . '/' . $attachInfo['save_path'].$attachInfo['save_name'];
		//开始下载附件
		downloadFile($fileurl,$filepath);
		exit;
	}
	//设置学习状态
	public function studyvideo(){
		$vid  = intval($_POST['vid']);
		$type = intval($_POST['type']);

		$i = M('ZyOrder')->setLearnStatus($this->mid,$vid,$type);
		if($i === false){
			$this->mzError('设置失败!');
		}else{
			$this->mzSuccess('设置成功!');
		}
	}


	/**
	 * 登录或注册
	 * @access public
	 * @author  Misszhou
	 */
	public function step1(){
		
		$status = model('Invite')->checkInviteCode($inviteCode, $this->_config['register_type']);
		if($status == 1) {
			$this->_invite = true;
			$this->_invite_code = $inviteCode;
		} else if($status == 2) {
			$this->assign('isAdmin',1);
			$this->error('抱歉，该邀请码已使用。');
		} else {
			$this->assign('isAdmin',1);
			$this->error($message);
		}
		$this->assign('isregok',0);
		$this->display();
	}
	/**
	 * 登录或注册-------------------有邀请码的
	 * @access public
	 * @author  Misszhou
	 */
	public function step2(){
		// 验证是否有钥匙 - 邀请注册问题
		if(empty($this->mid)) {
			if((isset($_GET['invite']) || $this->_config['register_type'] != 'open') && !in_array(ACTION_NAME, array('isEmailAvailable', 'isUnameAvailable', 'doStep1'))) {
				// 提示信息语言
				$messageHash = array('invite'=>'抱歉，本站目前仅支持邀请注册。', 'admin'=>'抱歉，本站目前仅支持管理员邀请注册。');
				$message = $messageHash[$this->_config['register_type']];
				if(!isset($_GET['invite'])) {
					$this->assign('isAdmin',1);
					$this->error($message);
				}
				$inviteCode = t($_GET['invite']);
				$status = model('Invite')->checkInviteCode($inviteCode, $this->_config['register_type']);
				if($status == 1) {
					$this->_invite = true;
					$this->_invite_code = $inviteCode;
				} else if($status == 2) {
					$this->assign('isAdmin',1);
					$this->error('抱歉，该邀请码已使用。');
				} else {
					$this->assign('isAdmin',1);
					$this->error($message);
				}
			}
		}
		// 若是邀请注册，获取邀请人相关信息
		if($this->_invite) {
			$inviteInfo = model('Invite')->getInviterInfoByCode($this->_invite_code);
			$this->assign('inviteInfo', $inviteInfo);
		}
		$this->assign('is_invite', $this->_invite);
		$this->assign('invite_code', $this->_invite_code);
		$this->assign('config', $this->_config);
		$this->assign('isregok',1);
		$this->display('step1');
	}






}