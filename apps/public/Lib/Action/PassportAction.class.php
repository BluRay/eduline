<?php
/**
 * PassportAction 通行证模块
 * @author  liuxiaoqing <liuxiaoqing@zhishisoft.com>
 * @version TS3.0
 */
tsload(APPS_PATH.'/classroom/Lib/Action/CommonAction.class.php');
class PassportAction extends CommonAction 
{

	private $passport;
	private $_user_model;
	private $_register_model;			// 注册模型字段
	private $_config;
/**
    * 初始化
    * @return void
    */
    public function _initialize() {
    	$this->passport = model('Passport');
    	$this->_user_model = model('User');
    	$this->_config = model('Xdata')->get('admin_Config:register');
    	$this->_register_model = model('Register');
        parent::_initialize();
    }

	/**
	 * 通行证首页
	 * @return void
	 */
	public function index() {
		// 如果设置了登录前的默认应用
		// U('welcome','',true);
		// 如果没设置
		$this->login();
	}
	
	/**
	 * 登录中转操作
	 */
	public function redirect(){
		
	}

	/**
	 * 默认登录页
	 * @return void
	 */
	public function login(){
		U('classroom/Index/index','',true); //dengjb 2014-5-8
	}
	
	/**
	 * 登录页
	 * @return void
	 */
	public function login_g(){
		// 添加样式
		$this->appCssList[] = 'login.css';
		if(model('Passport')->isLogged()){
			U('index/Index/index','',true);//dengjb 个人中心
		}

		// 获取邮箱后缀
		$registerConf = model('Xdata')->get('admin_Config:register');
		$this->assign('emailSuffix', explode(',', $registerConf['email_suffix']));
		$this->assign( 'register_type' , $registerConf['register_type']);
		$data= model('Xdata')->get("admin_Config:seo_login");
        !empty($data['title']) && $this->setTitle($data['title']);
        !empty($data['keywords']) && $this->setKeywords($data['keywords']);
        !empty($data['des'] ) && $this->setDescription ( $data ['des'] );
		
		$login_bg = getImageUrlByAttachId( $this->site ['login_bg'] );
		if(empty($login_bg))
			$login_bg = APP_PUBLIC_URL . '/image/body-bg2.jpg';
        
        $this->assign('login_bg', $login_bg);
        
		$this->display('login');
	}
	/**
	 * 快速登录
	 */
	public function quickLogin(){
		$registerConf = model('Xdata')->get('admin_Config:register');
		$this->assign( 'register_type' , $registerConf['register_type']);
		$this->display();
	}

	/**
	 * 用户登录
	 * @return void
	 */
	public function doLogin() {
		$login 		= addslashes($_POST['login_email']);
		$password 	= trim($_POST['login_password']);
		$remember	= intval($_POST['login_remember']);
		$result 	= $this->passport->loginLocal($login,$password,$remember);
		if(!$result){
			$status = 0; 
			$info	= $this->passport->getError();
			$data 	= 0;
		}else{
			$status = 1;
			$info 	= $this->passport->getSuccess();
			//$data 	= ($GLOBALS['ts']['site']['home_url'])?$GLOBALS['ts']['site']['home_url']:0;
			$data = U('index/Index/index');
		}

		$this->ajaxReturn($data,$info,$status);
	}	
	
	/**
	 * 注销登录
	 * @return void
	 */
	public function logout() {
		$this->passport->logoutLocal();
		$this->mzSuccess("退出成功！");
	}

	/**
	 * 找回密码页面
	 * @return void
	 */
	public function findPassword() {

		// 添加样式
		$this->appCssList[] = 'login.css';

		$this->display();
	}

	/**
	 * 通过安全问题找回密码
	 * @return void
	 */
	public function doFindPasswordByQuestions() {
		$this->display();
	}

