<?php
/**
 * 登录注册控制器
 * @author ashangmanage <ashangmanage@phpzsm.com>
 * @version CY1.0
 */
session_start();
class RegisterAction extends Action
{
	
 
	private $_register_model;			// 注册模型字段
	private $_user_model;				// 用户模型字段
 
	
	/**
	 * 模块初始化
	 * @return void
	 */
	protected function _initialize()
	{
		$this->_user_model = model('User');
		$this->_register_model = model('Register');
	}
 


	/**
	 * 注册流程 - 执行第一步骤
	 * @return void
	 */
	public function doStep1(){	
		$invite = t($_POST['invate']);
		$inviteCode = t($_POST['invate_key']);
		$phone = t($_POST['mobile']);
		$reg_captcha = $_POST['code'];//验证码
		$email = t($_POST['email']); //邮箱
		$password = trim($_POST['reg_pwd']);
		$repassword = trim($_POST['reg_confirmPwd']);
		
		//检查验证码 $_SESSION['code']
		if ((intval($reg_captcha) != $_SESSION['reg_code']) || ($phone != $_SESSION['mobi']) ) {
			echo 6;
		}else if($password != $repassword ) {
			echo 1;
		}else{
			$login_salt = rand(11111, 99999);
			$map['uname'] = $email;
			$map['sex'] = 1;
			$map['login_salt'] = $login_salt;
			$map['password'] = md5(md5($password).$login_salt);
			$map['login'] = $map['email'] = $email;
			$map['phone'] = $phone;
			$map['reg_ip'] = get_client_ip();
			$map['ctime'] = time();
			$map['is_active'] = 1;
			$map['is_audit'] = 1;
			$map['is_init'] = 1;
			//判断是否有相同的手机号和邮箱地址
			$res_phone = M('user')->where("phone='".$phone."'")->count();
			$res_email = M('user')->where("login='".$email."'")->count();
			if($res_phone > 0){
				echo 3;
			}else if($res_email >0 ){
				echo 4;
			}else{
				$uid = M('user')->add($map);
				if($uid) {
					
					//添加学币记录
					$data['uid'] = $uid;
					M('zy_learncoin')->add($data);
					// 添加学分
					model('Credit')->setUserCredit($uid,'init_default');
					
					// 如果是邀请注册，则邀请码失效
					if($invite) {
						$receiverInfo = model('User')->getUserInfo($uid);
						// 验证码使用
						model('Invite')->setInviteCodeUsed($inviteCode, $receiverInfo);
						// 添加用户邀请码字段
						model('User')->where('uid='.$uid)->setField('invite_code', $inviteCode);
						//给邀请人奖励
					}
		
					// 添加至默认的用户组
					$userGroup = model('Xdata')->get('admin_Config:register');
					$userGroup = empty($userGroup['default_user_group']) ? C('DEFAULT_GROUP_ID') : $userGroup['default_user_group'];
					model('UserGroupLink')->domoveUsergroup($uid, implode(',', $userGroup));
					
		
					 
					//注册成功
					$res = model('Passport')->loginLocal($phone,$password);
					$_SESSION['login_status'] = $res;
					unset($_SESSION['reg_code']);
					unset($_SESSION['mobi']);
					echo 5;
					
		
				} else {
					echo 2;			// 注册失败
				}
			}
		}
	}

	/**
	 * 注册第二步
	 */
	function reg(){
		 
		if($_SESSION['login_status']){
			$this->display();
		}
		else{
			redirect(U('index/Index/index'));
		}
		 
		
	}
	
	/**
	 * 选择类型
	 */
	function dostep2(){
		empty($_SESSION['mid']) && $this->redirect('index/Index/index');
		$my_study_level = $_POST['xueshen'];
		$uid = $_SESSION['mid'];
		$data['my_study_level'] = $my_study_level;
		M('user')->where('uid='.$uid)->save($data);
		
	}
	
	/**
	 * 选择阶段
	 */
	
