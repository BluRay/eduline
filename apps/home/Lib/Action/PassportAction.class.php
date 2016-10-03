<?php
/**
 * 登录控制器
 * @author ashangmanage <ashangmanage@phpzsm.com>
 * @version CY1.0
 */
class PassportAction extends Action
{
	
var $passport;

	/**
	 * 模块初始化
	 * @return void
	 */
	protected function _initialize() {
		$this->passport = model('Passport');
	}
	
	
	/**
	 * 用户登录
	 * @return void
	 */
	public function doLogin() {
		$login 		= addslashes($_POST['login_uname']);
		$password 	= trim($_POST['login_pwd']);
		$remember	= intval($_POST['login_remember']);
		$login_args	= t($_POST['login_args']);//ispage   isdialog
		$result 	= $this->passport->loginLocal($login,$password,$remember);
		$data = 0;
		if(!$result){
			$status = 0; 
			$info	= $this->passport->getError();
			$data 	= 0;
		}else{
			$status = 1;
			$info 	= $this->passport->getSuccess();
			//$data 	= ($GLOBALS['ts']['site']['home_url'])?$GLOBALS['ts']['site']['home_url']:0;
			if($login_args == 'ispage'){
				$data = 100001;
			}else if($login_args == 'isdialog'){
				$data = U('classroom/User/index');
			}else{
				$data = 0;	
			}
		}
		echo  $data;//$this->ajaxReturn($data,$info,$status);
	}
	
	/**
	 * 注销登录
	 * @return void
	 */
	public function logout() {
		$this->passport->logoutLocal();
		$_SESSION['login_status'] = false;
		echo 1;
		//$this->assign('jumpUrl',U('classroom/Index/index'));
		//$this->success('退出成功！');
	}
}