	/**
	 * 通过Email找回密码
	 */
	public function doFindPasswordByEmail() {
		unset($_SESSION['setpwduser']);
		$_POST["email"]	= t($_POST["email"]);
		$verify=t($_POST["everify"]);
		if(!$this->_isEmailString($_POST['email'])) {
			$this->error(L('PUBLIC_EMAIL_TYPE_WRONG'));
		}
		$user =	model("User")->where('`email`="'.$_POST["email"].'"')->find();
        if(!$user) {
        	$this->mzError('找不到该邮箱注册信息!');
        } 
        /* if($user['mail_activate']==0){
        	$this->mzError('此邮箱未通过验证，无法使用！');
        } */
        //检查验证码
        if(md5(strtoupper($verify)) != $_SESSION['verify']) {
        	$this->mzError('验证码错误！');
        }
        $result = $this->_sendPasswordEmail($user);
		if($result){
			$_SESSION['setpwduser']=$user;//将找回密码的邮箱放入session
			$nowtime=time();
			$_SESSION['setpwdtime']=$nowtime+60;//找回密码限制时间
			$this->mzSuccess('发送成功，请注意查收邮件');
		}else{
			$this->mzError('操作失败，请重试');
		}
	}
	/**
	 * 找回密码邮件发送成功页面
	 */
	public function okemailadd(){
		$email=$_SESSION['setpwduser'];//取出邮箱
		
		if(empty($email)){
			$this->error("非法操作！");
		}
		$emaildata=explode("@",$email['email']);
		$emailhr=$emaildata[1];
		$time=$_SESSION['setpwdtime'];
		$this->assign("time",$time-time());
		$this->assign("email",$email['email']);
		$this->assign("emailhr",$emailhr);
		$this->display();
		
		
	}
	/**
	 * 重新发送邮件
	 */
	public function fasemail(){
		$email=$_SESSION['setpwduser'];
	
		$time=$_SESSION['setpwdtime'];
		$nowtime=time();
		if(empty($email)){
			$this->redirect($GLOBALS['ts']['site']['home_url']);
		}
		if($time>$nowtime){
			$this->mzError("请".$time-$nowtime."秒后重试！");
		}
		$result = $this->_sendPasswordEmail($email);
		if($result){
			$nowtime+=60;
			$_SESSION['setpwdtime']=$nowtime;
			$this->success("发送成功，请注意查收！");
		}else{
			$this->mzError('操作失败，请重试');
		}
	}

	/**
	 * 找回密码页面
	 */
	private function _sendPasswordEmail($user) {
		if($user['uid']) {
	    	$this->appCssList[] = 'login.css';		// 添加样式
	        $code = md5($user["uid"].'+'.$user["password"].'+'.rand(1111,9999));
	        $config['reseturl'] = U('public/Passport/resetPassword', array('code'=>$code));
	        //设置旧的code过期
	        D('FindPassword')->where('uid='.$user["uid"])->setField('is_used',1);
	        //添加新的修改密码code
	        $add['uid'] = $user['uid'];
	        $add['email'] = $user['email'];
	        $add['code'] = $code;
	        $add['is_used'] = 0;
	        $result = D('FindPassword')->add($add);
	        if($result){
	    		model('Notify')->sendNotify($user['uid'], 'password_reset', $config);
				return true;
			}else{
				return false;
			}
	    }
	}

	public function doFindPasswordByEmailAgain(){
		$_POST["email"]	= t($_POST["email"]);
		$user =	model("User")->where('`email`="'.$_POST["email"].'"')->find();		
        if(!$user) {
        	$this->error('找不到该邮箱注册信息');
        } 

        $result = $this->_sendPasswordEmail($user);
		if($result){
			$this->success('发送成功，请注意查收邮件');
		}else{
			$this->error('操作失败，请重试');
		}
	}

	/**
	 * 通过手机短信找回密码
	 * @return void
	 */
	public function doFindPasswordBySMS() {
		$this->display();
	}

	/**
	 * 重置密码页面
	 * @return void
	 */
	public function resetPassword() {
		$code = t($_GET['code']);
		$this->_checkResetPasswordCode($code);
		$this->assign('code', $code);
		$this->display();
	}

	/**
	 * 执行重置密码操作
	 * @return void
	 */
	public function doResetPassword() {
		$code = t($_POST['code']);
		$user_info = $this->_checkResetPasswordCode($code);

		$password = trim($_POST['password']);
		$repassword = trim($_POST['repassword']);
		
		if(!model('Register')->isValidPassword($password, $repassword)){
			$this->mzError(model('Register')->getLastError());
		}
		/* echo $repassword.'<br>'.$password;
		die(); */
		$map['uid'] = $user_info['uid'];
		$data['login_salt'] = rand(10000,99999);
		$data['password']   = md5(md5($password) . $data['login_salt']);
		$res = model('User')->where($map)->save($data);
		if ($res) {
			D('find_password')->where('uid='.$user_info['uid'])->setField('is_used',1);
			model('User')->cleanCache($user_info['uid']);
			$this->assign('jumpUrl', U('public/Passport/login'));
			//邮件中会包含明文密码，很不安全，改为密文的
			$config['newpass'] = $this->_markPassword($password); //密码加星号处理
			model('Notify')->sendNotify($user_info['uid'],'password_setok',$config);
			$_SESSION['setpwduser'];
			$this->mzSuccess(L('PUBLIC_PASSWORD_RESET_SUCCESS'));
		} else {
			$this->mzError(L('PUBLIC_PASSWORD_RESET_FAIL'));
		}
	}