	function dostep3(){
		empty($_SESSION['mid']) && $this->redirect('index/Index/index');
		$study_phase = $_POST['jieduan'];
		$uid = $_SESSION['mid'];
		$data['study_phase'] = $study_phase;
		M('user')->where('uid='.$uid)->save($data);
		//推荐相同阶段人
		$tj_res = M('user')->where('study_phase='.$study_phase.' and uid !='.$uid)->field('uid')->limit(5)->select();
		$html = '<div class="clearfix mt20" id="guanzhu">';
		if(!empty($tj_res)){
		foreach ($tj_res as $k=>$v){
			$id = $v['uid'];
			$face = getUserFace($id, 'm');
//			$html .= '<div class="fl">';
//			$html .= '<div>';
//			$html .= '<img src="'.$face.'" class="bg_img_f"/>';
//			$html .= '<input type="checkbox" name="selected" value="'.$id.'" id="box">';
//			$html .= '</div>';
//			$html .= '<p class="txt_c">'.getUserName($id).'</p>';
//			$html .= '</div>';
			$html .= '<div class="fl" style="position:relative;text-align:center">';
			$html .= '<div>';
			$html .= '<img style="float:left;margin-bottom:20px;padding:1px;border:1px solid #ccc;" src="'.$face.'" class="bg_img_f"/>';
			$html .= '<input style="float: left;position: absolute;bottom: 20px;left: 110px;" type="checkbox" name="selected" value="'.$id.'" id="box">';
			$html .= '</div>';
			$html .= '<p class="txt_c" style="position:absolute;bottom:0;width:100%;">'.getUserName($id).'</p>';
			$html .= '</div>';
			
		}
		}
		else{
			$html .= '<div>暂无数据...<input type="hidden" id="box"/></div>';
		}
		
		$html .= '</div>';
		
		//推荐相同吐槽的人
		$my_study_level = M('user')->where('uid ='.$uid)->getField('my_study_level');
		$tj_tc = M('user')->where('my_study_level='.$my_study_level.' and uid !='.$uid)->field('uid')->limit(5)->select();
		$html1 = '<div class="clearfix mt20" id="tu">';
		if(!empty($tj_tc)){
		foreach ($tj_tc as $k=>$v){
			$id = $v['uid'];
			$face = getUserFace($id, 'm');
			$html .= '<div class="fl" style="position:relative;text-align:center">';
			$html .= '<div>';
			$html .= '<img style="float:left;margin-bottom:20px;padding:1px;border:1px solid #ccc;" src="'.$face.'" class="bg_img_f"/>';
			$html .= '<input style="float: left;position: absolute;bottom: 20px;left: 110px;" type="checkbox" name="selected" value="'.$id.'" id="box">';
			$html .= '</div>';
			$html .= '<p class="txt_c" style="position:absolute;bottom:0;width:100%;">'.getUserName($id).'</p>';
			$html .= '</div>';
			
		}
		}
		else{
			$html1 .= '<div>暂无数据...<input type="hidden" id="box"/></div>';
		}
		
		$html1 .= '</div>';
		echo $html.','.$html1;
		
	}
	
