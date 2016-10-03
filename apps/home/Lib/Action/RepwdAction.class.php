<?php
tsload(APPS_PATH.'/classroom/Lib/Action/CommonAction.class.php');
	/**
	 * 找回密码控制器
	 */
	class RepwdAction extends CommonAction{
		public function index(){
            $phoneCode = $_SESSION['repwdtime']; //保存在session中的时间

            if(!empty($phoneCode)){
                $noewtime=time();
                if($phoneCode>$noewtime){
                    $chatime=$phoneCode-$noewtime;
                    $this->assign("time",$chatime);
                }

            }
          /*  echo $chatime;
            die();*/
			$this->display();
		}
		/**
		 * 忘记密码   手机获取验证码 
		 */
		public function getVrifi(){
			$time = time();
			$phoneCode = session('phone_code'); //保存在session中的验证码
			$phone = $_POST['phone'];
			empty($phone) ? $this->mzError('手机号不能为空') : $phone;
			if(!preg_match('/^1[3458]\d{9}$/', $phone)){
				$this->mzError('请输入正确的手机号码');
			}
			if($_SESSION['repwdtime'] > $time) $this->mzError('请勿频繁获取手机验证码');
			//根据用户电话查询用户信息
			$user = model('User')->where(array('phone'=>$phone))->find();
			if(is_null($user) || empty($user)) $this->mzError('手机未绑定');
			$phoneCode[$phone]['send_time'] = $time + 90;
            $_SESSION['repwdtime']=$phoneCode[$phone]['send_time'];
			$code = rand(100000,999999);//手机验证码
			$phoneCode[$phone]['code'] = md5($code);
			$txt = '您本次获取的验证码为'.$code.'请在页面指定处填写，请勿随意告知其他任何人！如非本人操作，请忽略此信息！';
			if(model('Sms')->send($phone,$txt)){
				session('phone_code',$phoneCode); //相关信息保存session  时间的控制
				$this->success('短信发送成功，请注意查收！');
			}else{
				$this->mzError(model('Sms')->getError());
			}
		}
		/**
		 * 检查验证码是否正确
		 *   
		 */
		public function repwdhandle(){
			$phone = $_POST['phone'];
			$pwd = trim($_POST['pwd']);
			$repwd = trim($_POST['repwd']);
			$code = md5($_POST['code']);
			$phone = empty($phone) ? $this->mzError('手机号不能为空') : $phone;
			$phoneCodes = session('phone_code');
			//常规检查用户信息
			if(!empty($phone)){
				if(!preg_match('/^1[3458]\d{9}$/', $phone)){
					$this->mzError('异常操作');
				}
			}
			//检查用户信息
			if(!isset($phoneCodes[$phone]) || empty($phoneCodes[$phone])){
				$this->mzError('请先获取验证码');
			}
			$phoneCode = $phoneCodes[$phone];
			if($code !== $phoneCode['code']){
				$phoneCode['err'] += 1;
				if($phoneCode['err'] >= 4){
					$phoneCodes[$phone] = null;
					session('phone_code',$phoneCodes);
					$this->mzError('请重新获取短信验证码');
				}else{
					$phoneCodes[$phone] = $phoneCode;
					session('phone_code', $phoneCodes);
					$this->mzError('验证码错误，你还可以尝试'.(4-$phoneCode['err']).'次');
				}
			}
			if(!model('Register')->isValidPassword($pwd, $repwd)){
				$this->mzError(model('Register')->getLastError());
			}
			$uid = model("User")->where(array('phone'=>$phone))->getField('uid');
			if(empty($phone) || $uid<0){
				$this->mzError('异常操作');
			}
			$salt = rand(10000,99999);
			$password = md5(md5($pwd).$salt);
				
			$res= model('User')->where(array('phone'=>$phone))->save(array(
					'password'=>$password,
					'login_salt'=>$salt,
			));
			//清楚用户的缓存
			$res && model('User')->cleancache($uid);
			$res !== false ? $this->success('密码修改成功，请登录！') : $this->mzError('密码更改失败！');
		}
		/**
		 * 修改密码操作
		 */
	/* 	public function repwdhandle(){
			$phone = trim($_POST['phone']);
			$pwd = trim($_POST['pwd']);
			$repwd = trim($_POST['repwd']);
			if(!model('Register')->isValidPassword($pwd, $repwd)){
				exit(model('Register')->getLastError());
			}
			$uid = model("User")->where(array('phone'=>$phone))->getField('uid');
			if(empty($phone) || $uid<0){
				exit('异常操作');
			} 
			$salt = rand(10000,99999);
			$password = md5(md5($pwd).$salt);
			
			$res= model('User')->where(array('phone'=>$phone))->save(array(
				'password'=>$password,
				'login_salt'=>$salt,
			));
			//清楚用户的缓存
			$res && model('User')->cleancache($uid);
			$res !== false ? exit('ok') : exit('密码更改失败');
		} */
	}