	/**
	 * 检查重置密码的验证码操作
	 * @return void
	 */
	private function _checkResetPasswordCode($code) {
		$map['code'] = $code;
		$map['is_used'] = 0;
		$uid = D('find_password')->where($map)->getField('uid');
		if(!$uid){
			$this->assign('jumpUrl',U('public/Passport/findPassword'));
			$this->error('重置密码链接已失效，请重新找回');
		}
		$user_info = model('User')->where("`uid`={$uid}")->find();

		if (!$user_info) {
			$this->redirect = U('public/Passport/login');
		}

		return $user_info;
	}

	/*
	 * 验证安全邮箱
	 * @return void
	 */
	public function doCheckEmail() {
		$email = t($_POST['email']);
		if($this->_isEmailString($email)){
			die(1);			
		}else{
			die(0);
		}
	}

	/*
	 * 正则匹配，验证邮箱格式
	 * @return integer 1=成功 ""=失败
	 */
	private function _isEmailString($email) {
		return preg_match("/[_a-zA-Z\d\-\.]+@[_a-zA-Z\d\-]+(\.[_a-zA-Z\d\-]+)+$/i", $email) !== 0;
	}

	/*
	 * 替换密码为星号
	 * @return integer 1=成功 ""=失败
	 */
	private function _markPassword($str){
	    $c = strlen($str)/2;
	    return preg_replace('|(?<=.{'.(ceil($c/2)).'})(.{'.floor($c).'}).*?|',str_pad('',floor($c),'*'),$str,1);
	}