	/**
	 * 关注
	 */
	function guanzhu(){
		if(empty($_SESSION['mid'])){
			echo 1;
		}else{
		$guanzhu = $_POST['guanzhu'];
		$arr = explode(" ",$guanzhu);
		//执行关注
		$n = count($arr);
		for($i=0;$i<$n;$i++){
			model('Follow')->doFollow($_SESSION['mid'],$arr[$i]);
		}
		echo 2;
		}
		
	}
	/**
	 * 换一换相同阶段
	 */
	function getmore(){
		//推荐相同阶段人
		$uid = $_SESSION['mid'];
		$study_phase = M('user')->where('uid='.$uid)->getField('study_phase');
		$tj_res = M('user')->where('study_phase='.$study_phase.' and uid !='.$uid)->field('uid')->select();
		$new_arr = array();
		foreach ($tj_res as $k=>$v){
			$new_arr[$k] = $v['uid'];
		}
		$html = '<div class="clearfix mt20" id="guanzhu">';
		shuffle($new_arr); 		
		$new_arr = array_slice($new_arr,0,5);
		$n = count($new_arr);
		if(!empty($new_arr)){
		for ($i=0;$i<$n;$i++){
			 
			$face = getUserFace($new_arr[$i], 'm');
			$html .= '<div class="fl" style="position:relative;text-align:center">';
			$html .= '<div>';
			$html .= '<img style="float:left;margin-bottom:20px;padding:1px;border:1px solid #ccc;" src="'.$face.'" class="bg_img_f"/>';
			$html .= '<input style="float: left;position: absolute;bottom: 20px;left: 110px;" type="checkbox" name="selected" value="'.$new_arr[$i].'" id="box">';
			$html .= '</div>';
			$html .= '<p class="txt_c" style="position:absolute;bottom:0;width:100%;">'.getUserName($new_arr[$i]).'</p>';
			$html .= '</div>';
			
		}
		}
		else{
			$html .= '<div>暂无数据...<input type="hidden" id="box"/></div>';
		}
		
		$html .= '</div>';
		echo $html;
	}
	
	
	/**
	 * 换一换相同吐槽
	 */
	function gettu(){
		//推荐相同吐槽人
		$uid = $_SESSION['mid'];
		$my_study_level = M('user')->where('uid='.$uid)->getField('my_study_level');
		$tj_res = M('user')->where('my_study_level='.$my_study_level.' and uid !='.$uid)->field('uid')->select();
		$new_arr = array();
		foreach ($tj_res as $k=>$v){
			$new_arr[$k] = $v['uid'];
		}
		$html = '<div class="clearfix mt20" id="tu">';
		shuffle($new_arr); 		
		$new_arr = array_slice($new_arr,0,5);
		$n = count($new_arr);
		if(!empty($new_arr)){
		for ($i=0;$i<$n;$i++){
			 
			$face = getUserFace($new_arr[$i], 'm');
			$html .= '<div class="fl" style="position:relative;text-align:center">';
			$html .= '<div>';
			$html .= '<img style="float:left;margin-bottom:20px;padding:1px;border:1px solid #ccc;" src="'.$face.'" class="bg_img_f"/>';
			$html .= '<input style="float: left;position: absolute;bottom: 20px;left: 110px;" type="checkbox" name="selected" value="'.$new_arr[$i].'" id="box">';
			$html .= '</div>';
			$html .= '<p class="txt_c" style="position:absolute;bottom:0;width:100%;">'.getUserName($new_arr[$i]).'</p>';
			$html .= '</div>';
			
		}
		}
		else{
			$html .= '<div>暂无数据...<input type="hidden" id="box"/></div>';
		}
		
		$html .= '</div>';
		echo $html;
	}
	
	
	/**
	 * 等待审核页面
	 * @return void
	 */
	public function waitForAudit() {
		$user_info = $this->_user_model->where("uid={$this->uid}")->find();
		$email	=	model('Xdata')->getConfig('sys_email','site');
		if (!$user_info || $user_info['is_audit']) {
			$this->redirect('public/Passport/login');
		}
		$touid = D('user_group_link')->where('user_group_id=1')->field('uid')->findAll();
		foreach($touid as $k=>$v){
			model('Notify')->sendNotify($v['uid'], 'register_audit');
		}
		$this->assign('email',$email);
		$this->setTitle('帐号等待审核');
		$this->setKeywords('帐号等待审核');
		$this->display();
	}

	/**
	 * 等待激活页面
	 */
	public function waitForActivation() {
		$this->appCssList[] = 'login.css';
		$user_info = $this->_user_model->where("uid={$this->uid}")->find();
		// 判断用户信息是否存在
		if($user_info) {
			if($user_info['is_audit'] == '0') {
				// 审核
				exit(U('public/Register/waitForAudit', array('uid'=>$this->uid), true));
			} else if($user_info['is_active'] == '1') {
				// 激活
				exit(U('public/Register/step2',array(),true));				
			}
		} else {
			// 注册
			$this->redirect('public/Passport/login');
		}

		$email_site = 'http://mail.'.preg_replace('/[^@]+@/', '', $user_info['email']);

		$this->assign('email_site', $email_site);
		$this->assign('email', $user_info['email']);
		$this->assign('config', $this->_config);
		$this->setTitle('等待激活帐号');
		$this->setKeywords('等待激活帐号');
		$this->display();
	}

	/**
	 * 发送激活邮件
	 * @return void
	 */
	public function resendActivationEmail() {
		$res = $this->_register_model->sendActivationEmail($this->uid);
		$this->ajaxReturn(null, $this->_register_model->getLastError(), $res);
	}

	/**
	 * 修改激活邮箱
	 */
	public function changeActivationEmail() {
		$email = t($_POST['email']);
		// 验证邮箱是否为空
		if (!$email) {
			$this->ajaxReturn(null, '邮箱不能为空！', 0);
		}
		// 验证邮箱格式
		$checkEmail = $this->_register_model->isValidEmail($email);
		if (!$checkEmail) {
			$this->ajaxReturn(null, $this->_register_model->getLastError(), 0);
		}
		$res = $this->_register_model->changeRegisterEmail($this->uid, $email);
		$res && $this->_register_model->sendActivationEmail($this->uid);
		$this->ajaxReturn(null, $this->_register_model->getLastError(), $res);
	}