    /**
     * 登录或注册页面
     */
    public function regLogin(){
        $data = $this->fetch("reg_login");
        exit( json_encode($data) );
    }
    /**
     * 验证邮箱是否唯一
     * 异步方法
     */
    public function clickEmail(){
        $email=t($_POST['email']);
        $res=M('user')->where(array('login'=>$email))->find();
        if($res){
                echo 0;
                exit;
        }else{

                echo 1;
                exit;
        }
    }
    /**
     * 验证验证码是否正确
     * 异步方法
     */
    public function clickVerify(){
    	$verify=t($_POST['verify']);
    	if (md5(strtoupper($verify)) != $_SESSION['verify']) {
    		echo 0;
    		exit;
    	}else{
    		echo 1;
    		exit;
    	}
    }
    /**
     *验证用户名是否唯一
     * 异步方法
     */
    public function clickUname(){
    	$uname=t($_POST['uname']);
    	$res=$this->_register_model->isValidName($uname);
    	if ($res) {
    		echo 1;
    		exit;
    	}else{
    		echo 0;
    		exit;
    	}
    }
    /**
     *验证手机是否唯一
     * 异步方法
     */
    public function clickPhone(){
    	$phone=$_POST['phone'];
    	
    	$res=M('user')->where(array('phone'=>$phone))->find();
    	if ($res) {
    		echo 0;
    		exit;
    	}else{
    		echo 1;
    		exit;
    	}
    }
    /**
     * 获取验证码
     */
    public function getVerify(){
    	$verifytime=$_SESSION['verifytime'];
    	$nowtime=time();
    	if($nowtime<$verifytime){
    		$xctime=$verifytime-$nowtime;
    		$this->mzError("请".$xctime."秒后重新获取！");
    		exit();
    	}
    	$phone=$_POST['phone'];
    	$res=M('user')->where(array('phone'=>$phone))->find();
    	if ($res) {
    		$this->mzError('此手机号已被注册,请更换！');
    	}
    	$rnum=rand(1000,9999);
        $cont="您本次获取的验证码为".$rnum."请在页面指定处填写，请勿随意告知其他任何人！如非本人操作，请忽略此信息！";
    	$sendres=model('Sms')->send($phone,$cont);
    	if($sendres){
    		//将验证码存入session
    		$_SESSION['phoneverify']=$rnum;
    		//将号码存入session
    		$_SESSION['getverphone']=$phone;
    		$nowtime+=60;
    		$_SESSION['verifytime']=$nowtime;
    		$this->mzSuccess("发送成功，请注意查收！");
    	}else{
    		$this->mzError(model('Sms')->getError());
    	}
    	
    }
    //手机注册下一步
    public function clickPhoneVer(){
    	$phone=$_POST['phone'];
    	$verify=intval($_POST['verify']);
    	$verphone=$_SESSION['getverphone'];//取得获取验证码的手机
    	$verifys=$_SESSION['phoneverify'];//取得验证码
    	if($phone!=$verphone ||$verify!=$verifys){
    		$this->mzError("对不起，验证码错误，请重试！");
    	}else{
    		$this->mzSuccess();
    	}
    }
    /**
     * 异步注册
     */
    public function ajaxReg(){
    	$phone=$_POST['phone'];
		$email = t($_POST['email']);
		$uname = t($_POST['uname']);
		$sex = 1 == $_POST['sex'] ? 1 : 2;
		$password = trim($_POST['password']);
		$profession=t($_POST['profession']);
		$intro=t($_POST['intro']);
		$type= 1 == $_POST['type'] ? 1 : 2;
		/* $repassword = trim($_POST['repassword']); */
		if(!$this->_register_model->isValidName($uname)) {
			$this->mzError($this->_register_model->getLastError());
		}
        if($type==1){
        	if(!$this->_register_model->isValidEmail($email)) {
        		$this->mzError($this->_register_model->getLastError());
        	}
        	//检查验证码
        	if(md5(strtoupper($_POST['verify'])) != $_SESSION['verify']) {
        		$this->mzError('验证码错误！');
        	}
        	$map['login']=$email;
        	$map['email']=$email;
        }else{
        	if(!preg_match("/^[1][358]\d{9}$/",$phone)) {
        		$this->mzError("手机号格式错误！");
        	}
        	if($phone!=$_SESSION['getverphone'] ||$_POST['verify']!=$_SESSION['phoneverify']){
        		$this->mzError("对不起，验证码错误，请重试！");
        	}
        	
        	$map['phone']=$phone;
        	$map['login']=$phone;
        }
		

		if($password=="" ||strlen($password)<6 || strlen($password)>20){
			$this->mzError("对不起，密码长度不正确");
		}
		$login_salt = rand(11111, 99999);
		$map['uname'] = $uname;
		$map['sex'] = $sex;
		$map['profession']=$profession;
		$map['intro']=$intro;
		$map['login_salt'] = $login_salt;
		$map['password'] = md5(md5($password).$login_salt);
		$map['reg_ip'] = get_client_ip();
		$map['ctime'] = time();
		// 添加地区信息
		$map['location'] = t($_POST['city_names']);
		$cityIds = t($_POST['city_ids']);
		$cityIds = explode(',', $cityIds);
		isset($cityIds[0]) && $map['province'] = intval($cityIds[0]);
		isset($cityIds[1]) && $map['city'] = intval($cityIds[1]);
		isset($cityIds[2]) && $map['area'] = intval($cityIds[2]);
		// 审核状态： 0-需要审核；1-通过审核
		$map['is_audit'] = $this->_config['register_audit'] ? 0 : 1;
		// 需求添加 - 若后台没有填写邮件配置，将直接过滤掉激活操作
		$isActive = $this->_config['need_active'] ? 0 : 1;
		if ($isActive == 0) {
			$emailConf = model('Xdata')->get('admin_Config:email');
			if (empty($emailConf['email_host']) || empty($emailConf['email_account']) || empty($emailConf['email_password'])) {
				$isActive = 1;
			}
		}
		$map['is_active'] = $isActive;
		$map['first_letter'] = getFirstLetter($uname);
		//如果包含中文将中文翻译成拼音
		if ( preg_match('/[\x7f-\xff]+/', $map['uname'] ) ){
			//昵称和呢称拼音保存到搜索字段
			$map['search_key'] = $map['uname'].' '.model('PinYin')->Pinyin( $map['uname'] );
		} else {
			$map['search_key'] = $map['uname'];
		}
		$uid = $this->_user_model->add($map);
		if($uid) {
			// 添加积分
			model('Credit')->setUserCredit($uid,'init_default');
			// 添加至默认的用户组
			$userGroup = model('Xdata')->get('admin_Config:register');
			$userGroup = empty($userGroup['default_user_group']) ? C('DEFAULT_GROUP_ID') : $userGroup['default_user_group'];
			model('UserGroupLink')->domoveUsergroup($uid, implode(',', $userGroup));
            $data['oauth_token']         = getOAuthToken($uid);//添加app认证
            $data['oauth_token_secret']  = getOAuthTokenSecret();
            $data['uid']                 = $uid;
            $savedata = $data;
            D('')->table(C('DB_PREFIX').'ZyLoginsync')->add($savedata);
			//判断是否需要审核
				
			        if($type==2){
						$email=$phone;
					}

					D('Passport')->loginLocal($email,$password);
					
					$this->mzSuccess('恭喜您，注册成功');


		} else {
			$this->mzError(L('PUBLIC_REGISTER_FAIL'));			// 注册失败
		}
    }
    /**
     * 回调用户头像设置
     */
    public function setUserFace(){
    	$data=model("User")->getUserInfo($this->mid);
    	$this->assign("data",$data);
    	$this->display();
    }

    /**
     * 异步登录
     */
    public function ajaxLogin(){
        $login 		= addslashes($_POST['log_username']);
        $password 	= trim($_POST['log_pwd']);
        $remember	= intval($_POST['login_remember']);
        $result 	= $this->passport->loginLocal($login,$password,$remember);
        if(!$result){
          $this->mzError($this->passport->getError());
           exit;
        }else{
         $this->mzSuccess("恭喜，登录成功！");
         exit;
        }

    }
}