	/**
	 * 通过链接激活帐号
	 * @return void
	 */
	public function activate() {
		$user_info = $this->_user_model->getUserInfo($this->uid);

		$this->assign('user',$user_info);
		
		if (!$user_info || $user_info['is_active']) {
			$this->redirect('public/Passport/login');
		}

		$active = $this->_register_model->activate($this->uid, t($_GET['code']));

		if ($active) {
			// 登录
			model('Passport')->loginLocalWithoutPassword($user_info['email']);
			$this->setTitle('成功激活帐号');
			$this->setKeywords('成功激活帐号');
			// 跳转下一步
			$this->assign('jumpUrl', U('public/Register/step2'));
			$this->success($this->_register_model->getLastError());
		} else {
			$this->redirect('public/Passport/login');
			$this->error($this->_register_model->getLastError());
		}
	}

	/**
	 * 第二步注册
	 * @return void
	 */
	public function step2() {
		// 未登录
		empty($_SESSION['mid']) && $this->redirect('public/Passport/login');
		$user = $this->_user_model->getUserInfo($this->mid);
		$this->assign('user_info', $user);
		//如果已经同步过头像,不需要强制执行这一步
		if(model('Avatar')->hasAvatar()){
			$this->assign('need_photo',0);
		}else{
			$this->assign('need_photo',$this->_config['need_photo']);
		}
		$this->assign('tag_open',$this->_config['tag_open']);
		$this->assign('interester_open',$this->_config['interester_open']);
		$this->setTitle('上传站内头像');
		$this->setKeywords('上传站内头像');
		$this->display();
	}

	/**
	 * 注册流程 - 第三步骤
	 * 设置个人标签
	 */
	public function step3() {
		// 未登录
		empty($_SESSION['mid']) && $this->redirect('public/Passport/login');
		$this->appCssList[] = 'login.css';
		//$this->_config['tag_num'] = $this->_config['tag_num']?$this->_config['tag_num']:10;
		$this->assign('tag_num',$this->_config['tag_num']);
		$this->assign('interester_open',$this->_config['interester_open']);
		$this->setTitle('设置个人标签');
		$this->setKeywords('设置个人标签');
		$this->display();
	}

	

	/**
	 * 注册流程 - 第四步骤
	 */
	public function step4() {
		// 未登录
		empty($_SESSION['mid']) && $this->redirect('public/Passport/login');
		$this->appCssList[] = 'login.css';

		//dump($this->_config);exit;
		//按推荐用户
		$related_recommend_user = model('RelatedUser')->getRelatedUserByType(5,8);
		$this->assign('related_recommend_user',$related_recommend_user);
		//按标签
		if(in_array('tag', $this->_config['interester_rule'])){
			$related_tag_user = model('RelatedUser')->getRelatedUserByType(4,8);
			$this->assign('related_tag_user',$related_tag_user);
		}
		//按地区
		if(in_array('area', $this->_config['interester_rule'])){
			$related_city_user = model('RelatedUser')->getRelatedUserByType(3,8);
			$this->assign('related_city_user',$related_city_user);
		}
		$userInfo = model('User')->getUserInfo($this->mid);
		$location = explode(' ', $userInfo['location']);
		$this->assign('location',$location[0]);
		$this->setTitle('关注感兴趣的人');
		$this->setKeywords('关注感兴趣的人');
		$this->display();
	}

	/**
	 * 获取推荐用户
	 * @return void
	 */
	public function getRelatedUser() {
		$type = intval($_POST['type']);
		$related_user = model('RelatedUser')->getRelatedUserByType($type,8);
		$html = '';
		foreach($related_user as $k=>$v){
			$html .= '<li><div style="position:relative;width:80px;height:80px"><div class="selected"><i class="ico-ok-mark"></i></div>
					  <a event-node="bulkDoFollowData" value="'.$v['userInfo']['uid'].'" class="face_part" href="javascript:void(0);">
					  <img src="'.$v['userInfo']['avatar_big'].'" /></a></div><span class="name">'.$v['userInfo']['uname'].'</span></li>';
		}
		echo $html;
	}

	/**
	 * 注册流程 - 执行第四步骤
	 */
	public function doStep4() {
		set_time_limit(0);
		// 初始化完成
		$this->_register_model->overUserInit($this->mid);
		// 添加双向关注用户
		$eachFollow = $this->_config['each_follow'];
		if(!empty($eachFollow)) {
			model('Follow')->eachDoFollow($this->mid, $eachFollow);
		}
		// 添加默认关注用户
		$defaultFollow = $this->_config['default_follow'];
		$defaultFollow = array_diff(explode(',', $defaultFollow), explode(',', $eachFollow));
		if(!empty($defaultFollow)) {
			model('Follow')->bulkDoFollow($this->mid, $defaultFollow);
		}
		redirect($GLOBALS['ts']['site']['home_url']);
		//$this->redirect($GLOBALS['ts']['site']['home_url_str']);
	}

	/**
	 * 验证邮箱是否已被使用
	 */
	public function isEmailAvailable() {
		$email = t($_POST['email']);
		$result = $this->_register_model->isValidEmail($email);
		$this->ajaxReturn(null, $this->_register_model->getLastError(), $result);
	}


	/**
	 * 添加用户关注信息
	 */
	public function bulkDoFollow() {
		$res = model('Follow')->bulkDoFollow($this->mid, t($_POST['fids']));
    	$this->ajaxReturn($res, model('Follow')->getError(), false !== $res);
	}
	
	/**
	 * 发送获取注册验证码
	 */
	public function sendcode(){
		$phone = t($_POST['mobile']);
		$code = yan_code(6);
		$code = implode("",$code);
		$con = iconv( "UTF-8", "gb2312//IGNORE" ,'Hi，欢迎注册高教网，您申请的短信校验码为:'.$code);
		$name = iconv( "UTF-8", "gb2312//IGNORE" ,',请在页面指定处填写。如非本人操作，请勿理会！【高教网】 ');
		$res_phone = M('User')->where("`phone`='".trim($phone)."'")->count();
		if($res_phone > 0){
			echo 'a';
		}else{
			$res = $this->sendsms($phone,$con.' '.$name);
			if($res > 0){
				$_SESSION['reg_code'] = $code;
				$_SESSION['mobi'] = $phone;
			}
			
			echo $res;
		}
		
	}

	public function sendsms($moblie,$content){
   		$sn =  C('SN'); //提供的帐号
   		$pw =  C('PWD'); //密码
   		$pwd= strtoupper(md5($sn.$pw));
  		$data = array(
      	'sn' => $sn, //提供的帐号
      	'pwd' =>$pwd, //此处密码需要加密 加密方式为 md5(sn+password) 32位大写
      	'mobile' => $moblie, //手机号 多个用英文的逗号隔开 post理论没有长度限制.推荐群发一次小于等于10000个手机号
      	'content' => $content, //短信内容
      	'ext' => '',
      	'stime' => '', //定时时间 格式为2011-6-29 11:09:21
      	'rrid' => '' //默认空 如果空返回系统生成的标识串 如果传值保证值唯一 成功则返回传入的值 
          );

     	$url = "http://117.79.237.29/webservice.asmx/mt?";

 
 
     $retult= $this->api_notice_increment($url,$data);
	 
     $retult=str_replace("<?xml version=\"1.0\" encoding=\"utf-8\"?>","",$retult);
     $retult=str_replace("<string xmlns=\"http://tempuri.org/\">","",$retult);
	 $retult=str_replace("</string>","",$retult);
	 
	 return $retult;
     /**if($retult>0)
			echo '发送成功返回值为:'.$retult;
			else
			echo '发送失败 返回值为:'.$retult;
			*/
	}

  	public function api_notice_increment($url, $data){
     
    	$curl = curl_init(); // 启动一个CURL会话
    	curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址
    	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
    	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0); // 从证书中检查SSL加密算法是否存在
    	curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
    	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
    	curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
    	curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
    	$data = http_build_query($data);
    	curl_setopt($curl, CURLOPT_POSTFIELDS, $data); // Post提交的数据包
    	curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
    	curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
    	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
     
     
     	$lst = curl_exec($curl);
      	if (curl_errno($curl)) {
       	echo 'Errno'.curl_error($curl);//捕抓异常
      	}
    	 curl_close($curl);
     	return $lst;
 	}

	 
}